<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Http\AuthTokenProvider;
use Skionline\MerlinxGetter\Http\MerlinxHttpClient;
use Skionline\MerlinxGetter\Operation\SearchOperation;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$responses = [
		[
			'groupedList' => [
				'more' => false,
				'items' => [
					[
						'groupKeyValue' => 'group-1',
						'offer' => ['Id' => 'first-call'],
					],
				],
			],
		],
		[
			'groupedList' => [
				'more' => false,
				'items' => [
					[
						'groupKeyValue' => 'group-1',
						'offer' => ['Id' => 'second-call'],
					],
				],
			],
		],
	];

	$searchRequests = 0;
	$tokenRequests = 0;
	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$responses, &$searchRequests, &$tokenRequests): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			$tokenRequests++;
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$searchRequests++;
			$response = array_shift($responses);
			if (!is_array($response)) {
				return new MockResponse(json_encode(['error' => 'unexpected search request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
			}
			return new MockResponse(json_encode($response, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig());

	$firstTokenProvider = new AuthTokenProvider($config, $mock);
	$firstHttpClient = new MerlinxHttpClient($config, $firstTokenProvider, $mock);
	$firstOperation = new SearchOperation($config, $firstHttpClient);

	$search = ['Base' => ['Availability' => ['available']]];
	$views = ['groupedList' => ['limit' => 100]];

	$first = $firstOperation->execute(searchRequest($search, [], [], $views))->response();
	assertSameValue('first-call', $first['groupedList']['items']['group-1']['offer']['Id'] ?? null, 'First response marker mismatch.');
	assertSameValue(1, $searchRequests, 'First call should hit MerlinX once.');
	assertSameValue(1, $tokenRequests, 'First call should request one token.');

	$secondTokenProvider = new AuthTokenProvider($config, $mock);
	$secondHttpClient = new MerlinxHttpClient($config, $secondTokenProvider, $mock);
	$secondOperation = new SearchOperation($config, $secondHttpClient);

	$second = $secondOperation->execute(searchRequest($search, [], [], $views))->response();
	assertSameValue(1, $searchRequests, 'Second identical call from new instance should be served from persistent cache.');
	assertSameValue('first-call', $second['groupedList']['items']['group-1']['offer']['Id'] ?? null, 'Second response should come from persistent cache.');

	$third = $secondOperation->execute(searchRequest($search, [], [], $views, ['rateLimitRetryMaxAttempts' => 7]))->response();
	assertSameValue(2, $searchRequests, 'Changed options should bypass persistent cache key.');
	assertSameValue('second-call', $third['groupedList']['items']['group-1']['offer']['Id'] ?? null, 'Third response marker mismatch.');

	echo "PASS: SearchOperation persistent cache dedupes identical calls across instances and respects options key.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
