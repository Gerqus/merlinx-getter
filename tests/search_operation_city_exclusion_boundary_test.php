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
	$mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/search')) {
			return new MockResponse(json_encode([
				'customList' => [
					'items' => [
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'CUSTOM-EXCLUDED-CITY-OFFER',
									'XCity' => ['Name' => ' Limone Piemonte '],
								],
								'Accommodation' => [
									'Attributes' => ['location_city_break'],
								],
							],
						],
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'CUSTOM-EXCLUDED-ATTRIBUTE-OFFER',
									'XCity' => ['Name' => 'Genoa'],
								],
								'Accommodation' => [
									'Attributes' => ['location_ski_resorts'],
								],
							],
						],
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'CUSTOM-VISIBLE-CITY-OFFER',
									'XCity' => ['Name' => 'Genoa'],
								],
								'Accommodation' => [
									'Attributes' => ['location_city_break'],
								],
							],
						],
					],
				],
				'groupedList' => [
					'items' => [
						[
							'groupKeyValue' => 'EXCLUDED-CITY-GROUP',
							'offer' => [
								'Base' => [
									'OfferId' => 'GROUP-OFFER-1',
									'XCity' => ['Name' => 'Limone Piemonte'],
								],
								'Accommodation' => [
									'Attributes' => ['location_city_break'],
								],
							],
						],
						[
							'groupKeyValue' => 'EXCLUDED-ATTRIBUTE-GROUP',
							'offer' => [
								'Base' => [
									'OfferId' => 'GROUP-OFFER-ATTRIBUTE',
									'XCity' => ['Name' => 'Alassio'],
								],
								'Accommodation' => [
									'Attributes' => ['location_ski_resorts'],
								],
							],
						],
						[
							'groupKeyValue' => 'VISIBLE-CITY-GROUP',
							'offer' => [
								'Base' => [
									'OfferId' => 'GROUP-OFFER-2',
									'XCity' => ['Name' => 'Alassio'],
								],
								'Accommodation' => [
									'Attributes' => ['location_city_break'],
								],
							],
						],
					],
				],
				'offerList' => [
					'items' => [
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'EXCLUDED-CITY-OFFER',
									'XCity' => ['Name' => 'Limone Piemonte'],
								],
								'Accommodation' => [
									'Attributes' => ['location_city_break'],
								],
							],
						],
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'EXCLUDED-ATTRIBUTE-OFFER',
									'XCity' => ['Name' => 'Rome'],
								],
								'Accommodation' => [
									'Attributes' => ['location_ski_resorts'],
								],
							],
						],
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'VISIBLE-CITY-OFFER',
									'XCity' => ['Name' => 'Genoa'],
								],
								'Accommodation' => [
									'Attributes' => ['location_city_break'],
								],
							],
						],
					],
				],
				'fieldValues' => [
					'fieldValues' => [
						'Base.XCity' => ['Limone Piemonte', 'Genoa'],
						'Accommodation.XCity' => ['Alassio', 'limone-piemonte'],
						'Accommodation.Room' => [
							'DBL' => 'Pokój 2 os.',
							'SGL' => 'Pokój 1 os.',
						],
					],
					'more' => false,
					'pageBookmark' => '',
				],
				'unfilteredFieldValues' => [
					'fieldValues' => [
						'Base.XCity' => [' limone-piemonte ', 'Rome'],
					],
					'more' => false,
					'pageBookmark' => '',
				],
			], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'response_filters' => [
				'exclude_values_by_path' => [
					'fieldValues.Base.XCity' => ['Limone Piemonte'],
					'fieldValues.Accommodation.XCity' => ['Limone Piemonte'],
					'offer.Base.XCity.Name' => ['Limone Piemonte'],
					'offer.Accommodation.XCity.Name' => ['Limone Piemonte'],
					'offer.Accommodation.Attributes' => ['location_ski_resorts'],
				],
			],
		],
	]));

	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest([], [], [], [
		'customList' => ['limit' => 100],
		'groupedList' => ['limit' => 100],
		'offerList' => ['limit' => 100],
		'fieldValues' => ['fieldList' => ['Base.XCity', 'Accommodation.XCity', 'Accommodation.Room']],
		'unfilteredFieldValues' => ['fieldList' => ['Base.XCity']],
	]))->response();

	$groupedItems = $result['groupedList']['items'] ?? null;
	assertTrue(is_array($groupedItems), 'groupedList.items should exist.');
	assertSameValue(1, count($groupedItems), 'Package SearchOperation should filter groupedList by excluded city names.');
	assertTrue(!isset($groupedItems['EXCLUDED-CITY-GROUP']), 'Excluded city grouped item should be removed from package search response.');
	assertTrue(!isset($groupedItems['EXCLUDED-ATTRIBUTE-GROUP']), 'Grouped item with excluded accommodation attribute should be removed from package search response.');
	assertTrue(isset($groupedItems['VISIBLE-CITY-GROUP']), 'Visible city grouped item should be preserved in package search response.');

	$offerItems = $result['offerList']['items'] ?? null;
	assertTrue(is_array($offerItems), 'offerList.items should exist.');
	assertSameValue(1, count($offerItems), 'Package SearchOperation should filter offerList by excluded city names.');
	assertTrue(!isset($offerItems['EXCLUDED-CITY-OFFER']), 'Excluded city offer item should be removed from package search response.');
	assertTrue(!isset($offerItems['EXCLUDED-ATTRIBUTE-OFFER']), 'Offer item with excluded accommodation attribute should be removed from package search response.');
	assertTrue(isset($offerItems['VISIBLE-CITY-OFFER']), 'Visible city offer item should be preserved in package search response.');

	$customItems = $result['customList']['items'] ?? null;
	assertTrue(is_array($customItems), 'customList.items should exist.');
	assertSameValue(1, count($customItems), 'Package SearchOperation should filter generic item-list views by excluded city names.');
	assertTrue(!isset($customItems['CUSTOM-EXCLUDED-ATTRIBUTE-OFFER']), 'Custom item with excluded accommodation attribute should be removed from package search response.');
	$customItem = array_values($customItems)[0] ?? null;
	assertSameValue('CUSTOM-VISIBLE-CITY-OFFER', $customItem['offer']['Base']['OfferId'] ?? null, 'Visible city custom item should be preserved.');

	$fieldValues = $result['fieldValues'] ?? null;
	assertTrue(is_array($fieldValues), 'fieldValues should exist.');
	assertSameValue(['Genoa'], $fieldValues['Base.XCity'] ?? null, 'fieldValues.Base.XCity should exclude configured city names.');
	assertSameValue(['Alassio'], $fieldValues['Accommodation.XCity'] ?? null, 'fieldValues.Accommodation.XCity should exclude configured city names.');
	assertSameValue(
		['DBL' => 'Pokój 2 os.', 'SGL' => 'Pokój 1 os.'],
		$fieldValues['Accommodation.Room'] ?? null,
		'Unconfigured fieldValues keys should remain untouched.'
	);

	$unfilteredFieldValues = $result['unfilteredFieldValues'] ?? null;
	assertTrue(is_array($unfilteredFieldValues), 'unfilteredFieldValues should exist.');
	assertSameValue(['Rome'], $unfilteredFieldValues['Base.XCity'] ?? null, 'unfilteredFieldValues.Base.XCity should honor fieldValues path exclusions.');

	echo "PASS: SearchOperation applies configured path-based exclusions before merge for item views and fieldValues.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
