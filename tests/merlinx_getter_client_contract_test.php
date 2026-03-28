<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Contract\OperationInterface;
use Skionline\MerlinxGetter\Exception\MerlinxGetterException;
use Skionline\MerlinxGetter\MerlinxGetterClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$searchPayload = [
		'offerList' => [
			'more' => false,
			'items' => [
				'item-1' => [
					'offer' => [
						'Base' => [
							'OfferId' => 'contract-1',
							'StartDate' => '2099-01-01',
							'Availability' => ['base' => 'available'],
							'Price' => [
								'Total' => ['amount' => '1200.00', 'currency' => 'PLN'],
								'FirstPerson' => ['amount' => '600.00', 'currency' => 'PLN'],
							],
						],
					],
				],
			],
		],
	];
	$detailsPayload = [
		'result' => [
			'offer' => [
				'Base' => [
					'OfferId' => 'contract-1|SNOW|NHx8',
					'StartDate' => '2099-01-01',
					'Availability' => ['base' => 'available'],
				],
				'Accommodation' => [
					'Name' => 'Contract Hotel',
				],
			],
		],
	];
	$checkOnlinePayload = fixtureJson('checkonline/success.json');
	$portalPayload = [
		'offers' => [
			[
				'id' => 'portal-offer-1',
				'name' => 'Portal Hotel',
			],
		],
		'query' => ['sortBy' => 'price', 'sortDirection' => 'asc'],
		'error' => null,
		'limitHit' => false,
	];

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use ($searchPayload, $detailsPayload, $checkOnlinePayload, $portalPayload): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			return new MockResponse(json_encode($searchPayload, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/details')) {
			return new MockResponse(json_encode($detailsPayload, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/checkonline')) {
			return new MockResponse(json_encode($checkOnlinePayload, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if ($url === 'https://www.skionline.pl/wxp/?p=ofertyResultsJson') {
			return new MockResponse(json_encode($portalPayload, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$client = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $mock);

	$searchExecutionResult = $client->executeSearch(searchRequest([], [], [], ['offerList' => []]));
	$searchResult = $searchExecutionResult->response();
	assertTrue(is_array($searchResult), 'search() should return an array.');
	assertTrue(is_array($searchResult['offerList'] ?? null), 'search() should return offerList view payload.');
	assertSameValue(['limitHits' => []], $searchExecutionResult->meta(), 'search() should expose meta.limitHits without duplicating limitHit.');

	$detailsResult = $client->getDetails('contract-1|SNOW|NHx8');
	assertTrue(is_array($detailsResult['result']['offer'] ?? null), 'getDetails() should return result.offer array.');
	assertSameValue('contract-1|SNOW|NHx8', $detailsResult['result']['offer']['Base']['OfferId'] ?? null, 'getDetails() should expose OfferId.');
	assertSameValue('Contract Hotel', $detailsResult['result']['offer']['Accommodation']['Name'] ?? null, 'getDetails() payload mismatch.');

	$liveResult = $client->getLiveAvailability('offer-123|SNOW|NHx8', null, true);
	assertTrue(is_array($liveResult), 'getLiveAvailability() should return array.');
	assertTrue(is_array($liveResult['results'] ?? null), 'getLiveAvailability() should return results list.');
	assertTrue(!array_key_exists('debug', $liveResult), 'getLiveAvailability() should return sanitized payload without debug.');

	$portalResult = $client->portalSearch(['sortBy' => 'price']);
	assertTrue(is_array($portalResult), 'portalSearch() should return an array.');
	assertTrue(is_array($portalResult['offers'] ?? null), 'portalSearch() should return offers array.');
	assertSameValue('portal-offer-1', $portalResult['offers'][0]['id'] ?? null, 'portalSearch() should expose endpoint payload unchanged.');
	assertSameValue(true, $client->clearCache(), 'clearCache() should return boolean success status.');

	$client->registerOperation(new class implements OperationInterface {
		public function key(): string
		{
			return 'search';
		}
	});

	assertThrows(
		static fn() => $client->executeSearch(searchRequest([], [], [], ['offerList' => []])),
		MerlinxGetterException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'Operation is not registered: search'), 'Expected operation type-safety guard message.');
		}
	);

	echo "PASS: MerlinxGetterClient public methods return stable top-level contracts and enforce operation type safety.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
