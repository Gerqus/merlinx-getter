<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Skionline\MerlinxGetter\Exception\ResponseFormatException;
use Skionline\MerlinxGetter\Http\AuthTokenProvider;
use Skionline\MerlinxGetter\Http\MerlinxHttpClient;
use Skionline\MerlinxGetter\Operation\SearchOperation;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$buildOperation = static function (callable $callback): SearchOperation {
		$mock = new MockHttpClient($callback);
		$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig());
		$tokenProvider = new AuthTokenProvider($config, $mock);
		$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
		return new SearchOperation($config, $httpClient);
	};

	$serverErrorOperation = $buildOperation(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			return new MockResponse('{"error":"upstream"}', ['http_code' => 500]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	assertThrows(
		static fn() => $serverErrorOperation->execute(searchRequest([], [], [], ['offerList' => []])),
		HttpRequestException::class,
		static function (Throwable $e): void {
			assertSameValue(500, $e->statusCode(), 'Expected HTTP 500 status to be carried by HttpRequestException.');
			assertTrue(str_contains((string) $e->responseBody(), 'upstream'), 'Expected response body to be attached for server errors.');
			assertTrue(str_contains($e->getMessage(), 'server error'), 'Expected exception message to classify 5xx as server error.');
			assertTrue(str_contains($e->getMessage(), 'POST /v5/data/travel/search'), 'Expected exception message to include request endpoint and method.');
			assertTrue(str_contains($e->getMessage(), 'Response snippet:'), 'Expected exception message to include response snippet.');
			assertTrue(str_contains($e->getMessage(), 'Request payload snippet:'), 'Expected exception message to include request payload snippet.');
		}
	);

	$rateLimitPayload = file_get_contents(__DIR__ . '/fixtures/search/rate_limited_payload.txt');
	if (!is_string($rateLimitPayload)) {
		throw new RuntimeException('Unable to read rate-limited payload fixture.');
	}

	$rateLimitRequestCount = 0;
	$rateLimitOperation = $buildOperation(function (string $method, string $url, array $options = []) use (&$rateLimitRequestCount, $rateLimitPayload): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			$rateLimitRequestCount++;
			return new MockResponse($rateLimitPayload, ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	assertThrows(
		static fn() => $rateLimitOperation->execute(searchRequest([], [], [], ['offerList' => []], [
			'rateLimitRetryMaxAttempts' => 1,
			'rateLimitRetryDelayMs' => 0,
			'rateLimitRetryBackoffMultiplier' => 2.0,
			'rateLimitRetryMaxDelayMs' => 0,
		])),
		HttpRequestException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'rate limit persisted'), 'Expected rate-limit persistence message.');
		}
	);
	assertSameValue(2, $rateLimitRequestCount, 'Expected retries to stop after configured max attempts.');

	$invalidJsonOperation = $buildOperation(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			return new MockResponse('{invalid-json', ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	assertThrows(
		static fn() => $invalidJsonOperation->execute(searchRequest([], [], [], ['offerList' => []])),
		ResponseFormatException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'invalid JSON'), 'Expected invalid JSON error message.');
		}
	);

	$scalarJsonOperation = $buildOperation(static function (string $method, string $url, array $options = []): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}
		if (str_contains($url, '/v5/data/travel/search')) {
			return new MockResponse('"ok"', ['http_code' => 200]);
		}
		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	assertThrows(
		static fn() => $scalarJsonOperation->execute(searchRequest([], [], [], ['offerList' => []])),
		ResponseFormatException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'unexpected format'), 'Expected unexpected format message for non-object JSON.');
		}
	);

	echo "PASS: SearchOperation propagates HTTP/rate-limit/format errors through stable exception contracts.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
