<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Verification\OfferListSnapshotVerifier;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$verifier = new OfferListSnapshotVerifier();

	$expectedItems = [
		[
			'offer' => [
				'Base' => [
					'UniqueObjectId' => 'u-offer-1',
					'OfferId' => 'offer-1',
					'Operator' => 'VITX',
					'OperatorDesc' => 'Itaka',
					'StartDate' => '2026-06-10',
				],
			],
		],
		[
			'offer' => [
				'Base' => [
					'UniqueObjectId' => 'u-offer-2',
					'OfferId' => 'offer-2',
					'Operator' => 'VITN',
					'OperatorDesc' => 'Itaka No Limits',
					'StartDate' => '2026-07-21',
				],
			],
		],
		[
			'offer' => [
				'Base' => [
					'UniqueObjectId' => 'u-offer-3',
					'OfferId' => 'offer-3',
					'Operator' => 'VITX',
					'OperatorDesc' => 'Itaka',
					'StartDate' => '2026-07-05',
				],
			],
		],
	];

	$snapshotItems = [
		$expectedItems[0],
		$expectedItems[1],
		$expectedItems[1],
		[
			'offer' => [
				'Base' => [
					'UniqueObjectId' => 'u-offer-4',
					'OfferId' => 'offer-4',
					'Operator' => 'OTHER',
					'OperatorDesc' => 'Other Operator',
					'StartDate' => '2026-06-15',
				],
			],
		],
		[
			'offer' => [
				'Base' => [
					'UniqueObjectId' => 'u-offer-5',
					'OfferId' => 'offer-5',
					'Operator' => 'VITX',
					'OperatorDesc' => 'Itaka',
					'StartDate' => '2026-08-01',
				],
			],
		],
	];

	$result = $verifier->verify(
		$snapshotItems,
		$expectedItems,
		['VITX', 'VITN'],
		'2026-06-01',
		'2026-07-31',
	);

	assertSameValue(false, $result['isValid'] ?? null, 'Verification should fail when snapshot and expected sets differ.');
	assertSameValue(5, $result['snapshot']['totalItems'] ?? null, 'Snapshot total item count mismatch.');
	assertSameValue(4, $result['snapshot']['uniqueOfferIds'] ?? null, 'Snapshot unique offer count mismatch.');
	assertSameValue(3, $result['expected']['totalItems'] ?? null, 'Expected total item count mismatch.');
	assertSameValue(3, $result['expected']['uniqueOfferIds'] ?? null, 'Expected unique offer count mismatch.');

	assertSameValue(['u-offer-3'], $result['missingOfferIds'] ?? null, 'Missing offer IDs mismatch.');
	assertSameValue(['u-offer-4', 'u-offer-5'], $result['unexpectedOfferIds'] ?? null, 'Unexpected offer IDs mismatch.');
	assertSameValue(['u-offer-4'], $result['invalidOperatorOfferIds'] ?? null, 'Invalid operator IDs mismatch.');
	assertSameValue(['u-offer-5'], $result['outOfRangeOfferIds'] ?? null, 'Out-of-range offer IDs mismatch.');
	assertSameValue(['u-offer-2'], $result['duplicateOfferIds'] ?? null, 'Duplicate offer IDs mismatch.');

	assertSameValue(2, $result['snapshot']['byOperator']['VITX'] ?? null, 'Operator count for VITX mismatch.');
	assertSameValue(2, $result['snapshot']['byOperator']['VITN'] ?? null, 'Operator count for VITN mismatch.');
	assertSameValue(1, $result['snapshot']['byOperator']['OTHER'] ?? null, 'Operator count for OTHER mismatch.');

	$matchingResult = $verifier->verify(
		$expectedItems,
		$expectedItems,
		['VITX', 'VITN'],
		'2026-06-01',
		'2026-07-31',
	);

	assertSameValue(true, $matchingResult['isValid'] ?? null, 'Verification should pass when sets are identical.');
	assertSameValue([], $matchingResult['missingOfferIds'] ?? null, 'Missing IDs should be empty for matching sets.');
	assertSameValue([], $matchingResult['unexpectedOfferIds'] ?? null, 'Unexpected IDs should be empty for matching sets.');
	assertSameValue([], $matchingResult['invalidOperatorOfferIds'] ?? null, 'Invalid operator IDs should be empty for matching sets.');
	assertSameValue([], $matchingResult['outOfRangeOfferIds'] ?? null, 'Out-of-range IDs should be empty for matching sets.');
	assertSameValue([], $matchingResult['duplicateOfferIds'] ?? null, 'Duplicate IDs should be empty for matching sets.');

	$expectedWithTokenizedOfferIds = [
		[
			'offer' => [
				'Base' => [
					'UniqueObjectId' => 'u-tokenized-1',
					'OfferId' => 'token-a',
					'Operator' => 'VITX',
					'OperatorDesc' => 'Itaka',
					'StartDate' => '2026-06-15',
				],
			],
		],
	];
	$snapshotWithTokenizedOfferIds = [
		[
			'offer' => [
				'Base' => [
					'UniqueObjectId' => 'u-tokenized-1',
					'OfferId' => 'token-b',
					'Operator' => 'VITX',
					'OperatorDesc' => 'Itaka',
					'StartDate' => '2026-06-15',
				],
			],
		],
	];

	$tokenizedIdsResult = $verifier->verify(
		$snapshotWithTokenizedOfferIds,
		$expectedWithTokenizedOfferIds,
		['VITX', 'VITN'],
		'2026-06-01',
		'2026-07-31',
	);
	assertSameValue(true, $tokenizedIdsResult['isValid'] ?? null, 'UniqueObjectId should be used as stable identity when OfferId tokens differ.');
	assertSameValue(1, $tokenizedIdsResult['snapshot']['uniqueOfferIds'] ?? null, 'UniqueObjectId-based unique count mismatch.');

	echo "PASS: OfferListSnapshotVerifier validates constraints and offer ID set parity.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
