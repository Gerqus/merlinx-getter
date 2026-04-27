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
	$seenFieldLists = [];
	$mock = new MockHttpClient(static function (string $method, string $url, array $options = []) use (&$seenFieldLists): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (!str_contains($url, '/v5/data/travel/search')) {
			return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
		}

		$payload = extractJsonPayload($options);
		$fieldList = $payload['views']['offerList']['fieldList'] ?? [];
		$seenFieldLists[] = is_array($fieldList) ? $fieldList : [];
		$startDate = $payload['conditions']['search']['Base']['StartDate'] ?? null;
		$isSummerBranch = is_array($startDate) && ($startDate['Min'] ?? null) === '2026-05-01';

		$items = $isSummerBranch
			? [
				[
					'offer' => [
						'Base' => [
							'OfferId' => 'SUMMER-SKI-ATTRIBUTE',
							'XCity' => ['Name' => 'Schladming'],
						],
						'Accommodation' => [
							'Attributes' => ['location_ski_resorts'],
						],
					],
				],
			]
			: [
				[
					'offer' => [
						'Base' => [
							'OfferId' => 'FALLBACK-SKI-ATTRIBUTE',
							'XCity' => ['Name' => 'Schladming'],
						],
						'Accommodation' => [
							'Attributes' => ['location_ski_resorts'],
						],
					],
				],
				[
					'offer' => [
						'Base' => [
							'OfferId' => 'FALLBACK-VISIBLE',
							'XCity' => ['Name' => 'Schladming'],
						],
						'Accommodation' => [
							'Attributes' => ['location_mountains'],
						],
					],
				],
			];

		return new MockResponse(json_encode([
			'offerList' => [
				'items' => $items,
				'more' => false,
				'pageBookmark' => '',
			],
		], JSON_THROW_ON_ERROR), ['http_code' => 200]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'operators' => ['NKRA'],
			'conditions' => [
				[
					'search' => [
						'Base' => [
							'StartDate' => ['Min' => '2026-05-01', 'Max' => '2026-10-31'],
						],
					],
				],
				[
					'search' => [
						'Accommodation' => [
							'Attributes' => ['-location_ski_resorts'],
						],
					],
					'response_filters' => [
						'exclude_values_by_path' => [
							'offer.Accommodation.Attributes' => ['location_ski_resorts'],
						],
					],
				],
			],
		],
	]));

	$operation = new SearchOperation(
		$config,
		new MerlinxHttpClient($config, new AuthTokenProvider($config, $mock), $mock),
	);
	$result = $operation->execute(searchRequest([], [], [], [
		'offerList' => [
			'fieldList' => ['Base.OfferId'],
			'limit' => 100,
		],
	]))->response();

	$items = $result['offerList']['items'] ?? null;
	assertTrue(is_array($items), 'offerList.items should exist.');
	assertSameValue(['SUMMER-SKI-ATTRIBUTE', 'FALLBACK-VISIBLE'], array_keys($items), 'Condition-local response filters should only remove items from the condition that declared them.');
	assertSameValue(['Base.OfferId'], $seenFieldLists[0] ?? null, 'Summer condition without attribute filter should not request Accommodation.Attributes only for filtering.');
	assertTrue(in_array('Accommodation.Attributes', $seenFieldLists[1] ?? [], true), 'Fallback condition should request fields required by its condition-local response filter.');

	echo "PASS: SearchOperation applies response filters per configured condition.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
