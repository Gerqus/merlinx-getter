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
	$searchRequestCount = 0;
	$requests = [];

	$responses = [
		[
			'offerList' => [
				'more' => true,
				'pageBookmark' => 'bm-c1-1',
				'items' => [
					[
						'offer' => [
							'Base' => [
								'OfferId' => 'offer-c1-p1|SNOW|NHx8',
							],
						],
					],
				],
			],
			'fieldValues' => [
				'fieldValues' => [
					'Base.Operator' => ['SNOW'],
				],
			],
		],
		[
			'offerList' => [
				'more' => false,
				'pageBookmark' => 'bm-c1-2',
				'items' => [
					[
						'offer' => [
							'Base' => [
								'OfferId' => 'offer-c1-p2|SNOW|NHx8',
							],
						],
					],
				],
			],
			'fieldValues' => [
				'fieldValues' => [
					'Base.Operator' => ['SNOW'],
				],
			],
		],
		[
			'offerList' => [
				'more' => false,
				'pageBookmark' => 'bm-c2-1',
				'items' => [
					[
						'offer' => [
							'Base' => [
								'OfferId' => 'offer-c2-p1|ALT|NHx8',
							],
						],
					],
				],
			],
			'fieldValues' => [
				'fieldValues' => [
					'Base.Operator' => ['ALT'],
				],
			],
		],
	];

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$searchRequestCount, &$requests, &$responses): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/search')) {
			$payload = extractJsonPayload($options);
			$requests[] = ['url' => $url, 'payload' => $payload];
			$searchRequestCount++;
			$response = array_shift($responses);

			if (!is_array($response)) {
				return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
			}

			return new MockResponse(json_encode($response, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'operators' => ['BASE'],
			'conditions' => [
				[
					'search' => [
						'Base' => [
							'Operator' => ['SNOW'],
						],
					],
				],
				[
					'search' => [
						'Base' => [
							'Operator' => ['ALT'],
						],
					],
				],
			],
		],
	]));

	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$request = searchRequest(
		search: [
			'Base' => [
				'Transport' => ['own'],
			],
		],
		filter: [],
		results: [],
		views: ['offerList' => ['limit' => 500], 'fieldValues' => ['fieldList' => ['Base.Operator']]],
	);

	$firstResult = $operation->execute($request)->response();

	assertSameValue(3, $searchRequestCount, 'First execution should run two configured conditions and one extra paginated page.');
	assertSameValue('SNOW', $requests[0]['payload']['conditions']['search']['Base']['Operator'][0] ?? null, 'First condition should build request with SNOW operator.');
	assertSameValue('own', $requests[0]['payload']['conditions']['search']['Base']['Transport'][0] ?? null, 'Request-level search condition should be merged into condition query.');
	assertSameValue('available', $requests[0]['payload']['conditions']['search']['Base']['Availability'][0] ?? null, 'Default availability policy should be applied by request builder.');
	assertSameValue('bm-c1-1', $requests[1]['payload']['views']['offerList']['previousPageBookmark'] ?? null, 'Second HTTP request should be paginated with previousPageBookmark.');
	assertSameValue('ALT', $requests[2]['payload']['conditions']['search']['Base']['Operator'][0] ?? null, 'Second configured condition should execute as separate query.');

	assertSameValue(3, count($firstResult['offerList']['items'] ?? []), 'Merged result should contain items from both pages and both conditions.');
	assertSameValue(
		['offer-c1-p1|SNOW|NHx8', 'offer-c1-p2|SNOW|NHx8', 'offer-c2-p1|ALT|NHx8'],
		array_keys($firstResult['offerList']['items'] ?? []),
		'Merged offerList item keys should preserve first-seen order across pagination and conditions.'
	);
	assertSameValue(['SNOW', 'ALT'], $firstResult['fieldValues']['Base.Operator'] ?? null, 'Merged fieldValues should include values from all executed queries.');

	$secondResult = $operation->execute($request)->response();
	assertSameValue(3, $searchRequestCount, 'Second identical execution should be served from cache with no extra HTTP search calls.');	
	assertSameValue($firstResult, $secondResult, 'Cached second execution result should be identical to first execution result.');

	echo "PASS: SearchOperation full pipeline covers condition build, pagination merge, and cache hit on repeated call.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
