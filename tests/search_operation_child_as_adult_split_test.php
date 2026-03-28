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
	$capturedRequests = [];
	$searchRequestCount = 0;
	$responses = [
		[
			'offerList' => [
				'more' => false,
				'items' => [
					'normal-key' => ['offer' => ['Base' => ['OfferId' => 'normal-offer|VITX|NHx8']]],
				],
			],
		],
		[
			'offerList' => [
				'more' => false,
				'items' => [
					'special-key' => ['offer' => ['Base' => ['OfferId' => 'special-offer|SNOW|NHx8']]],
				],
			],
		],
	];

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$capturedRequests, &$searchRequestCount, &$responses): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$searchRequestCount++;
			$capturedRequests[] = extractJsonPayload($options);
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
			'operators' => ['VITX', 'SNOW'],
			'operator_policies' => [
				'child_as_adult_operators' => ['SNOW'],
			],
			'conditions' => [
				['search' => [], 'filter' => []],
			],
		],
	]));

	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest([
		'Base' => [
			'ParticipantsList' => [
				['code' => 'ADULT'],
				['birthdate' => '2015-02-01'],
			],
		],
	], [], [], ['offerList' => []]))->response();

	assertSameValue(2, $searchRequestCount, 'Expected split into two /search requests for mixed operators with child birthdate.');
	assertSameValue(2, count($capturedRequests), 'Expected two captured /search payloads.');

	$normalPayload = null;
	$specialPayload = null;
	foreach ($capturedRequests as $payload) {
		if (!is_array($payload)) {
			continue;
		}
		$ops = $payload['conditions']['search']['Base']['Operator'] ?? [];
		if ($ops === ['VITX']) {
			$normalPayload = $payload;
		}
		if ($ops === ['SNOW']) {
			$specialPayload = $payload;
		}
	}

	assertTrue(is_array($normalPayload), 'Expected normal operator request payload.');
	assertTrue(is_array($specialPayload), 'Expected special operator request payload.');
	assertSameValue('2015-02-01', $normalPayload['conditions']['search']['Base']['ParticipantsList'][1]['birthdate'] ?? null, 'Normal payload should preserve child birthdate participant.');
	assertSameValue('ADULT', $specialPayload['conditions']['search']['Base']['ParticipantsList'][0]['code'] ?? null, 'Special payload first participant should be ADULT.');
	assertSameValue('ADULT', $specialPayload['conditions']['search']['Base']['ParticipantsList'][1]['code'] ?? null, 'Special payload child should be rewritten as ADULT.');

	$items = $result['offerList']['items'] ?? null;
	assertTrue(is_array($items), 'Expected merged offerList.items in result.');
	assertSameValue(2, count($items), 'Merged result should include items from both split requests.');
	assertTrue(isset($items['normal-offer|VITX|NHx8']), 'Merged result missing normal-operator offer.');
	assertTrue(isset($items['special-offer|SNOW|NHx8']), 'Merged result missing special-operator offer.');

	$dedupeRequests = 0;
	$dedupeMock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$dedupeRequests): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$dedupeRequests++;
			return new MockResponse(json_encode([
				'fieldValues' => [
					'fieldValues' => [
						'Base.StartDate' => ['2026-03-07'],
					],
					'more' => false,
					'pageBookmark' => '',
				],
			], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$dedupeConfig = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'operators' => ['SNOW'],
			'operator_policies' => [
				'child_as_adult_operators' => ['SNOW'],
			],
			'conditions' => [
				['search' => [], 'filter' => []],
				['search' => [], 'filter' => []],
			],
		],
	]));

	$dedupeTokenProvider = new AuthTokenProvider($dedupeConfig, $dedupeMock);
	$dedupeHttpClient = new MerlinxHttpClient($dedupeConfig, $dedupeTokenProvider, $dedupeMock);
	$dedupeOperation = new SearchOperation($dedupeConfig, $dedupeHttpClient);

	$dedupeOperation->execute(searchRequest([
		'Base' => [
			'ParticipantsList' => [
				['code' => 'ADULT'],
				['birthdate' => '2015-02-01'],
			],
		],
	], [], [], ['fieldValues' => ['fieldList' => ['Base.StartDate']]]));

	assertSameValue(1, $dedupeRequests, 'Expected query dedupe to collapse identical child-as-adult split payloads.');

	$interleavedCapturedRequests = [];
	$interleavedSearchRequestCount = 0;
	$interleavedResponses = [
		[
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => ['Base' => ['OfferId' => 'shared-order-offer|NHx8']],
						'marker' => 'VITX',
					],
				],
			],
		],
		[
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => ['Base' => ['OfferId' => 'shared-order-offer|NHx8']],
						'marker' => 'SNOW',
					],
				],
			],
		],
		[
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => ['Base' => ['OfferId' => 'shared-order-offer|NHx8']],
						'marker' => 'LULU',
					],
				],
			],
		],
		[
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => ['Base' => ['OfferId' => 'shared-order-offer|NHx8']],
						'marker' => 'DER',
					],
				],
			],
		],
	];

	$interleavedMock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$interleavedCapturedRequests, &$interleavedSearchRequestCount, &$interleavedResponses): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$interleavedSearchRequestCount++;
			$interleavedCapturedRequests[] = extractJsonPayload($options);
			$response = array_shift($interleavedResponses);
			if (!is_array($response)) {
				return new MockResponse(json_encode(['error' => 'unexpected extra request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
			}

			return new MockResponse(json_encode($response, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$interleavedConfig = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'operators' => ['VITX', 'SNOW', 'LULU', 'DER'],
			'operator_policies' => [
				'child_as_adult_operators' => ['SNOW', 'DER'],
			],
			'conditions' => [
				['search' => [], 'filter' => []],
			],
		],
	]));

	$interleavedTokenProvider = new AuthTokenProvider($interleavedConfig, $interleavedMock);
	$interleavedHttpClient = new MerlinxHttpClient($interleavedConfig, $interleavedTokenProvider, $interleavedMock);
	$interleavedOperation = new SearchOperation($interleavedConfig, $interleavedHttpClient);

	$interleavedResult = $interleavedOperation->execute(searchRequest([
		'Base' => [
			'ParticipantsList' => [
				['code' => 'ADULT'],
				['birthdate' => '2015-02-01'],
			],
		],
	], [], [], ['offerList' => []]))->response();

	assertSameValue(4, $interleavedSearchRequestCount, 'Expected one search request per contiguous operator treatment run.');
	assertSameValue(['VITX'], $interleavedCapturedRequests[0]['conditions']['search']['Base']['Operator'] ?? null, 'First run should preserve the first normal operator.');
	assertSameValue(['SNOW'], $interleavedCapturedRequests[1]['conditions']['search']['Base']['Operator'] ?? null, 'Second run should preserve the first child-as-adult operator.');
	assertSameValue(['LULU'], $interleavedCapturedRequests[2]['conditions']['search']['Base']['Operator'] ?? null, 'Third run should preserve the second normal operator.');
	assertSameValue(['DER'], $interleavedCapturedRequests[3]['conditions']['search']['Base']['Operator'] ?? null, 'Fourth run should preserve the second child-as-adult operator.');

	assertSameValue('2015-02-01', $interleavedCapturedRequests[0]['conditions']['search']['Base']['ParticipantsList'][1]['birthdate'] ?? null, 'Normal runs should preserve child participants.');
	assertSameValue('ADULT', $interleavedCapturedRequests[1]['conditions']['search']['Base']['ParticipantsList'][0]['code'] ?? null, 'Special runs should rewrite the first participant to ADULT.');
	assertSameValue('ADULT', $interleavedCapturedRequests[1]['conditions']['search']['Base']['ParticipantsList'][1]['code'] ?? null, 'Special runs should rewrite children to ADULT.');
	assertSameValue('2015-02-01', $interleavedCapturedRequests[2]['conditions']['search']['Base']['ParticipantsList'][1]['birthdate'] ?? null, 'Later normal runs should still preserve child participants.');
	assertSameValue('ADULT', $interleavedCapturedRequests[3]['conditions']['search']['Base']['ParticipantsList'][1]['code'] ?? null, 'Later special runs should still rewrite children to ADULT.');

	$interleavedItems = $interleavedResult['offerList']['items'] ?? null;
	assertTrue(is_array($interleavedItems), 'Expected merged offerList.items in the interleaved run result.');
	assertSameValue('VITX', $interleavedItems['shared-order-offer|NHx8']['marker'] ?? null, 'Merged offer data should preserve the first configured run when duplicate offers are returned.');

	echo "PASS: SearchOperation preserves contiguous child-as-adult runs, merge order, and query dedupe.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
