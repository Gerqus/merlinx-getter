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
	$responses = [
		[
			'offerList' => [
				'more' => true,
				'pageBookmark' => 'bm-a',
				'items' => [
					'upstream-key-1' => [
						'offer' => [
							'Base' => [
								'OfferId' => 'offer-1|SNOW|NHx8',
								'Price' => ['Total' => ['amount' => '100.00']],
							],
							'Accommodation' => [],
						],
					],
					'upstream-missing' => [
						'offer' => [
							'Base' => [],
						],
					],
				],
			],
		],
		[
			'offerList' => [
				'more' => false,
				'pageBookmark' => 'bm-b',
				'items' => [
					'upstream-key-dup' => [
						'offer' => [
							'Base' => [
								'OfferId' => 'offer-1|SNOW|NHx8',
							],
							'Accommodation' => [
								'Name' => 'Filled From Duplicate',
							],
						],
					],
					'upstream-key-2' => [
						'offer' => [
							'Base' => [
								'OfferId' => 'offer-2|SNOW|NHx8',
							],
						],
					],
				],
			],
		],
	];

	$searchRequests = 0;
	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$responses, &$searchRequests): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$searchRequests++;
			$index = $searchRequests - 1;
			if (!isset($responses[$index])) {
				return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
			}
			return new MockResponse(json_encode($responses[$index], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'conditions' => [
				['search' => [], 'filter' => []],
				['search' => [], 'filter' => []],
			],
		],
	]));

	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest([], [], [], ['offerList' => ['limit' => 500]]))->response();

	assertSameValue(2, $searchRequests, 'Expected two /search requests (single deduped query with two pages).');

	$items = $result['offerList']['items'] ?? null;
	assertTrue(is_array($items), 'Merged offerList.items is missing or invalid.');
	assertSameValue(2, count($items), 'Merged offerList.items should include both unique OfferIds.');
	assertTrue(!($result['offerList']['more'] ?? true), 'Merged offerList.more should be false as per last response.');
	assertSameValue('bm-b', $result['offerList']['pageBookmark'] ?? null, 'Merged offerList.pageBookmark should match last response.');
	assertSameValue(
		['offer-1|SNOW|NHx8', 'offer-2|SNOW|NHx8'],
		array_keys($items),
		'Merged offerList.items should be keyed by full OfferId in first-seen order.'
	);
	assertSameValue(
		'100.00',
		$items['offer-1|SNOW|NHx8']['offer']['Base']['Price']['Total']['amount'] ?? null,
		'First occurrence should keep existing populated fields.'
	);
	assertSameValue(
		'Filled From Duplicate',
		$items['offer-1|SNOW|NHx8']['offer']['Accommodation']['Name'] ?? null,
		'Duplicate OfferId should fill gaps from later payloads.'
	);

	echo "PASS: SearchOperation keys offerList.items by OfferId, drops malformed entries, and fills duplicate gaps.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
