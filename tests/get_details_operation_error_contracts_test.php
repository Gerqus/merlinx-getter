<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Skionline\MerlinxGetter\Exception\InvalidInputException;
use Skionline\MerlinxGetter\Exception\ResponseFormatException;
use Skionline\MerlinxGetter\MerlinxGetterClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/bootstrap.php';

try {
	$happyCapture = null;
	$happyMock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$happyCapture): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/details')) {
			$query = (string) parse_url($url, PHP_URL_QUERY);
			if (preg_match('/(?:^|&)Base\\.OfferId=([^&]+)/', $query, $matches) === 1) {
				$happyCapture = rawurldecode($matches[1]);
			}

			return new MockResponse(json_encode([
				'result' => [
					'offer' => [
						'Base' => ['OfferId' => 'offer-789|SNOW|NHx8'],
					],
				],
			], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$happyClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $happyMock);
	$happyClient->getDetails('  offer-789|SNOW|NHx8  ');
	assertSameValue('offer-789|SNOW|NHx8', $happyCapture, 'OfferId should be trimmed before sending.');

	assertThrows(
		static fn() => $happyClient->getDetails(" \t\n"),
		InvalidInputException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'OfferId is required'), 'Expected validation message for empty OfferId.');
		}
	);

	$errorClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), new MockHttpClient(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/details')) {
			return new MockResponse('{"error":"boom"}', ['http_code' => 503]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	}));

	assertThrows(
		static fn() => $errorClient->getDetails('offer-err|SNOW|NHx8'),
		HttpRequestException::class,
		static function (Throwable $e): void {
			assertSameValue(503, $e->statusCode(), 'Expected status code in HttpRequestException.');
			assertTrue(str_contains((string) $e->responseBody(), 'boom'), 'Expected response body in HttpRequestException.');
		}
	);

	$invalidJsonClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), new MockHttpClient(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/details')) {
			return new MockResponse('{not-json', ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	}));

	assertThrows(
		static fn() => $invalidJsonClient->getDetails('offer-json|SNOW|NHx8'),
		ResponseFormatException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'invalid JSON'), 'Expected invalid JSON message.');
		}
	);

	$scalarJsonClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), new MockHttpClient(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/details')) {
			return new MockResponse('"ok"', ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	}));

	assertThrows(
		static fn() => $scalarJsonClient->getDetails('offer-scalar|SNOW|NHx8'),
		ResponseFormatException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'unexpected format'), 'Expected unexpected format message for scalar JSON response.');
		}
	);

	$missingOfferClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), new MockHttpClient(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/details')) {
			return new MockResponse(json_encode(['result' => ['debug' => 'x']], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	}));

	assertThrows(
		static fn() => $missingOfferClient->getDetails('offer-missing|SNOW|NHx8'),
		ResponseFormatException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'missing result.offer'), 'Expected missing result.offer message.');
		}
	);

	echo "PASS: getDetails validates input and preserves HTTP/format exception contracts.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
