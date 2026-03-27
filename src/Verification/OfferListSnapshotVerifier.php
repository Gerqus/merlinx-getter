<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Verification;

final class OfferListSnapshotVerifier
{
	/**
	 * @param array<int, mixed> $snapshotItems
	 * @param array<int, mixed> $expectedItems
	 * @param array<int, string> $allowedOperators
	 * @return array<string, mixed>
	 */
	public function verify(
		array $snapshotItems,
		array $expectedItems,
		array $allowedOperators,
		string $minStartDate,
		string $maxStartDate,
	): array {
		$allowedOperatorMap = $this->normalizeOperatorMap($allowedOperators);

		$snapshotSummary = $this->summarizeItems($snapshotItems);
		$expectedSummary = $this->summarizeItems($expectedItems);

		$missingOfferIds = array_values(array_diff($expectedSummary['uniqueOfferIdsList'], $snapshotSummary['uniqueOfferIdsList']));
		$unexpectedOfferIds = array_values(array_diff($snapshotSummary['uniqueOfferIdsList'], $expectedSummary['uniqueOfferIdsList']));
		sort($missingOfferIds);
		sort($unexpectedOfferIds);

		$invalidOperatorOfferIds = [];
		$outOfRangeOfferIds = [];
		foreach ($snapshotItems as $item) {
			$offerId = $this->extractComparableOfferId($item);
			if ($offerId === null) {
				continue;
			}

			$operator = $this->extractBaseValue($item, 'Operator');
			if ($operator === null || !isset($allowedOperatorMap[$operator])) {
				$invalidOperatorOfferIds[$offerId] = true;
			}

			$startDate = $this->extractBaseValue($item, 'StartDate');
			if ($startDate === null || $startDate < $minStartDate || $startDate > $maxStartDate) {
				$outOfRangeOfferIds[$offerId] = true;
			}
		}

		$invalidOperatorList = array_keys($invalidOperatorOfferIds);
		$outOfRangeList = array_keys($outOfRangeOfferIds);
		sort($invalidOperatorList);
		sort($outOfRangeList);

		$isValid = $missingOfferIds === []
			&& $unexpectedOfferIds === []
			&& $invalidOperatorList === []
			&& $outOfRangeList === [];

		return [
			'isValid' => $isValid,
			'snapshot' => [
				'totalItems' => $snapshotSummary['totalItems'],
				'uniqueOfferIds' => count($snapshotSummary['uniqueOfferIdsList']),
				'byOperator' => $snapshotSummary['byOperator'],
				'byOperatorDesc' => $snapshotSummary['byOperatorDesc'],
			],
			'expected' => [
				'totalItems' => $expectedSummary['totalItems'],
				'uniqueOfferIds' => count($expectedSummary['uniqueOfferIdsList']),
				'byOperator' => $expectedSummary['byOperator'],
				'byOperatorDesc' => $expectedSummary['byOperatorDesc'],
			],
			'missingOfferIds' => $missingOfferIds,
			'unexpectedOfferIds' => $unexpectedOfferIds,
			'invalidOperatorOfferIds' => $invalidOperatorList,
			'outOfRangeOfferIds' => $outOfRangeList,
			'duplicateOfferIds' => $snapshotSummary['duplicateOfferIds'],
		];
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array<int, mixed>
	 */
	public function extractOfferListItems(array $payload): array
	{
		$items = $payload['offerList']['items'] ?? null;
		if (!is_array($items)) {
			return [];
		}

		return array_values($items);
	}

	/**
	 * @param array<int, mixed> $items
	 * @return array{
	 *   totalItems: int,
	 *   uniqueOfferIdsList: array<int, string>,
	 *   duplicateOfferIds: array<int, string>,
	 *   byOperator: array<string, int>,
	 *   byOperatorDesc: array<string, int>
	 * }
	 */
	private function summarizeItems(array $items): array
	{
		$totalItems = count($items);
		$seen = [];
		$duplicate = [];
		$byOperator = [];
		$byOperatorDesc = [];

		foreach ($items as $item) {
			$offerId = $this->extractComparableOfferId($item);
			if ($offerId !== null) {
				if (isset($seen[$offerId])) {
					$duplicate[$offerId] = true;
				}
				$seen[$offerId] = true;
			}

			$operator = $this->extractBaseValue($item, 'Operator') ?? 'UNKNOWN';
			$operatorDesc = $this->extractBaseValue($item, 'OperatorDesc') ?? 'UNKNOWN';
			$byOperator[$operator] = ($byOperator[$operator] ?? 0) + 1;
			$byOperatorDesc[$operatorDesc] = ($byOperatorDesc[$operatorDesc] ?? 0) + 1;
		}

		$uniqueOfferIdsList = array_keys($seen);
		sort($uniqueOfferIdsList);

		$duplicateOfferIds = array_keys($duplicate);
		sort($duplicateOfferIds);

		ksort($byOperator);
		ksort($byOperatorDesc);

		return [
			'totalItems' => $totalItems,
			'uniqueOfferIdsList' => $uniqueOfferIdsList,
			'duplicateOfferIds' => $duplicateOfferIds,
			'byOperator' => $byOperator,
			'byOperatorDesc' => $byOperatorDesc,
		];
	}

	/**
	 * @param array<int, string> $operators
	 * @return array<string, true>
	 */
	private function normalizeOperatorMap(array $operators): array
	{
		$map = [];
		foreach ($operators as $operator) {
			$trimmed = trim($operator);
			if ($trimmed === '') {
				continue;
			}
			$map[$trimmed] = true;
		}
		return $map;
	}

	private function extractBaseValue(mixed $item, string $field): ?string
	{
		if (!is_array($item)) {
			return null;
		}

		$value = $item['offer']['Base'][$field] ?? null;
		if (!is_scalar($value)) {
			return null;
		}

		$stringValue = trim((string) $value);
		return $stringValue === '' ? null : $stringValue;
	}

	private function extractComparableOfferId(mixed $item): ?string
	{
		$uniqueObjectId = $this->extractBaseValue($item, 'UniqueObjectId');
		if ($uniqueObjectId !== null) {
			return $uniqueObjectId;
		}

		return $this->extractBaseValue($item, 'OfferId');
	}
}
