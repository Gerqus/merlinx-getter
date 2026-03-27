<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Skionline\MerlinxGetter\Exception\ResponseFormatException;
use Skionline\MerlinxGetter\MerlinxGetterClient;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/bootstrap.php';

try {
	$fallbackPayload = [
		'offers' => [],
		'query' => [],
		'error' => 'Nie udało się pobrać ofert. Spróbuj ponownie za chwilę.',
		'limitHit' => false,
	];

	$fallbackMock = new MockHttpClient(static function (string $method, string $url, array $options = []) use ($fallbackPayload): MockResponse {
		return new MockResponse(json_encode($fallbackPayload, JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$fallbackClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $fallbackMock);
	$fallbackResult = $fallbackClient->portalSearch([
		'sortBy' => 'price',
		'destinations' => ['1_1208'],
	]);

	assertSameValue($fallbackPayload, $fallbackResult, 'portalSearch() should return decoded JSON body even on HTTP 500.');

	$invalidJsonBody = '<html><body>' . str_repeat('x', 220) . '</body></html>';
	$invalidJsonMock = new MockHttpClient(static function (string $method, string $url, array $options = []) use ($invalidJsonBody): MockResponse {
		return new MockResponse($invalidJsonBody, ['http_code' => 200]);
	});

	$invalidJsonClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $invalidJsonMock);
	assertThrows(
		static fn() => $invalidJsonClient->portalSearch(['sortBy' => 'price']),
		ResponseFormatException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'invalid JSON'), 'Expected invalid JSON message for portalSearch().');
			assertTrue(str_contains($e->getMessage(), 'Body preview:'), 'Expected body preview in invalid JSON message for portalSearch().');
			assertTrue(!str_contains($e->getMessage(), str_repeat('x', 200)), 'Expected invalid JSON body preview to be truncated.');
		}
	);

	$scalarJsonMock = new MockHttpClient(static function (string $method, string $url, array $options = []): MockResponse {
		return new MockResponse('"ok"', ['http_code' => 200]);
	});

	$scalarJsonClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $scalarJsonMock);
	assertThrows(
		static fn() => $scalarJsonClient->portalSearch(['sortBy' => 'price']),
		ResponseFormatException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'unexpected format'), 'Expected unexpected format message for scalar portalSearch() JSON.');
		}
	);

	$timeoutCalls = 0;
	$timeoutMock = new MockHttpClient(static function (string $method, string $url, array $options = []) use (&$timeoutCalls): MockResponse {
		$timeoutCalls++;
		throw new TimeoutException('idle timeout');
	});

	$timeoutClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $timeoutMock);
	$timeoutResult = $timeoutClient->portalSearch([
		'sortBy' => 'price',
		'destinations' => ['1_1208'],
	]);
	assertSameValue(2, $timeoutCalls, 'portalSearch() should retry exactly once after timeout before falling back.');
	assertSameValue($fallbackPayload, $timeoutResult, 'portalSearch() should return safe fallback payload after repeated timeout.');

	$transportMock = new MockHttpClient(static function (string $method, string $url, array $options = []): MockResponse {
		throw new TransportException('loopback down');
	});

	$transportClient = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $transportMock);
	assertThrows(
		static fn() => $transportClient->portalSearch(['sortBy' => 'price']),
		HttpRequestException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'Portal search HTTP request failed'), 'Expected transport failure message for portalSearch().');
			assertSameValue(null, $e->statusCode(), 'Transport error should not have an HTTP status code.');
		}
	);

	echo "PASS: portalSearch preserves HTTP 500/JSON contracts, retries timeout once with fallback payload, and still throws on non-timeout transport errors.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
