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
	$searchRequestCount = 0;
	$requests = [];
	$responses = [
		[
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => [
							'Base' => [
								'OfferId' => 'offer-sn|SNOW|NHx8',
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
			$searchRequestCount++;
			$payload = extractJsonPayload($options);
			$requests[] = is_array($payload) ? $payload : [];
			$response = array_shift($responses);
			if (!is_array($response)) {
				return new MockResponse(json_encode(['error' => 'unexpected extra request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
			}

			return new MockResponse(json_encode($response, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'conditions' => [
				[
					'search' => [
						'Base' => [
							'Operator' => ['SNOW'],
						],
					],
					'filter' => [],
				],
				[
					'search' => [
						'Base' => [
							'Operator' => ['ALT'],
						],
					],
					'filter' => [],
				],
			],
		],
	]));
	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest([], [], [], [
		'offerList' => ['limit' => 1],
		'fieldValues' => ['fieldList' => ['Base.Operator']],
	]));

	assertSameValue(2, $searchRequestCount, 'Soft-cap fan-out guard should keep fieldValues follow-through but stop offerList in sibling query.');
	assertSameValue(1, $requests[0]['views']['offerList']['limit'] ?? null, 'First query should keep explicit offerList.limit unchanged.');
	assertTrue(isset($requests[0]['views']['fieldValues']), 'First query should include fieldValues view.');
	assertTrue(!isset($requests[1]['views']['offerList']), 'Second query should skip offerList after merged offerList count reaches the soft cap.');
	assertTrue(isset($requests[1]['views']['fieldValues']), 'Second query should still include fieldValues view.');

	$response = $result->response();
	assertSameValue(1, count($response['offerList']['items'] ?? []), 'Merged offerList should keep only first query item.');
	assertSameValue(['SNOW', 'ALT'], $response['fieldValues']['Base.Operator'] ?? null, 'Field values should still aggregate across fan-out queries.');
	assertSameValue(['limitHits' => ['offerList' => true]], $result->meta(), 'Search result meta should expose only limitHits for capped views.');
	assertTrue(!array_key_exists('limitHit', $result->meta()), 'Search result meta should not duplicate overall limitHit flag.');

	$paginationRequestCount = 0;
	$paginationRequests = [];
	$paginationMock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$paginationRequestCount, &$paginationRequests): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/search')) {
			$paginationRequestCount++;
			$payload = extractJsonPayload($options);
			$paginationRequests[] = is_array($payload) ? $payload : [];

			$count = $paginationRequestCount === 1 ? 50 : 10;
			$start = $paginationRequestCount === 1 ? 1 : 51;
			$items = [];
			for ($i = 0; $i < $count; $i++) {
				$items[] = [
					'offer' => [
						'Base' => [
							'OfferId' => 'offer-' . ($start + $i) . '|SNOW|NHx8',
						],
					],
				];
			}

			return new MockResponse(json_encode([
				'offerList' => [
					'more' => true,
					'pageBookmark' => 'bm-' . $paginationRequestCount,
					'items' => $items,
				],
			], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$paginationConfig = MerlinxGetterConfig::fromArray(baseMerlinxConfig());
	$paginationTokenProvider = new AuthTokenProvider($paginationConfig, $paginationMock);
	$paginationHttpClient = new MerlinxHttpClient($paginationConfig, $paginationTokenProvider, $paginationMock);
	$paginationOperation = new SearchOperation($paginationConfig, $paginationHttpClient);

	$paginationResult = $paginationOperation->execute(searchRequest([], [], [], ['offerList' => ['limit' => 60]]));

	assertSameValue(2, $paginationRequestCount, 'Pagination should stop immediately after merged offerList reaches soft-cap limit.');
	assertSameValue(60, $paginationRequests[0]['views']['offerList']['limit'] ?? null, 'First page request should keep original view limit.');
	assertSameValue(60, $paginationRequests[1]['views']['offerList']['limit'] ?? null, 'Follow-up page request should keep original view limit unchanged.');
	assertSameValue('bm-1', $paginationRequests[1]['views']['offerList']['previousPageBookmark'] ?? null, 'Second page request should include previousPageBookmark.');
	assertSameValue(60, count($paginationResult->response()['offerList']['items'] ?? []), 'Merged response should include all fetched items without trimming.');
	assertSameValue(['limitHits' => ['offerList' => true]], $paginationResult->meta(), 'Meta should mark capped view as limit-hit after pagination.');

	echo "PASS: SearchOperation applies additive merged-view soft-cap guard and exposes limitHits meta without changing sent limits.\n";
	exit(0);
} catch (Throwable $e) {
	echo 'FAIL: ' . $e->getMessage() . "\n";
	exit(1);
}
