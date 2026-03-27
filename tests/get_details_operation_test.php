<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\MerlinxGetterClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/bootstrap.php';

try {
	$capturedMethod = null;
	$capturedOfferId = null;
	$detailsPayload = [
		'result' => [
			'offer' => [
				'Base' => [
					'OfferId' => 'DETAILS_MAIN_1234567890|SNOW|NHx8',
					'StartDate' => '2026-12-20',
					'Availability' => ['base' => 'available'],
				],
				'Accommodation' => [
					'Name' => 'Details Hotel',
				],
			],
			'debug' => 'should be removed',
		],
		'debug' => 'should be removed',
	];

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$capturedMethod, &$capturedOfferId, $detailsPayload): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/details')) {
			$capturedMethod = $method;
			$query = (string) parse_url($url, PHP_URL_QUERY);
			if (preg_match('/(?:^|&)Base\\.OfferId=([^&]+)/', $query, $matches) === 1) {
				$capturedOfferId = rawurldecode($matches[1]);
			}

			return new MockResponse(json_encode($detailsPayload, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$client = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $mock);
	$result = $client->getDetails('DETAILS_MAIN_1234567890|SNOW|NHx8');

	assertSameValue('GET', $capturedMethod, 'getDetails() should use GET.');
	assertSameValue('DETAILS_MAIN_1234567890|SNOW|NHx8', $capturedOfferId, 'getDetails() should send Base.OfferId query parameter unchanged.');
	assertTrue(is_array($result), 'getDetails() should return an array.');
	assertTrue(is_array($result['result']['offer'] ?? null), 'getDetails() should return result.offer payload.');
	assertTrue(!array_key_exists('debug', $result), 'Top-level debug field should be removed.');
	assertTrue(!array_key_exists('debug', $result['result'] ?? []), 'Nested debug field should be removed.');
	assertSameValue('Details Hotel', $result['result']['offer']['Accommodation']['Name'] ?? null, 'Accommodation name mismatch.');
	assertSameValue('available', $result['result']['offer']['Base']['Availability']['base'] ?? null, 'Availability base mismatch.');

	echo "PASS: getDetails calls /details and returns sanitized raw payload.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
