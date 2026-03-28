<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Http\AuthTokenProvider;
use Skionline\MerlinxGetter\Http\MerlinxHttpClient;
use Skionline\MerlinxGetter\Operation\SearchOperation;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/helpers/bootstrap.php';

final class InMemoryLogger implements \Skionline\MerlinxGetter\Log\LoggerInterface
{
	/**
	 * @var array<int, string>
	 */
	private array $warnings = [];

	public function warning(string $message, array $context = []): void
	{
		$this->warnings[] = $message;
	}

	/**
	 * @return array<int, string>
	 */
	public function warnings(): array
	{
		return $this->warnings;
	}

	public function emergency(string $message, array $context = []): void
	{
	}

	public function alert(string $message, array $context = []): void
	{
	}

	public function critical(string $message, array $context = []): void
	{
	}

	public function error(string $message, array $context = []): void
	{
	}

	public function notice(string $message, array $context = []): void
	{
	}

	public function info(string $message, array $context = []): void
	{
	}

	public function debug(string $message, array $context = []): void
	{
	}
}

/**
 * @param callable(int, array<string, mixed>|null): array<string, mixed> $buildSearchResponse
 */
function runPaginationScenario(
	string $name,
	array $views,
	int $expectedSearchRequestCount,
	callable $buildSearchResponse
): void {
	$searchRequestCount = 0;
	$requests = [];

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$searchRequestCount, &$requests, $buildSearchResponse): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/search')) {
			$payload = extractJsonPayload($options);
			$requests[] = ['url' => $url, 'payload' => $payload];
			$searchRequestCount++;

			$response = $buildSearchResponse($searchRequestCount, $payload);
			return new MockResponse(json_encode($response, JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig());
	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient);
	$operation->execute(searchRequest(['text' => $name], [], [], $views))->response();

	assertSameValue(
		$expectedSearchRequestCount,
		$searchRequestCount,
		$name . ': unexpected number of /search requests.'
	);
}

