<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\MerlinxGetterClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require __DIR__ . '/bootstrap.php';

try {
	$capturedMethod = null;
	$capturedUrl = null;
	$capturedHeaders = null;
	$capturedParams = null;

	$portalPayload = [
		'offers' => [
			[
				'id' => 'portal-offer-1',
				'name' => 'Hotel Testowy',
			],
		],
		'query' => [
			'sortBy' => 'price',
			'sortDirection' => 'desc',
		],
		'error' => null,
		'limitHit' => false,
	];

	$mock = new MockHttpClient(function (string $method, string $url, array $options = []) use (&$capturedMethod, &$capturedUrl, &$capturedHeaders, &$capturedParams, $portalPayload): MockResponse {
		$capturedMethod = $method;
		$capturedUrl = $url;
		$capturedHeaders = is_array($options['normalized_headers'] ?? null) ? $options['normalized_headers'] : [];

		$body = $options['body'] ?? null;
		$body = is_string($body) ? $body : '';
		parse_str($body, $capturedParams);

		return new MockResponse(json_encode($portalPayload, JSON_THROW_ON_ERROR), ['http_code' => 200]);
	});

	$client = new MerlinxGetterClient(MerlinxGetterConfig::fromArray(baseMerlinxConfig()), $mock);

	$params = [
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
	];

	$result = $client->portalSearch($params);

	assertSameValue('POST', $capturedMethod, 'portalSearch() should use POST.');
	assertSameValue('https://www.skionline.pl/wxp/?p=ofertyResultsJson', $capturedUrl, 'portalSearch() endpoint URL mismatch.');
	assertTrue(is_array($capturedHeaders), 'portalSearch() should send HTTP headers.');
	assertSameValue(['Accept: application/json'], $capturedHeaders['accept'] ?? null, 'portalSearch() Accept header mismatch.');
	assertSameValue(['Content-Type: application/x-www-form-urlencoded'], $capturedHeaders['content-type'] ?? null, 'portalSearch() Content-Type header mismatch.');
	assertSameValue($params, $capturedParams, 'portalSearch() should round-trip parsed params through form encoding.');
	assertSameValue($portalPayload, $result, 'portalSearch() should return decoded endpoint payload unchanged.');

	echo "PASS: portalSearch posts parsed params to the public SkiOnline offers results JSON endpoint and returns decoded payload.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
