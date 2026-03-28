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
	$responses = [
		fixtureJson('search/offer_list_page1.json'),
		fixtureJson('search/offer_list_page2.json'),
	];

	$requests = [];
	$searchRequestCount = 0;
	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$responses, &$requests, &$searchRequestCount): MockResponse {
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

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig());
	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest([], [], [], ['offerList' => ['limit' => 500]]))->response();

	assertSameValue(2, $searchRequestCount, 'Expected two /search requests due to pagination.');
	$secondPayload = $requests[1]['payload'] ?? [];
	$bookmark = $secondPayload['views']['offerList']['previousPageBookmark'] ?? null;
	assertSameValue('bm-1', $bookmark, 'Second request should include previousPageBookmark.');

	$items = $result['offerList']['items'] ?? null;
	assertTrue(is_array($items), 'Merged offerList.items is missing or invalid.');
	assertSameValue(2, count($items), 'Merged offerList.items should include both pages.');
	assertSameValue(
		['offer-key-1|SNOW|NHx8', 'offer-key-2|SNOW|NHx8'],
		array_keys($items),
		'Merged items should be keyed by OfferId in first-seen order.'
	);
	assertSameValue('offer-key-1|SNOW|NHx8', $items['offer-key-1|SNOW|NHx8']['offer']['Base']['OfferId'] ?? null, 'Merged items should keep first page first offer.');
	assertSameValue('offer-key-2|SNOW|NHx8', $items['offer-key-2|SNOW|NHx8']['offer']['Base']['OfferId'] ?? null, 'Merged items should include second page offer.');
	assertSameValue(false, $result['offerList']['more'] ?? null, 'offerList.more should be false after pagination completes.');
	assertSameValue('bm-2', $result['offerList']['pageBookmark'] ?? null, 'offerList.pageBookmark should come from the last page.');

	echo "PASS: SearchOperation paginates offerList using pageBookmark.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
