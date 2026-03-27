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
	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$searchRequestCount, &$requests): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/search')) {
			$payload = extractJsonPayload($options);
			$requests[] = ['url' => $url, 'payload' => $payload];
			$searchRequestCount++;
			if ($searchRequestCount > 2) {
				return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
			}

			return new MockResponse(json_encode([
				'offerList' => [
					'more' => true,
					'pageBookmark' => 'same-bm',
					'items' => [
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'offer-' . $searchRequestCount . '|SNOW|NHx8',
								],
							],
						],
					],
				],
			], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig());
	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);

	$result = $operation->execute(searchRequest([], [], [], ['offerList' => ['limit' => 500]]))->response();

	assertSameValue(2, $searchRequestCount, 'Repeated pageBookmark should stop pagination after the first duplicate bookmark.');
	$secondPayload = $requests[1]['payload'] ?? [];
	$bookmark = $secondPayload['views']['offerList']['previousPageBookmark'] ?? null;
	assertSameValue('same-bm', $bookmark, 'Second request should include repeated previousPageBookmark.');

	$items = $result['offerList']['items'] ?? null;
	assertTrue(is_array($items), 'Merged offerList.items is missing or invalid.');
	assertSameValue(2, count($items), 'Merged offerList.items should include both fetched pages before stopping.');
	assertSameValue(
		['offer-1|SNOW|NHx8', 'offer-2|SNOW|NHx8'],
		array_keys($items),
		'Merged items should keep first-seen OfferId order.'
	);
	assertSameValue('offer-1|SNOW|NHx8', $items['offer-1|SNOW|NHx8']['offer']['Base']['OfferId'] ?? null, 'Merged items should keep first page first offer.');
	assertSameValue('offer-2|SNOW|NHx8', $items['offer-2|SNOW|NHx8']['offer']['Base']['OfferId'] ?? null, 'Merged items should keep second page offer.');
	assertSameValue(true, $result['offerList']['more'] ?? null, 'offerList.more should remain from the last received page.');
	assertSameValue('same-bm', $result['offerList']['pageBookmark'] ?? null, 'offerList.pageBookmark should remain from the last received page.');

	echo "PASS: SearchOperation stops pagination on repeated pageBookmark and returns merged partial result.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
