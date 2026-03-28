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
	$fieldValuesPayload = fixtureJson('search/field_values_basic.json');
	$fieldValuesPayload['fieldValues']['fieldValues']['Accommodation.Attributes'] = [
		'location_mountains',
		'facility_pool',
		'facility_pool',
	];
	$responses = [
		$fieldValuesPayload,
		$fieldValuesPayload,
	];

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$responses): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$response = array_shift($responses);
			if ($response === null) {
				return new MockResponse(json_encode(['error' => 'no more responses'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
			}
			return new MockResponse(json_encode($response, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'conditions' => [
				['search' => [], 'filter' => []],
				['search' => ['Accommodation' => ['Attributes' => ['+location_mountains']]], 'filter' => []],
			],
		],
	]));

	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest([], [], [], [
		'fieldValues' => ['fieldList' => ['Accommodation.Room', 'Accommodation.XService', 'Base.StartDate']],
	]))->response();

	$fieldValues = $result['fieldValues'] ?? null;
	assertTrue(is_array($fieldValues), 'fieldValues missing or invalid.');
	assertTrue(!array_key_exists('more', $fieldValues), 'fieldValues should not include view metadata: more.');
	assertTrue(!array_key_exists('pageBookmark', $fieldValues), 'fieldValues should not include view metadata: pageBookmark.');

	$rooms = $fieldValues['Accommodation.Room'] ?? null;
	assertTrue(is_array($rooms), 'Accommodation.Room missing or invalid.');
	$roomLabel = $rooms['DBL'] ?? null;
	assertTrue(is_string($roomLabel), 'Accommodation.Room.DBL should be a string label.');
	assertSameValue('Pokój 2 os.', $roomLabel, 'Accommodation.Room.DBL label mismatch.');

	$board = $fieldValues['Accommodation.XService'] ?? null;
	assertTrue(is_array($board), 'Accommodation.XService missing or invalid.');
	$boardKeys = array_map('strval', array_keys($board));
	sort($boardKeys);
	assertSameValue(['2', '6'], $boardKeys, 'Accommodation.XService keys should be preserved.');
	foreach ($board as $label) {
		assertTrue(is_string($label), 'Accommodation.XService labels should be strings.');
	}

	$dates = $fieldValues['Base.StartDate'] ?? null;
	assertTrue(is_array($dates), 'Base.StartDate missing or invalid.');
	assertSameValue(count(array_unique($dates)), count($dates), 'Base.StartDate should not contain duplicates.');
	assertSameValue(['2026-03-07', '2026-03-14'], $dates, 'Base.StartDate values mismatch.');

	$nights = $fieldValues['Base.NightsBeforeReturn'] ?? null;
	assertTrue(is_array($nights), 'Base.NightsBeforeReturn missing or invalid.');
	assertSameValue([7, 14], $nights, 'Base.NightsBeforeReturn values mismatch.');

	$attributes = $fieldValues['Accommodation.Attributes'] ?? null;
	assertTrue(is_array($attributes), 'Accommodation.Attributes missing or invalid.');
	assertSameValue(['facility_pool'], $attributes, 'Configured accommodation attributes should be pruned in-package.');

	echo "PASS: SearchOperation keeps fieldValues maps stable across query variants.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
