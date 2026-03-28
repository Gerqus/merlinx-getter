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
		. 'merlinx-getter-details-cache-'
		. str_replace('.', '-', uniqid('', true));
	if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
		throw new RuntimeException('Unable to create details cache test directory.');
	}

	$sharedMainPrefix = str_repeat('A', 70);
	$aliasOfferIdA = $sharedMainPrefix . 'TAIL_A|SNOW|NHx8';
	$aliasOfferIdB = $sharedMainPrefix . 'TAIL_B|SNOW|NHx8';
	$malformedOfferId = 'not-a-composite-offer-id';
	$missingPaxOfferId = $sharedMainPrefix . 'TAIL_C|SNOW';

	$detailsRequests = [];
	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$detailsRequests): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/details')) {
			$query = (string) parse_url($url, PHP_URL_QUERY);
			$offerId = '';
			if (preg_match('/(?:^|&)Base\\.OfferId=([^&]+)/', $query, $matches) === 1) {
				$offerId = rawurldecode($matches[1]);
			}
			$detailsRequests[] = $offerId;

			return new MockResponse(json_encode([
				'result' => [
					'offer' => [
						'Base' => [
							'OfferId' => $offerId,
						],
						'Accommodation' => [
							'Name' => 'Hotel for ' . $offerId,
						],
					],
				],
			], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$client = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'cache' => [
			'dir' => $cacheDir,
			'token' => ['ttlSeconds' => 600],
			'search' => [
				'ttlSeconds' => 600,
				'staleSeconds' => 900,
			],
		],
	])), $mock);

	$first = $client->getDetails($aliasOfferIdA);
	$second = $client->getDetails($aliasOfferIdB);
	assertSameValue(1, count($detailsRequests), 'Alias-equivalent OfferIds should reuse one cached details fetch.');
	assertSameValue($first, $second, 'Alias-equivalent OfferIds should return the same cached payload.');

	$malformedFirst = $client->getDetails($malformedOfferId);
	$malformedSecond = $client->getDetails($malformedOfferId);
	assertSameValue(3, count($detailsRequests), 'Malformed OfferIds should bypass cache and fetch every time.');
	assertSameValue($malformedFirst['result']['offer']['Base']['OfferId'] ?? null, $malformedSecond['result']['offer']['Base']['OfferId'] ?? null, 'Malformed fetch responses should still echo returned upstream payload.');

	$missingPaxFirst = $client->getDetails($missingPaxOfferId);
	$missingPaxSecond = $client->getDetails($missingPaxOfferId);
	assertSameValue(5, count($detailsRequests), 'OfferIds missing pax segment should bypass cache and fetch every time.');
	assertSameValue($missingPaxOfferId, $missingPaxFirst['result']['offer']['Base']['OfferId'] ?? null, 'Missing-pax payload mismatch.');
	assertSameValue($missingPaxOfferId, $missingPaxSecond['result']['offer']['Base']['OfferId'] ?? null, 'Missing-pax payload mismatch on second fetch.');

	assertSameValue(true, $client->clearCache(), 'clearCache() should succeed after details caching.');
	$afterClear = $client->getDetails($aliasOfferIdA);
	assertSameValue(6, count($detailsRequests), 'clearCache() should evict cached details payloads.');
	assertSameValue($aliasOfferIdA, $afterClear['result']['offer']['Base']['OfferId'] ?? null, 'Fresh details payload mismatch after clearCache().');

	echo "PASS: getDetails caches by alias key, bypasses malformed inputs, and clearCache evicts details entries.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
