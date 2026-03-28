<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Search\Util\TravelSearchResponseMerger;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$merger = new TravelSearchResponseMerger();

	$merged = $merger->merge(
		[
			'regionList' => [
				'59:' => [
					'desc' => 'Polska',
					'regions' => [
						'59_3781' => [
							'desc' => 'Sudety',
							'offer' => [
								'count' => 5,
							],
						],
					],
				],
			],
		],
		[
			'regionList' => [
				'59:' => [
					'regions' => [
						'59_3781' => [
							'offer' => [
								'minPrice' => '100.00',
							],
						],
						'59_950' => [
							'desc' => 'Tatry',
						],
					],
				],
				'1:' => [
					'desc' => 'Austria',
				],
			],
		],
	);

	$regionList = $merged['regionList'] ?? null;
	assertTrue(is_array($regionList), 'Merged regionList should be an array.');
	assertSameValue('Polska', $regionList['59:']['desc'] ?? null, 'Region merge should preserve first country description.');
	assertSameValue(5, $regionList['59:']['regions']['59_3781']['offer']['count'] ?? null, 'Region merge should preserve existing offer payload.');
	assertSameValue('100.00', $regionList['59:']['regions']['59_3781']['offer']['minPrice'] ?? null, 'Region merge should deep-fill missing nested offer fields.');
	assertSameValue('Tatry', $regionList['59:']['regions']['59_950']['desc'] ?? null, 'Region merge should add new regions by key.');
	assertSameValue('Austria', $regionList['1:']['desc'] ?? null, 'Region merge should add new countries by key.');

	echo "PASS: TravelSearchResponseMerger preserves regionList as a keyed tree and deep-fills nested content.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
