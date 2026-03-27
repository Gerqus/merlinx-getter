<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Search\Execution\SearchExecutionRequestBuilder;
use Skionline\MerlinxGetter\Search\Policy\VariantOperatorSearchGroups;

require __DIR__ . '/bootstrap.php';

try {
	$config = MerlinxGetterConfig::fromArray(baseMerlinxConfig([
		'search_engine' => [
			'operators' => ['VITX'],
			'operator_policies' => [
				'child_as_adult_operators' => ['SNOW'],
			],
			'conditions' => [
				[
					'search' => [
						'Base' => ['Duration' => 7],
						'Accommodation' => ['Name' => 'ConfigHotel'],
					],
					'filter' => [
						'Base' => ['PriceRange' => 'CONFIG'],
					],
					'results' => [
						'mode' => 'condition',
					],
					'views' => [
						'offerList' => [
							'limit' => 25,
						],
					],
				],
			],
		],
	]));

	$request = searchRequest(
		[
			'Base' => [
				'Operator' => ['VITX'],
				'Duration' => 3,
			],
			'Accommodation' => [
				'Name' => 'RequestHotel',
				'XCode' => [216116],
			],
		],
		[
			'Base' => [
				'PriceRange' => 'REQUEST',
				'StartDate' => ['2026-03-01'],
			],
		],
		[
			'mode' => 'request',
			'requestOnly' => true,
		],
		[
			'offerList' => [
				'limit' => 10,
				'requestOnly' => 'kept',
			],
		]
	);

	$queries = SearchExecutionRequestBuilder::build($config, $request);
	assertSameValue(1, count($queries), 'Expected one query for a single condition and single operator group.');

	$builtSearch = $queries[0]->search();
	assertSameValue('ConfigHotel', $builtSearch['Accommodation']['Name'] ?? null, 'Condition search should override request value on shared key.');
	assertSameValue([216116], $builtSearch['Accommodation']['XCode'] ?? null, 'Request-only search key should be preserved.');
	assertSameValue(7, $builtSearch['Base']['Duration'] ?? null, 'Condition should override request duration for the same key.');

	$builtFilter = $queries[0]->filter();
	assertSameValue('CONFIG', $builtFilter['Base']['PriceRange'] ?? null, 'Condition filter should override request filter on shared key.');
	assertSameValue(['2026-03-01'], $builtFilter['Base']['StartDate'] ?? null, 'Request-only filter key should be preserved.');

	$builtResults = $queries[0]->results();
	assertSameValue('condition', $builtResults['mode'] ?? null, 'Condition results should override request results on shared key.');
	assertSameValue(true, $builtResults['requestOnly'] ?? null, 'Request-only results keys should be preserved.');

	$builtViews = $queries[0]->views();
	assertSameValue(25, $builtViews['offerList']['limit'] ?? null, 'Condition views should override request views on shared key.');
	assertSameValue('kept', $builtViews['offerList']['requestOnly'] ?? null, 'Request-only views keys should be preserved.');

	$groupsPolicy = new VariantOperatorSearchGroups(['SNOW']);
	$noOperatorsGroups = $groupsPolicy->build([], [['code' => 'ADULT']]);
	assertSameValue(1, count($noOperatorsGroups), 'Empty operators should still produce one group.');
	assertTrue(array_key_exists('operators', $noOperatorsGroups[0]), 'No-operators branch should return operators key.');
	assertSameValue(null, $noOperatorsGroups[0]['operators'], 'No-operators branch should keep operators as null.');
	assertSameValue([['code' => 'ADULT']], $noOperatorsGroups[0]['participants'] ?? null, 'No-operators branch should preserve participants.');

	$noParticipantsGroups = $groupsPolicy->build(['VITX'], []);
	assertSameValue(1, count($noParticipantsGroups), 'Empty participants should still produce one group.');
	assertSameValue(['VITX'], $noParticipantsGroups[0]['operators'] ?? null, 'No-participants branch should preserve operators.');
	assertTrue(array_key_exists('participants', $noParticipantsGroups[0]), 'No-participants branch should return participants key.');
	assertSameValue(null, $noParticipantsGroups[0]['participants'], 'No-participants branch should keep participants as null.');

	$nullLikeRequest = searchRequest([
		'Base' => [
			'Operator' => null,
		],
	]);
	$nullLikeQueries = SearchExecutionRequestBuilder::build($config, $nullLikeRequest);
	assertSameValue(1, count($nullLikeQueries), 'Null operator input should still produce one query.');
	$nullLikeBase = $nullLikeQueries[0]->search()['Base'] ?? [];
	assertSameValue(['VITX'], $nullLikeBase['Operator'] ?? null, 'Builder should normalize null operator input to configured operators.');
	assertTrue(!array_key_exists('ParticipantsList', $nullLikeBase), 'Builder should not emit ParticipantsList key when participant group value is null/empty.');

	$noOperatorConfigArray = baseMerlinxConfig();
	$noOperatorConfigArray['search_engine']['operators'] = [];
	$noOperatorConfig = MerlinxGetterConfig::fromArray($noOperatorConfigArray);
	$noOperatorRequest = searchRequest([
		'Base' => [
			'Operator' => [],
		],
	]);
	$noOperatorQueries = SearchExecutionRequestBuilder::build($noOperatorConfig, $noOperatorRequest);
	assertSameValue(1, count($noOperatorQueries), 'Empty configured operators should still produce one query.');
	$noOperatorBase = $noOperatorQueries[0]->search()['Base'] ?? [];
	assertTrue(!array_key_exists('Operator', $noOperatorBase), 'Builder should omit Base.Operator when normalized operator list is empty.');

	echo "PASS: SearchExecutionRequestBuilder merge order and VariantOperatorSearchGroups null behavior are covered.\n";
	exit(0);
} catch (Throwable $e) {
	echo 'FAIL: ' . $e->getMessage() . "\n";
	exit(1);
}
