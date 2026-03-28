<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\MerlinxGetterClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$capturedCheckOnlinePayload = null;
	$checkOnlineRequests = 0;
	$successPayload = fixtureJson('checkonline/success.json');

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$capturedCheckOnlinePayload, &$checkOnlineRequests, $successPayload): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/checkonline')) {
			$checkOnlineRequests++;
			$capturedCheckOnlinePayload = extractJsonPayload($options);
			$payload = $successPayload;
			$payload['results'][0]['requestNo'] = $checkOnlineRequests;
			return new MockResponse(json_encode($payload, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$client = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $mock);
	$result = $client->getLiveAvailability('offer-123|SNOW|NHx8', null, true);
	$cached = $client->getLiveAvailability('offer-123|SNOW|NHx8', null, true);
	$forced = $client->getLiveAvailability('offer-123|SNOW|NHx8', null, true, true);

	assertTrue(is_array($capturedCheckOnlinePayload), 'Checkonline payload was not captured.');
	assertSameValue(['checkstatus'], $capturedCheckOnlinePayload['actions'] ?? null, 'Default action should be checkstatus.');
	assertSameValue(['offer-123|SNOW|NHx8'], $capturedCheckOnlinePayload['offerIds'] ?? null, 'OfferId payload mismatch.');
	assertSameValue(true, $capturedCheckOnlinePayload['includeTFG'] ?? null, 'includeTFG payload mismatch.');
	assertTrue(!array_key_exists('debug', $result), 'Sanitized response should not contain debug field.');
	assertSameValue('available', $result['results'][0]['offer']['Base']['Availability']['base'] ?? null, 'Availability base mismatch.');
	assertSameValue('1851.00', $result['results'][0]['offer']['Base']['Price']['FirstPerson']['amount'] ?? null, 'FirstPerson price amount mismatch.');
	assertSameValue('7166.00', $result['results'][0]['offer']['Base']['Price']['Total']['amount'] ?? null, 'Total price amount mismatch.');
	assertSameValue('checkstatus', $result['results'][0]['action'] ?? null, 'Action mismatch.');
	assertSameValue(2, $checkOnlineRequests, 'Second call should hit cache and forced call should bypass cache.');
	assertSameValue(1, $result['results'][0]['requestNo'] ?? null, 'First request marker mismatch.');
	assertSameValue(1, $cached['results'][0]['requestNo'] ?? null, 'Cached marker mismatch.');
	assertSameValue(2, $forced['results'][0]['requestNo'] ?? null, 'Forced marker mismatch.');

	echo "PASS: getLiveAvailability calls /checkonline, caches within TTL, and supports force bypass while returning sanitized raw payload.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
