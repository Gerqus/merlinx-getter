<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\MerlinxGetterClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/bootstrap.php';

try {
	$detailsRequestCount = 0;
	$responses = [
		new MockResponse("Too many requests\n", ['http_code' => 200, 'response_headers' => ['Retry-After: 0']]),
		new MockResponse("Too many requests\n", ['http_code' => 200]),
		new MockResponse(json_encode([
			'result' => [
				'offer' => [
					'Base' => [
						'OfferId' => 'details-retry-success|SNOW|NHx8',
					],
				],
			],
		], JSON_THROW_ON_ERROR), ['http_code' => 200]),
	];

	$mock = new MockHttpClient(static function (string $method, string $url, array $options = []) use (&$detailsRequestCount, &$responses): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/details')) {
			$detailsRequestCount++;
			$response = array_shift($responses);
			if ($response instanceof MockResponse) {
				return $response;
			}

			return new MockResponse('{"error":"unexpected extra details request"}', ['http_code' => 500]);
		}

		return new MockResponse('{"error":"unexpected request"}', ['http_code' => 500]);
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

	$client = new MerlinxGetterClient($config, $mock);
	$result = $client->getDetails('details-retry-success|SNOW|NHx8');

	assertSameValue(3, $detailsRequestCount, 'Expected details endpoint to retry twice before succeeding.');
	assertSameValue(
		'details-retry-success|SNOW|NHx8',
		$result['result']['offer']['Base']['OfferId'] ?? null,
		'Expected successful details payload after retries.'
	);

	echo "PASS: getDetails retries rate-limited MerlinX responses via shared HTTP behavior.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
