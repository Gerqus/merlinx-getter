<?php

declare(strict_types=1);

require __DIR__ . '/tests/bootstrap.php';

use Skionline\MerlinxGetter\MerlinxGetterClient;
use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

const LIVE_PORTAL_RESULTS_ENDPOINT = 'https://www.skionline.pl/wxp/?p=ofertyResultsJson';
const RESPONSE_PREVIEW_LIMIT = 180;

final class IntegrationSkip extends RuntimeException
{
}

function responseBodyPreview(string $body): string
{
	$normalized = preg_replace('/\s+/u', ' ', trim($body));
	$normalized = is_string($normalized) ? trim($normalized) : trim($body);
	if ($normalized === '') {
		return '[empty body]';
	}

	if (strlen($normalized) <= RESPONSE_PREVIEW_LIMIT) {
		return $normalized;
	}

	return substr($normalized, 0, RESPONSE_PREVIEW_LIMIT) . '...';
}

/**
 * @param array<string, mixed> $params
 * @return array{status:int, payload:array<string, mixed>, headers:array<string, array<int, string>>}
 */
function callLivePortalResultsEndpoint(array $params): array
{
	$client = HttpClient::create();

	try {
		$response = $client->request('POST', LIVE_PORTAL_RESULTS_ENDPOINT, [
			'body' => http_build_query($params, '', '&', PHP_QUERY_RFC3986),
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
			'timeout' => 15.0,
		]);

		$status = $response->getStatusCode();
		$headers = $response->getHeaders(false);
		$body = $response->getContent(false);
	} catch (TransportExceptionInterface $e) {
		throw new IntegrationSkip(
			'Live portal endpoint is not reachable at ' . LIVE_PORTAL_RESULTS_ENDPOINT . '.',
			0,
			$e
		);
	}

	$contentType = $headers['content-type'][0]
		?? $headers['Content-Type'][0]
		?? '';
	if (!str_contains(strtolower((string) $contentType), 'application/json')) {
		throw new RuntimeException(
			'Live portal endpoint did not return JSON. '
			. 'Status=' . $status
			. ' Content-Type=' . ($contentType !== '' ? $contentType : 'n/a')
			. ' BodyPreview=' . responseBodyPreview($body)
		);
	}

	try {
		$payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		throw new RuntimeException(
			'Live portal endpoint returned invalid JSON. BodyPreview=' . responseBodyPreview($body),
			0,
			$e
		);
	}

	if (!is_array($payload)) {
		throw new RuntimeException('Live portal endpoint returned non-object JSON root.');
	}

	return [
		'status' => $status,
		'payload' => $payload,
		'headers' => $headers,
	];
}

/**
 * @param array<string, mixed> $payload
 */
function assertPortalEnvelope(array $payload, string $context): void
{
	assertTrue(array_key_exists('offers', $payload), $context . ': missing offers key.');
	assertTrue(array_key_exists('query', $payload), $context . ': missing query key.');
	assertTrue(array_key_exists('error', $payload), $context . ': missing error key.');
	assertTrue(array_key_exists('limitHit', $payload), $context . ': missing limitHit key.');

	assertTrue(is_array($payload['offers']), $context . ': offers must be an array.');
	assertTrue(is_array($payload['query']), $context . ': query must be an array.');
	assertTrue(is_bool($payload['limitHit']), $context . ': limitHit must be a boolean.');
	assertTrue(
		$payload['error'] === null || is_string($payload['error']),
		$context . ': error must be null or string.'
	);
}

/**
 * @param array{status:int, payload:array<string, mixed>, headers:array<string, array<int, string>>} $response
 */
function assertPortalHttpContract(array $response, string $context): void
{
	assertTrue(
		in_array($response['status'], [200, 500], true),
		$context . ': expected live endpoint status 200 or 500, got ' . $response['status'] . '.'
	);

	$contentType = $response['headers']['content-type'][0]
		?? $response['headers']['Content-Type'][0]
		?? '';
	assertTrue(
		str_contains(strtolower((string) $contentType), 'application/json'),
		$context . ': expected application/json content type.'
	);

	assertPortalEnvelope($response['payload'], $context);
}

/**
 * @param array<string, mixed> $params
 */
function assertPortalSearchResults(MerlinxGetterClient $client, array $params, string $context): array
{
	$clientPayload = $client->portalSearch($params);
	assertPortalEnvelope($clientPayload, $context . ' portalSearch');
	return $clientPayload;
}

try {
	$preflight = callLivePortalResultsEndpoint([]);
	assertPortalHttpContract($preflight, 'preflight');

	$client = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()));

	$emptyPayload = assertPortalSearchResults($client, [], 'empty params');

	$searchedPayload = assertPortalSearchResults(
		$client,
		[
			'sortBy' => 'price',
			'sortDirection' => 'desc',
			'searchText' => 'madonna',
			'adults' => '2',
			'priceMax' => '3500',
			'destinations' => ['1_1208', '1_1209'],
			'transport' => ['own', 'bus'],
			'departureLocations' => ['Krakow', 'Warszawa'],
			'board' => ['1', '2'],
			'attributes' => ['pool', 'spa'],
			'accommodationType' => ['H', 'AP'],
			'childrenBirthDates' => ['2018-01-02', '2020-03-04'],
		],
		'rich params'
	);

	assert(count($searchedPayload['offers']) !== count($emptyPayload['offers']), 'Expected search results to differ between empty and rich params.');
	assert(count($emptyPayload['offers']) > 0, 'Expected search results to differ between empty and rich params.');
	
	print_r(array_map(fn($offer) => $offer['name'] ?? '[no name]', $emptyPayload['offers']));

	echo "PASS: integration-test.php verifies portalSearch() against the live public SkiOnline offers JSON endpoint.\n";
	exit(0);
} catch (IntegrationSkip $e) {
	echo "SKIP: " . $e->getMessage() . "\n";
	echo $e->getTraceAsString();
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	echo $e->getTraceAsString();
	exit(1);
}
