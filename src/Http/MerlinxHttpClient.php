<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Http;

use JsonException;
use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MerlinxHttpClient
{
	private readonly HttpClientInterface $httpClient;
	private readonly AuthTokenProvider $tokenProvider;
	private readonly string $baseUrl;
	private readonly string $domain;
	private readonly float $timeout;

	public function __construct(MerlinxGetterConfig $config, AuthTokenProvider $tokenProvider, ?HttpClientInterface $httpClient = null)
	{
		$this->httpClient = $httpClient ?? HttpClient::create();
		$this->tokenProvider = $tokenProvider;
		$this->baseUrl = rtrim($config->baseUrl, '/');
		$this->domain = $config->domain;
		$this->timeout = $config->timeout;
	}

	/**
	 * @param array<string, mixed> $options
	 */
	public function request(string $method, string $uri, array $options = []): HttpResponse
	{
		$url = $this->buildUrl($uri);
		$isAuthRequest = $this->isAuthUri($uri);
		$options = $this->withTimeout($options);
		$options = $isAuthRequest ? $options : $this->withAuthHeaders($options);

		$response = $this->send($method, $url, $options);
		if ($isAuthRequest) {
			return $response;
		}

		if ($this->isAuthError($response->statusCode(), $response->body())) {
			$freshToken = $this->tokenProvider->forceRefresh();
			$optionsWithNewToken = $this->withAuthHeaders($options, $freshToken);
			return $this->send($method, $url, $optionsWithNewToken);
		}

		return $response;
	}

	private function buildUrl(string $uri): string
	{
		if (str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://')) {
			return $uri;
		}
		return $this->baseUrl . '/' . ltrim($uri, '/');
	}

	private function isAuthUri(string $uri): bool
	{
		return str_contains($uri, '/v5/token/new');
	}

	/**
	 * @param array<string, mixed> $options
	 * @return array<string, mixed>
	 */
	private function withTimeout(array $options): array
	{
		if (!isset($options['timeout'])) {
			$options['timeout'] = $this->timeout;
		}
		return $options;
	}

	/**
	 * @param array<string, mixed> $options
	 * @return array<string, mixed>
	 */
	private function withAuthHeaders(array $options, ?string $token = null): array
	{
		$headers = $options['headers'] ?? [];
		if (!is_array($headers)) {
			$headers = [];
		}

		$headers['X-TOKEN'] = $token ?? $this->tokenProvider->getToken();
		if ($this->domain !== '') {
			$headers['X-DOMAIN'] = $this->domain;
		}

		$options['headers'] = $headers;
		return $options;
	}

	/**
	 * @param array<string, mixed> $options
	 */
	private function send(string $method, string $url, array $options): HttpResponse
	{
		try {
			$response = $this->httpClient->request($method, $url, $options);
			$status = $response->getStatusCode();
			$headers = $response->getHeaders(false);
			$body = $response->getContent(false);
		} catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
			throw new HttpRequestException('MerlinX HTTP request failed.', null, null, $e);
		}

		return new HttpResponse($status, $headers, $this->removeDebugField($body));
	}

	private function isAuthError(int $statusCode, string $body): bool
	{
		if ($statusCode === 412) {
			return true;
		}

		return stripos($body, 'autherror') !== false;
	}

	private function removeDebugField(string $body): string
	{
		$trimmed = trim($body);
		if ($trimmed === '' || ($trimmed[0] !== '{' && $trimmed[0] !== '[')) {
			return $body;
		}

		try {
			$data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
			if (!is_array($data)) {
				return $body;
			}

			$cleaned = $this->removeDebugFieldRecursive($data);
			$result = json_encode($cleaned, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			return is_string($result) ? $result : $body;
		} catch (JsonException) {
			return $body;
		}
	}

	private function removeDebugFieldRecursive(mixed $value): mixed
	{
		if (!is_array($value)) {
			return $value;
		}

		$result = [];
		foreach ($value as $key => $val) {
			if ($key === 'debug') {
				continue;
			}
			$result[$key] = $this->removeDebugFieldRecursive($val);
		}

		return $result;
	}
}
