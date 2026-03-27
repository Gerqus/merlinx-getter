<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Http\AuthTokenProvider;
use Skionline\MerlinxGetter\Http\MerlinxHttpClient;
use Skionline\MerlinxGetter\Operation\SearchOperation;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/bootstrap.php';

try {
	$searchResponses = [
		new MockResponse("Too many requests\n", ['http_code' => 200, 'response_headers' => ['Retry-After: 0']]),
		new MockResponse("Too many requests\n", ['http_code' => 200]),
		new MockResponse(json_encode([
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => [
							'Base' => [
								'OfferId' => 'retry-success|SNOW|NHx8',
							],
						],
					],
				],
			],
		], JSON_THROW_ON_ERROR), ['http_code' => 200]),
	];

	$searchRequestCount = 0;
	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$searchResponses, &$searchRequestCount): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$searchRequestCount++;
			$response = array_shift($searchResponses);
			if ($response instanceof MockResponse) {
				return $response;
			}
			return new MockResponse(json_encode(['error' => 'unexpected extra search request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'runtime' => [
				'rate_limit_retry_max_attempts' => 3,
				'rate_limit_retry_delay_ms' => 1,
				'rate_limit_retry_backoff_multiplier' => 2.0,
				'rate_limit_retry_max_delay_ms' => 16,
			],
		],
	]));
	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest([], [], [], ['offerList' => []]))->response();

	assertSameValue(3, $searchRequestCount, 'Expected two retries before successful winter search response.');
	assertTrue(is_array($result['offerList']['items'] ?? null), 'offerList.items should exist after retry success.');
	assertSameValue('retry-success|SNOW|NHx8', $result['offerList']['items']['retry-success|SNOW|NHx8']['offer']['Base']['OfferId'] ?? null, 'Returned payload should come from final successful response.');

	echo "PASS: SearchOperation honors config-default retry options for rate-limited responses.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
