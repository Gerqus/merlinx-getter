<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Http\AuthTokenProvider;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/bootstrap.php';

try {
	$tokenRequestCount = 0;
	$responses = [
		new MockResponse("Too many requests\n", ['http_code' => 200, 'response_headers' => ['Retry-After: 0']]),
		new MockResponse(json_encode(['token' => 'token-retry-success'], JSON_THROW_ON_ERROR), ['http_code' => 200]),
	];

	$mock = new MockHttpClient(static function (string $method, string $url, array $options = []) use (&$tokenRequestCount, &$responses): MockResponse {
		if (!str_contains($url, '/v5/token/new')) {
			return new MockResponse('{"error":"unexpected request"}', ['http_code' => 500]);
		}

		$tokenRequestCount++;
		$response = array_shift($responses);
		if ($response instanceof MockResponse) {
			return $response;
		}

		return new MockResponse('{"error":"unexpected extra token request"}', ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'runtime' => [
				'rate_limit_retry_max_attempts' => 3,
				'rate_limit_retry_delay_ms' => 1,
				'rate_limit_retry_backoff_multiplier' => 2.0,
				'rate_limit_retry_max_delay_ms' => 16,
			],
		],
	]));

	$provider = new AuthTokenProvider($config, $mock);
	$token = $provider->getToken();

	assertSameValue(2, $tokenRequestCount, 'Expected token endpoint to retry once before succeeding.');
	assertSameValue('token-retry-success', $token, 'Expected token returned from second successful attempt.');

	echo "PASS: AuthTokenProvider retries rate-limited token acquisition.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
