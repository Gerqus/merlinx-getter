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
	$firstPage = fixtureJson('search/offer_list_page1.json');
	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$searchRequestCount, &$requests, $firstPage): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/search')) {
			$searchRequestCount++;
			$requests[] = ['url' => $url, 'payload' => extractJsonPayload($options)];
			return new MockResponse(json_encode($firstPage, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig());
	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest([], [], [], ['offerList' => ['limit' => 1]]))->response();

	assertSameValue(1, $searchRequestCount, 'Expected exactly one /search request when explicit offerList.limit is reached on the first page.');
	$firstPayload = $requests[0]['payload'] ?? [];
	assertTrue(is_array($firstPayload), 'Expected first search payload to be captured.');
	$bookmark = $firstPayload['views']['offerList']['previousPageBookmark'] ?? null;
	assertSameValue(null, $bookmark, 'First request must not include previousPageBookmark.');
	assertSameValue(true, $result['offerList']['more'] ?? null, 'Result should preserve first-page "more" flag when follow-through is skipped.');
	assertSameValue('bm-1', $result['offerList']['pageBookmark'] ?? null, 'Result should preserve first-page bookmark when follow-through is skipped.');
	$items = $result['offerList']['items'] ?? null;
	assertTrue(is_array($items), 'offerList.items should exist.');
	assertSameValue(1, count($items), 'Only first-page items should be present when follow-through is skipped.');
	assertTrue(!array_is_list($items), 'Single-page response should preserve offerList.items associative map shape.');

	echo "PASS: SearchOperation stops follow-through when explicit offerList limit is already reached.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
