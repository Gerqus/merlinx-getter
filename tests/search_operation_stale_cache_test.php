<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Skionline\MerlinxGetter\Http\AuthTokenProvider;
use Skionline\MerlinxGetter\Http\MerlinxHttpClient;
use Skionline\MerlinxGetter\Operation\SearchOperation;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/bootstrap.php';

try {
	$buildCacheDir = static function (): string {
		$dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
			. DIRECTORY_SEPARATOR
			. 'merlinx-getter-stale-cache-'
			. str_replace('.', '-', uniqid('', true));
		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			throw new RuntimeException('Unable to create stale-cache test directory.');
		}
		return $dir;
	};

	$searchRequests = 0;
	$responses = [
		new MockResponse(json_encode([
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => [
							'Base' => [
								'OfferId' => 'stale-source|SNOW|NHx8',
							],
						],
					],
				],
			],
		], JSON_THROW_ON_ERROR), ['http_code' => 200]),
		new MockResponse('{"error":"upstream"}', ['http_code' => 500]),
	];

	$staleFallbackMock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$responses, &$searchRequests): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$searchRequests++;
			$response = array_shift($responses);
			if (!$response instanceof MockResponse) {
				return new MockResponse('{"error":"unexpected"}', ['http_code' => 500]);
			}
			return $response;
		}

		return new MockResponse('{"error":"unexpected request"}', ['http_code' => 500]);
	});

	$staleConfig = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'cache' => [
				'search' => [
					'ttl_seconds' => 1,
					'stale_seconds' => 3,
					'lock_timeout_ms' => 500,
					'lock_retry_delay_ms' => 10,
				],
			],
		],
		'cache' => [
			'dir' => $buildCacheDir(),
		],
	]));

	$staleTokenProvider = new AuthTokenProvider($staleConfig, $staleFallbackMock);
	$staleHttpClient = new MerlinxHttpClient($staleConfig, $staleTokenProvider, $staleFallbackMock);
	$staleOperation = new SearchOperation($staleConfig, $staleHttpClient);

	$search = ['Base' => ['Availability' => ['available']]];
	$views = ['offerList' => ['limit' => 10]];

	$first = $staleOperation->execute(searchRequest($search, [], [], $views))->response();
	assertSameValue('stale-source|SNOW|NHx8', $first['offerList']['items']['stale-source|SNOW|NHx8']['offer']['Base']['OfferId'] ?? null, 'Initial stale-source marker mismatch.');
	assertSameValue(1, $searchRequests, 'First call should fetch search payload once.');

	sleep(2);

	$second = $staleOperation->execute(searchRequest($search, [], [], $views))->response();
	assertSameValue(2, $searchRequests, 'Expired fresh cache should attempt refresh before stale fallback.');
	assertSameValue('stale-source|SNOW|NHx8', $second['offerList']['items']['stale-source|SNOW|NHx8']['offer']['Base']['OfferId'] ?? null, 'Stale fallback should reuse cached data on upstream error.');

	$expiredSearchRequests = 0;
	$expiredResponses = [
		new MockResponse(json_encode([
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => [
							'Base' => [
								'OfferId' => 'expired-source|SNOW|NHx8',
							],
						],
					],
				],
			],
		], JSON_THROW_ON_ERROR), ['http_code' => 200]),
		new MockResponse('{"error":"expired-upstream"}', ['http_code' => 500]),
	];
	$expiredMock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$expiredSearchRequests, &$expiredResponses): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$expiredSearchRequests++;
			$response = array_shift($expiredResponses);
			if (!$response instanceof MockResponse) {
				return new MockResponse('{"error":"unexpected"}', ['http_code' => 500]);
			}
			return $response;
		}

		return new MockResponse('{"error":"unexpected request"}', ['http_code' => 500]);
	});

	$expiredConfig = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'cache' => [
				'search' => [
					'ttl_seconds' => 1,
					'stale_seconds' => 1,
					'lock_timeout_ms' => 500,
					'lock_retry_delay_ms' => 10,
				],
			],
		],
		'cache' => [
			'dir' => $buildCacheDir(),
		],
	]));
	$expiredTokenProvider = new AuthTokenProvider($expiredConfig, $expiredMock);
	$expiredHttpClient = new MerlinxHttpClient($expiredConfig, $expiredTokenProvider, $expiredMock);
	$expiredOperation = new SearchOperation($expiredConfig, $expiredHttpClient);

	$expiredOperation->execute(searchRequest($search, [], [], $views));
	assertSameValue(1, $expiredSearchRequests, 'Initial expired-scenario search call mismatch.');

	sleep(3);

	assertThrows(
		static fn() => $expiredOperation->execute(searchRequest($search, [], [], $views)),
		HttpRequestException::class,
		static function (Throwable $e): void {
			assertSameValue(500, $e->statusCode(), 'Expired stale path should keep upstream HTTP status in exception.');
			assertTrue(str_contains((string) $e->responseBody(), 'expired-upstream'), 'Expired stale path should keep upstream response body.');
		}
	);
	assertSameValue(2, $expiredSearchRequests, 'Expired stale entry should not mask upstream failure.');

	echo "PASS: SearchOperation serves stale on refresh error and stops serving stale after stale window expiry.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
