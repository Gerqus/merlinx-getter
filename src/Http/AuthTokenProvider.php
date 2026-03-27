<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Http;

use JsonException;
use Psr\SimpleCache\CacheInterface;
use Skionline\MerlinxGetter\Cache\FileKeyLock;
use Skionline\MerlinxGetter\Cache\FilesystemCacheFactory;
use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Skionline\MerlinxGetter\Exception\ResponseFormatException;
use Skionline\MerlinxGetter\Http\Auxiliary\HttpErrorReporter;
use Skionline\MerlinxGetter\Http\Auxiliary\RateLimitRetryEngine;
use Skionline\MerlinxGetter\Http\Models\RetryPolicy;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AuthTokenProvider
{
	private readonly HttpClientInterface $httpClient;
	private readonly string $endpoint;

	/** @var array<string, string> */
	private readonly array $payload;

	private readonly int $ttl;
	private readonly CacheInterface $cache;
	private readonly FileKeyLock $lock;
	private readonly string $tokenCacheKey;
	private readonly string $refreshLockKey;
	private readonly RetryPolicy $retryPolicy;
	private readonly RateLimitRetryEngine $retryEngine;
	private readonly HttpErrorReporter $errorReporter;

	private ?string $token = null;
	private int $expiresAt = 0;

	public function __construct(
		MerlinxGetterConfig $config,
		?HttpClientInterface $httpClient = null,
		?CacheInterface $cache = null,
		?FileKeyLock $lock = null
	) {
		$this->httpClient = $httpClient ?? HttpClient::create();
		$this->endpoint = rtrim($config->baseUrl, '/') . '/v5/token/new';
		$this->payload = [
			'login' => $config->login,
			'password' => $config->password,
			'expedient' => $config->expedient,
			'domain' => $config->domain,
			'source' => $config->source,
			'type' => $config->type,
			'language' => $config->language,
		];
		$this->ttl = $config->cacheTokenTtlSeconds;
		$this->cache = $cache ?? (new FilesystemCacheFactory($config->cacheDir))->create('merlinx_getter.token.v2');
		$lockDir = rtrim($config->cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'locks';
		$this->lock = $lock ?? new FileKeyLock($lockDir, $config->cacheSearchLockTimeoutMs, $config->cacheSearchLockRetryDelayMs);
		$this->retryPolicy = RetryPolicy::fromConfig($config);
		$this->retryEngine = new RateLimitRetryEngine();
		$this->errorReporter = new HttpErrorReporter();

		$fingerprintPayload = [
			'endpoint' => $this->endpoint,
			'payload' => $this->payload,
			'schema' => 'token_cache_v2',
		];
		try {
			$fingerprint = hash('sha256', json_encode($fingerprintPayload, JSON_THROW_ON_ERROR));
		} catch (JsonException) {
			$fingerprint = hash('sha256', serialize($fingerprintPayload));
		}
		$this->tokenCacheKey = 'token.' . $fingerprint;
		$this->refreshLockKey = 'token_refresh.' . $fingerprint;
	}

	public function getToken(): string
	{
		if ($this->token !== null && $this->expiresAt > time()) {
			return $this->token;
		}

		$cached = $this->readPersistentToken();
		if ($cached !== null) {
			$this->token = $cached['token'];
			$this->expiresAt = $cached['expiresAt'];
			return $this->token;
		}

		return $this->refreshToken(false);
	}

	public function forceRefresh(): string
	{
		return $this->refreshToken(true);
	}

	public function clearRuntimeState(): void
	{
		$this->token = null;
		$this->expiresAt = 0;
	}

	private function refreshToken(bool $force): string
	{
		return $this->lock->withLock($this->refreshLockKey, function () use ($force): string {
			if (!$force) {
				if ($this->token !== null && $this->expiresAt > time()) {
					return $this->token;
				}

				$cached = $this->readPersistentToken();
				if ($cached !== null) {
					$this->token = $cached['token'];
					$this->expiresAt = $cached['expiresAt'];
					return $this->token;
				}
			}

			$response = $this->requestTokenWithRetry();
			$status = $response['status'];
			$body = $response['body'];
			$attemptsMade = $response['attemptsMade'];
			$maxAttempts = $response['maxAttempts'];

			if ($status >= 400) {
				throw new HttpRequestException(
					$this->errorReporter->buildMessage(
						'MerlinX token request failed with status ' . $status,
						'POST',
						$this->tokenEndpointForErrorMessage(),
						$attemptsMade,
						$maxAttempts,
						$body,
						$this->errorReporter->buildSnippet($this->payload),
					),
					$status,
					$body
				);
			}

			try {
				$data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
			} catch (JsonException $e) {
				throw new ResponseFormatException('MerlinX token response is invalid JSON.', 0, $e);
			}

			if (!is_array($data) || !is_string($data['token'] ?? null) || trim((string) $data['token']) === '') {
				throw new ResponseFormatException('MerlinX token response does not contain a token.');
			}

			$this->token = trim((string) $data['token']);
			$this->expiresAt = time() + $this->ttl;
			$this->writePersistentToken($this->token, $this->expiresAt);

			return $this->token;
		});
	}

	/**
	 * @return array{status:int,body:string,attemptsMade:int,maxAttempts:int}
	 */
	private function requestTokenWithRetry(): array
	{
		$retryDelayMs = $this->retryPolicy->initialDelayMs();
		$maxAttempts = $this->retryPolicy->maxAttempts();
		$endpoint = $this->tokenEndpointForErrorMessage();
		$requestSnippet = $this->errorReporter->buildSnippet($this->payload);

		for ($attempt = 0;; $attempt++) {
			$status = 0;
			$headers = [];
			$body = '';

			try {
				$response = $this->httpClient->request('POST', $this->endpoint, [
					'json' => $this->payload,
					'headers' => ['Content-Type' => 'application/json'],
					'timeout' => 10,
				]);
				$status = $response->getStatusCode();
				$headers = $response->getHeaders(false);
				$body = $response->getContent(false);
			} catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
				if ($this->retryEngine->isRateLimitedThrowable($e) && $attempt < $maxAttempts) {
					$this->retryEngine->wait($retryDelayMs, null);
					$retryDelayMs = $this->retryEngine->nextDelayMs($retryDelayMs, $this->retryPolicy);
					continue;
				}

				throw new HttpRequestException(
					$this->errorReporter->buildMessage(
						'MerlinX token request failed: ' . $e->getMessage(),
						'POST',
						$endpoint,
						$attempt + 1,
						$maxAttempts + 1,
						null,
						$requestSnippet,
					),
					null,
					null,
					$e
				);
			}

			if ($this->retryEngine->isRateLimited($status, $body)) {
				if ($attempt < $maxAttempts) {
					$retryAfterMs = $this->retryEngine->extractRetryAfterMs($headers);
					$this->retryEngine->wait($retryDelayMs, $retryAfterMs);
					$retryDelayMs = $this->retryEngine->nextDelayMs($retryDelayMs, $this->retryPolicy);
					continue;
				}

				throw new HttpRequestException(
					$this->errorReporter->buildMessage(
						'MerlinX token request rate limit persisted after retries',
						'POST',
						$endpoint,
						$attempt + 1,
						$maxAttempts + 1,
						$body,
						$requestSnippet,
					),
					$status,
					$body
				);
			}

			return [
				'status' => $status,
				'body' => $body,
				'attemptsMade' => $attempt + 1,
				'maxAttempts' => $maxAttempts + 1,
			];
		}
	}

	private function tokenEndpointForErrorMessage(): string
	{
		$path = parse_url($this->endpoint, PHP_URL_PATH);
		return is_string($path) && $path !== '' ? $path : $this->endpoint;
	}

	/**
	 * @return array{token:string,expiresAt:int}|null
	 */
	private function readPersistentToken(): ?array
	{
		try {
			$cached = $this->cache->get($this->tokenCacheKey);
		} catch (\Throwable) {
			return null;
		}

		if (!is_array($cached)) {
			return null;
		}

		$token = $cached['token'] ?? null;
		$expiresAt = $cached['expiresAt'] ?? null;
		if (!is_string($token) || trim($token) === '') {
			return null;
		}
		if (!is_int($expiresAt) && !(is_string($expiresAt) && ctype_digit($expiresAt))) {
			return null;
		}

		$expiresAtInt = (int) $expiresAt;
		if ($expiresAtInt <= time()) {
			try {
				$this->cache->delete($this->tokenCacheKey);
			} catch (\Throwable) {
			}
			return null;
		}

		return [
			'token' => trim($token),
			'expiresAt' => $expiresAtInt,
		];
	}

	private function writePersistentToken(string $token, int $expiresAt): void
	{
		$ttl = max(1, $expiresAt - time());
		try {
			$this->cache->set($this->tokenCacheKey, [
				'token' => $token,
				'expiresAt' => $expiresAt,
			], $ttl);
		} catch (\Throwable) {
		}
	}
}
