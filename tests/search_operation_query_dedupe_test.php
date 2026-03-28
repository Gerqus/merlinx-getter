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
	$searchRequests = 0;
	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$searchRequests): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$searchRequests++;
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

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'conditions' => [
				['search' => [], 'filter' => []],
				['search' => ['Base' => ['Operator' => ['SNOW']]], 'filter' => []],
			],
		],
	]));

	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest(
		search: [],
		filter: [],
		results: [],
		views: ['fieldValues' => ['fieldList' => ['Base.StartDate']]],
	))->response();

	assertTrue(is_array($result), 'Result should be array.');
	assertSameValue(1, $searchRequests, 'Duplicate configured query variants should be deduped to one HTTP request.');
	assertTrue(isset($result['fieldValues']['Base.StartDate']), 'Expected fieldValues payload to be returned.');

	echo "PASS: SearchOperation dedupes duplicate query bodies from search_engine.conditions.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