try {
	runPaginationScenario(
		'no bookmark in response',
		['offerList' => ['limit' => 600]],
		1,
		static function (): array {
			return [
				'offerList' => [
					'more' => true,
					'items' => [
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'offer-1|SNOW|NHx8',
								],
							],
						],
					],
				],
			];
		}
	);

	runPaginationScenario(
		'empty bookmark in response',
		['offerList' => ['limit' => 600]],
		1,
		static function (): array {
			return [
				'offerList' => [
					'more' => true,
					'pageBookmark' => '',
					'items' => [
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'offer-empty-bookmark|SNOW|NHx8',
								],
							],
						],
					],
				],
			];
		}
	);

	runPaginationScenario(
		'repeated bookmark',
		['offerList' => ['limit' => 600]],
		2,
		static function (int $requestNumber): array {
			return [
				'offerList' => [
					'more' => true,
					'pageBookmark' => 'same-bm',
					'items' => [
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'offer-' . $requestNumber . '|SNOW|NHx8',
								],
							],
						],
					],
				],
			];
		}
	);

	runPaginationScenario(
		'more false',
		['offerList' => ['limit' => 600]],
		1,
		static function (): array {
			return [
				'offerList' => [
					'more' => false,
					'pageBookmark' => 'bm-final',
					'items' => [
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'offer-1|SNOW|NHx8',
								],
							],
						],
					],
				],
			];
		}
	);

	runPaginationScenario(
		'explicit view limit reached',
		['offerList' => ['limit' => 60]],
		2,
		static function (int $requestNumber): array {
			$items = [];
			$count = $requestNumber === 1 ? 50 : 10;
			$start = $requestNumber === 1 ? 1 : 51;
			for ($i = 0; $i < $count; $i++) {
				$items[] = [
					'offer' => [
						'Base' => [
							'OfferId' => 'offer-' . ($start + $i) . '|SNOW|NHx8',
						],
					],
				];
			}

			return [
				'offerList' => [
					'more' => true,
					'pageBookmark' => 'bm-' . $requestNumber,
					'items' => $items,
				],
			];
		}
	);

	runPaginationScenario(
		'hard max pages guard for no explicit limit',
		['offerList' => []],
		8,
		static function (int $requestNumber): array {
			return [
				'offerList' => [
					'more' => true,
					'pageBookmark' => 'bm-' . $requestNumber,
					'items' => [
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'offer-' . $requestNumber . '|SNOW|NHx8',
								],
							],
						],
					],
				],
			];
		}
	);

	$searchRequestCount = 0;
	$logger = new InMemoryLogger();
	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$searchRequestCount): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/search')) {
			$searchRequestCount++;
			return new MockResponse(json_encode([
				'offerList' => [
					'more' => true,
					'pageBookmark' => 'bm-warn-' . $searchRequestCount,
					'items' => [
						[
							'offer' => [
								'Base' => [
									'OfferId' => 'offer-warn-' . $searchRequestCount . '|SNOW|NHx8',
								],
							],
						],
					],
				],
			], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig());
	$tokenProvider = new AuthTokenProvider($config, $mock);
	$httpClient = new MerlinxHttpClient($config, $tokenProvider, $mock);
	$operation = new SearchOperation($config, $httpClient, null, null, null, null, $logger);
	$result = $operation->execute(searchRequest(['text' => 'max-pages-warning'], [], [], ['offerList' => []]))->response();

	assertSameValue(8, $searchRequestCount, 'max pages warning scenario: expected cap to stop pagination at 8 requests.');
	assertSameValue(8, count($result['offerList']['items'] ?? []), 'max pages warning scenario: merged items should contain all fetched pages up to the cap.');
	assertSameValue(1, count($logger->warnings()), 'max pages warning scenario: expected one warning when pagination cap is hit.');
	assertTrue(
		str_contains($logger->warnings()[0], 'max pages') || str_contains($logger->warnings()[0], 'cap'),
		'max pages warning scenario: warning should mention max pages cap.'
	);

	runPaginationScenario(
		'empty items response',
		['offerList' => ['limit' => 600]],
		1,
		static function (): array {
			return [
				'offerList' => [
					'more' => true,
					'pageBookmark' => 'bm-1',
					'items' => [],
				],
			];
		}
	);

	$fieldValuesRequestCount = 0;
	$fieldValuesRequests = [];
	$fieldValuesMock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$fieldValuesRequestCount, &$fieldValuesRequests): MockResponse {
		if (str_contains($url, '/v5/token/new')) {
			return new MockResponse(json_encode(['token' => 'dummy-token'], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		if (str_contains($url, '/v5/data/travel/search')) {
			$fieldValuesRequestCount++;
			$fieldValuesRequests[] = extractJsonPayload($options);

			if ($fieldValuesRequestCount === 1) {
				return new MockResponse(json_encode([
					'fieldValues' => [
						'more' => true,
						'pageBookmark' => 'fv-bm-1',
						'fieldValues' => [
							'Base.Operator' => ['SNOW'],
						],
					],
				], JSON_THROW_ON_ERROR), ['http_code' => 200]);
			}

			return new MockResponse(json_encode([
				'fieldValues' => [
					'more' => false,
					'pageBookmark' => '',
					'fieldValues' => [
						'Base.Operator' => ['ALT'],
					],
				],
			], JSON_THROW_ON_ERROR), ['http_code' => 200]);
		}

		return new MockResponse(json_encode(['error' => 'unexpected request'], JSON_THROW_ON_ERROR), ['http_code' => 500]);
	});

	$fieldValuesConfig = MerlinxGetterConfig::fromArray(baseMerlinxConfig());
	$fieldValuesTokenProvider = new AuthTokenProvider($fieldValuesConfig, $fieldValuesMock);
	$fieldValuesHttpClient = new MerlinxHttpClient($fieldValuesConfig, $fieldValuesTokenProvider, $fieldValuesMock);
	$fieldValuesOperation = new SearchOperation($fieldValuesConfig, $fieldValuesHttpClient);
	$fieldValuesResult = $fieldValuesOperation->execute(searchRequest([], [], [], ['fieldValues' => ['fieldList' => ['Base.Operator']]]))->response();

	assertSameValue(2, $fieldValuesRequestCount, 'fieldValues bookmark follow-through: expected two paginated search requests.');
	assertSameValue('fv-bm-1', $fieldValuesRequests[1]['views']['fieldValues']['previousPageBookmark'] ?? null, 'fieldValues bookmark follow-through: second request should include previousPageBookmark.');
	assertSameValue(['SNOW', 'ALT'], $fieldValuesResult['fieldValues']['Base.Operator'] ?? null, 'fieldValues bookmark follow-through: merged operators should include both pages.');

	echo "PASS: SearchOperation pagination loop handles all required exit conditions.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
