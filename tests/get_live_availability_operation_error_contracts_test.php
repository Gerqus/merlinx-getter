<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Skionline\MerlinxGetter\Exception\InvalidInputException;
use Skionline\MerlinxGetter\Exception\ResponseFormatException;
use Skionline\MerlinxGetter\MerlinxGetterClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$payloadCapture = null;
	$happyMock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$payloadCapture): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/checkonline')) {
			$payloadCapture = extractJsonPayload($options);
			return new MockResponse(json_encode(['results' => [['OfferId' => 'offer-789', 'action' => 'checkstatus', 'offer' => ['Base' => ['Availability' => ['base' => 'available']]]]]], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$happyClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $happyMock);
	$happyClient->getLiveAvailability('  offer-789  ', '   ', false);
	$happyClient->getLiveAvailability('offer-789', 'checkstatus', false, true);

	assertTrue(is_array($payloadCapture), 'Expected request payload to be captured for default-action assertion.');
	assertSameValue(['checkstatus'], $payloadCapture['actions'] ?? null, 'Blank action should default to checkstatus.');
	assertSameValue(['offer-789'], $payloadCapture['offerIds'] ?? null, 'OfferId should be trimmed before sending.');
	assertSameValue(false, $payloadCapture['includeTFG'] ?? null, 'includeTFG should pass through unchanged.');

	assertThrows(
		static fn() => $happyClient->getLiveAvailability(" \t\n", 'checkstatus', true),
		InvalidInputException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'OfferId is required'), 'Expected validation message for empty OfferId.');
		}
	);

	$errorMock = new MockHttpClient(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/checkonline')) {
			return new MockResponse('{"error":"boom"}', ['http_code' => 503]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$errorClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $errorMock);
	assertThrows(
		static fn() => $errorClient->getLiveAvailability('offer-err', 'checkstatus', true),
		HttpRequestException::class,
		static function (Throwable $e): void {
			assertSameValue(503, $e->statusCode(), 'Expected status code in HttpRequestException.');
			assertTrue(str_contains((string) $e->responseBody(), 'boom'), 'Expected response body in HttpRequestException.');
		}
	);

	$invalidJsonMock = new MockHttpClient(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/checkonline')) {
			return new MockResponse('{not-json', ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$invalidJsonClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $invalidJsonMock);
	assertThrows(
		static fn() => $invalidJsonClient->getLiveAvailability('offer-json', 'checkstatus', true),
		ResponseFormatException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'invalid JSON'), 'Expected invalid JSON message.');
		}
	);

	$scalarJsonMock = new MockHttpClient(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/checkonline')) {
			return new MockResponse('"ok"', ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$scalarJsonClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $scalarJsonMock);
	assertThrows(
		static fn() => $scalarJsonClient->getLiveAvailability('offer-scalar', 'checkstatus', true),
		ResponseFormatException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'unexpected format'), 'Expected unexpected format message for scalar JSON response.');
		}
	);

	echo "PASS: GetLiveAvailabilityOperation validates input, defaults action, and preserves exception contracts.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
