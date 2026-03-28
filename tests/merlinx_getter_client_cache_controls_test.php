<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\MerlinxGetterClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$cacheDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
		. DIRECTORY_SEPARATOR
		. 'merlinx-getter-client-cache-controls-'
		. str_replace('.', '-', uniqid('', true));
	if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
		throw new RuntimeException('Unable to create client cache-control test directory.');
	}

	$searchRequests = 0;
	$tokenRequests = 0;
	$detailsRequests = 0;
	$checkOnlineRequests = 0;
	$searchResponses = [
		[
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => [
							'Base' => [
								'OfferId' => 'search-cache-1',
							],
						],
					],
				],
			],
		],
		[
			'offerList' => [
				'more' => false,
				'items' => [
					[
						'offer' => [
							'Base' => [
								'OfferId' => 'search-cache-2',
							],
						],
					],
				],
			],
		],
	];
	$detailsResponses = [
		[
			'result' => [
				'offer' => [
					'Base' => ['OfferId' => 'details-cache-1|SNOW|NHx8'],
				],
			],
		],
		[
			'result' => [
				'offer' => [
					'Base' => ['OfferId' => 'details-cache-2|SNOW|NHx8'],
				],
			],
		],
	];

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$searchRequests, &$tokenRequests, &$detailsRequests, &$checkOnlineRequests, &$searchResponses, &$detailsResponses): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			$tokenRequests++;
			return new MockResponse(json_encode(['token' => 'token-' . $tokenRequests], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/search')) {
			$searchRequests++;
			$payload = array_shift($searchResponses);
			if (!is_array($payload)) {
				return new MockResponse('{"error":"unexpected search request"}', ['http_code' => 500]);
			}
			return new MockResponse(json_encode($payload, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/details')) {
			$detailsRequests++;
			$payload = array_shift($detailsResponses);
			if (!is_array($payload)) {
				return new MockResponse('{"error":"unexpected details request"}', ['http_code' => 500]);
			}
			return new MockResponse(json_encode($payload, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/checkonline')) {
			$checkOnlineRequests++;
			return new MockResponse(json_encode([
				'results' => [
					['requestNo' => $checkOnlineRequests, 'status' => 'ok'],
				],
			], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse('{"error":"unexpected request"}', ['http_code' => 500]);
	});

	$config = baseMerlinxConfig([
		'cache' => [
			'dir' => $cacheDir,
			'token' => ['ttlSeconds' => 600],
			'search' => [
				'ttlSeconds' => 600,
				'staleSeconds' => 900,
			],
			'liveAvailability' => [
				'ttlSeconds' => 30,
			],
		],
	]);

	$client = new MerlinxGetterClient(MerlinxGetterConfig::fromArray($config), $mock);
	$search = ['Base' => ['Availability' => ['available']]];
	$views = ['offerList' => ['limit' => 5]];

	$first = $client->executeSearch(searchRequest($search, [], [], $views))->response();
	assertSameValue('search-cache-1', $first['offerList']['items']['search-cache-1']['offer']['Base']['OfferId'] ?? null, 'Initial search payload mismatch.');
	assertSameValue(1, $searchRequests, 'Initial search should hit upstream once.');
	assertSameValue(1, $tokenRequests, 'Initial search should request token once.');

	$second = $client->executeSearch(searchRequest($search, [], [], $views))->response();
	assertSameValue('search-cache-1', $second['offerList']['items']['search-cache-1']['offer']['Base']['OfferId'] ?? null, 'Cached search payload mismatch.');
	assertSameValue(1, $searchRequests, 'Cached search should not hit upstream search endpoint.');
	assertSameValue(1, $tokenRequests, 'Cached search should not fetch a new token.');

	$detailsFirst = $client->getDetails('details-cache-1|SNOW|NHx8');
	$detailsSecond = $client->getDetails('details-cache-1|SNOW|NHx8');
	assertSameValue('details-cache-1|SNOW|NHx8', $detailsFirst['result']['offer']['Base']['OfferId'] ?? null, 'Initial details payload mismatch.');
	assertSameValue('details-cache-1|SNOW|NHx8', $detailsSecond['result']['offer']['Base']['OfferId'] ?? null, 'Cached details payload mismatch.');
	assertSameValue(1, $detailsRequests, 'Cached details should not hit upstream details endpoint.');

	$live1 = $client->getLiveAvailability('offer-123|SNOW|NHx8', null, true);
	$live2 = $client->getLiveAvailability('offer-123|SNOW|NHx8', null, true);
	$live3 = $client->getLiveAvailability('offer-123|SNOW|NHx8', null, true, true);
	assertSameValue(2, $checkOnlineRequests, 'Live availability should be cached within TTL and force mode should bypass cache.');
	assertSameValue(1, $live1['results'][0]['requestNo'] ?? null, 'First live availability marker mismatch.');
	assertSameValue(1, $live2['results'][0]['requestNo'] ?? null, 'Second live availability marker should come from cache.');
	assertSameValue(2, $live3['results'][0]['requestNo'] ?? null, 'Forced live availability marker mismatch.');

	assertSameValue(true, $client->clearCache(), 'clearCache() should report success.');

	$afterClear = $client->executeSearch(searchRequest($search, [], [], $views))->response();
	assertSameValue('search-cache-2', $afterClear['offerList']['items']['search-cache-2']['offer']['Base']['OfferId'] ?? null, 'Search after clear should fetch fresh payload.');
	assertSameValue(2, $searchRequests, 'Search after clear should hit upstream again.');
	assertSameValue(2, $tokenRequests, 'Search after clear should refresh token due runtime + persistent cache reset.');

	$detailsAfterClear = $client->getDetails('details-cache-1|SNOW|NHx8');
	assertSameValue('details-cache-2|SNOW|NHx8', $detailsAfterClear['result']['offer']['Base']['OfferId'] ?? null, 'Details after clear should fetch fresh payload.');
	assertSameValue(2, $detailsRequests, 'Details after clear should hit upstream again.');

	echo "PASS: MerlinxGetterClient clearCache resets token/search/details/live-availability cache state and getLiveAvailability supports cache + force bypass.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
