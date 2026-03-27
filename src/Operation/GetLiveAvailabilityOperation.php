<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Operation;

use JsonException;
use Psr\SimpleCache\CacheInterface;
use Skionline\MerlinxGetter\Cache\FilesystemCacheFactory;
use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Contract\OperationInterface;
use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Skionline\MerlinxGetter\Exception\InvalidInputException;
use Skionline\MerlinxGetter\Exception\ResponseFormatException;
use Skionline\MerlinxGetter\Http\MerlinxHttpClient;
use Skionline\MerlinxGetter\Search\Util\SearchRequestFingerprint;

final class GetLiveAvailabilityOperation implements OperationInterface
{
	private const CACHE_SCHEMA = 'live_availability_cache_v1';
	private const CACHE_KEY_PREFIX = 'live_availability.';

	private readonly CacheInterface $cache;
	private readonly int $cacheTtlSeconds;
	private readonly string $configFingerprint;

	public function __construct(
		private readonly MerlinxHttpClient $client,
		private readonly MerlinxGetterConfig $config,
		?CacheInterface $cache = null,
		?int $cacheTtlSeconds = null,
	) {
		$this->cache = $cache ?? (new FilesystemCacheFactory($this->config->cacheDir))->create('merlinx_getter.live_availability.v1');
		$this->cacheTtlSeconds = max(1, $cacheTtlSeconds ?? $this->config->cacheLiveAvailabilityTtlSeconds);
		$this->configFingerprint = SearchRequestFingerprint::hash([
			'baseUrl' => $this->config->baseUrl,
			'domain' => $this->config->domain,
			'source' => $this->config->source,
			'type' => $this->config->type,
			'language' => $this->config->language,
		]);
	}

	public function key(): string
	{
		return 'getLiveAvailability';
	}

	/**
	 * @return array<string, mixed>
	 */
	public function execute(string $offerId, ?string $action = 'checkstatus', bool $includeTfg = true, bool $force = false): array
	{
		$offerId = trim($offerId);
		if ($offerId === '') {
			throw new InvalidInputException('OfferId is required.');
		}

		$action = is_string($action) ? trim($action) : '';
		if ($action === '') {
			$action = 'checkstatus';
		}

		$cacheKey = $this->buildCacheKey($offerId, $action, $includeTfg);
		if (!$force) {
			$cached = $this->readCache($cacheKey);
			if ($cached !== null) {
				return $cached;
			}
		}

		$response = $this->client->request('POST', '/v5/data/travel/checkonline', [
			'json' => [
				'actions' => [$action],
				'offerIds' => [$offerId],
				'includeTFG' => $includeTfg,
			],
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
			],
		]);

		$status = $response->statusCode();
		$content = $response->body();
		if ($status >= 400) {
			throw new HttpRequestException('MerlinX checkonline failed with status ' . $status . '.', $status, $content);
		}

		try {
			$data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			throw new ResponseFormatException('MerlinX checkonline response is invalid JSON.', 0, $e);
		}

		if (!is_array($data)) {
			throw new ResponseFormatException('MerlinX checkonline response has unexpected format.');
		}

		$this->writeCache($cacheKey, $data);

		return $data;
	}

	private function buildCacheKey(string $offerId, string $action, bool $includeTfg): string
	{
		return self::CACHE_KEY_PREFIX . SearchRequestFingerprint::hash([
			'schema' => self::CACHE_SCHEMA,
			'configFingerprint' => $this->configFingerprint,
			'offerId' => $offerId,
			'action' => $action,
			'includeTfg' => $includeTfg,
		]);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function readCache(string $cacheKey): ?array
	{
		try {
			$payload = $this->cache->get($cacheKey);
		} catch (\Throwable) {
			return null;
		}

		return is_array($payload) ? $payload : null;
	}

	/**
	 * @param array<string, mixed> $payload
	 */
	private function writeCache(string $cacheKey, array $payload): void
	{
		try {
			$this->cache->set($cacheKey, $payload, $this->cacheTtlSeconds);
		} catch (\Throwable) {
		}
	}
}
