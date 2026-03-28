<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Http\AuthTokenProvider;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$cacheDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
		. DIRECTORY_SEPARATOR
		. 'merlinx-getter-token-cache-'
		. str_replace('.', '-', uniqid('', true));
	if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
		throw new RuntimeException('Unable to create token-cache test directory.');
	}

	$tokenRequests = 0;
	$issuedTokens = ['token-1', 'token-2'];

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$tokenRequests, &$issuedTokens): MockResponse {
		if (!str_contains($url, '/v5/token/new')) {
			return new MockResponse('{"error":"unexpected"}', ['http_code' => 500]);
		}

		$tokenRequests++;
		$token = array_shift($issuedTokens);
		if (!is_string($token)) {
			$token = 'token-fallback';
		}

		return new MockResponse(json_encode(['token' => $token], JSON_THROW_ON_ERROR), ['http_code' => 200]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'cache' => [
			'dir' => $cacheDir,
			'token' => ['ttlSeconds' => 600],
			'search' => [
				'ttlSeconds' => 60,
				'staleSeconds' => 120,
			],
		],
	]));

	$firstProvider = new AuthTokenProvider($config, $mock);
	$firstToken = $firstProvider->getToken();
	assertSameValue('token-1', $firstToken, 'First token fetch mismatch.');
	assertSameValue(1, $tokenRequests, 'First token fetch should hit upstream once.');

	$secondProvider = new AuthTokenProvider($config, $mock);
	$secondToken = $secondProvider->getToken();
	assertSameValue('token-1', $secondToken, 'Second provider should read token from persistent cache.');
	assertSameValue(1, $tokenRequests, 'Second provider cache hit should not request new token.');

	$refreshed = $secondProvider->forceRefresh();
	assertSameValue('token-2', $refreshed, 'forceRefresh should fetch a new token.');
	assertSameValue(2, $tokenRequests, 'forceRefresh should trigger one additional upstream token request.');

	$thirdProvider = new AuthTokenProvider($config, $mock);
	$thirdToken = $thirdProvider->getToken();
	assertSameValue('token-2', $thirdToken, 'Third provider should see refreshed token from persistent cache.');
	assertSameValue(2, $tokenRequests, 'Persisted refreshed token should avoid extra upstream requests.');

	echo "PASS: AuthTokenProvider reuses persistent token cache across instances and refreshes atomically.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
