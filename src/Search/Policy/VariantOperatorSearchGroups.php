<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Policy;

final class VariantOperatorSearchGroups
{
	/** @var array<int, string> */
	private array $childAsAdultOperators;

	/**
	 * @param array<int, string> $childAsAdultOperators
	 */
	public function __construct(array $childAsAdultOperators)
	{
		$this->childAsAdultOperators = self::normalizeOperatorList($childAsAdultOperators);
	}

	/**
	 * @param array<int, string> $effectiveOperators
	 * @param array<int, array<string, mixed>> $participants
	 * @return array<int, array{operators: array<int, string>, participants: array<int, array<string, mixed>>}>
	 */
	public function build(array $effectiveOperators, array $participants): array
	{
		$operators = self::normalizeOperatorList($effectiveOperators);
		$participants = self::normalizeParticipants($participants);

		if ($operators === []) {
			return [[
				'operators' => null,
				'participants' => $participants,
			]];
		}

		if ($participants === [] || !self::hasChildrenBirthdates($participants)) {
			return [[
				'operators' => $operators,
				'participants' => null,
			]];
		}

		if ($this->childAsAdultOperators === []) {
			return [[
				'operators' => $operators,
				'participants' => $participants,
			]];
		}

		$specialMap = array_fill_keys($this->childAsAdultOperators, true);
		if (!self::containsSpecialOperator($operators, $specialMap)) {
			return [[
				'operators' => $operators,
				'participants' => $participants,
			]];
		}

		$groups = [];
		$currentOperators = [];
		$currentTreatChildrenAsAdults = null;

		foreach ($operators as $operator) {
			$treatChildrenAsAdults = isset($specialMap[$operator]);
			if ($currentTreatChildrenAsAdults === null) {
				$currentTreatChildrenAsAdults = $treatChildrenAsAdults;
				$currentOperators = [$operator];
				continue;
			}

			if ($currentTreatChildrenAsAdults !== $treatChildrenAsAdults) {
				$groups[] = self::buildGroup(
					$currentOperators,
					$participants,
					(bool) $currentTreatChildrenAsAdults,
				);
				$currentOperators = [$operator];
				$currentTreatChildrenAsAdults = $treatChildrenAsAdults;
				continue;
			}

			$currentOperators[] = $operator;
		}

		if ($currentOperators !== []) {
			$groups[] = self::buildGroup(
				$currentOperators,
				$participants,
				(bool) $currentTreatChildrenAsAdults,
			);
		}

		return $groups;
	}

	/**
	 * @param array<int, string> $operators
	 * @param array<int, array<string, mixed>> $participants
	 * @return array{operators: array<int, string>, participants: array<int, array<string, mixed>>}
	 */
	private static function buildGroup(array $operators, array $participants, bool $treatChildrenAsAdults): array
	{
		return [
			'operators' => $operators,
			'participants' => $treatChildrenAsAdults
				? self::buildAllAdultParticipants(count($participants))
				: $participants,
		];
	}

	/**
	 * @param array<int, string> $operators
	 * @return array<int, string>
	 */
	private static function normalizeOperatorList(array $operators): array
	{
		$seen = [];
		foreach ($operators as $operator) {
			if (!is_string($operator) && !is_int($operator)) {
				continue;
			}

			$normalized = strtoupper(trim((string) $operator));
			if ($normalized === '') {
				continue;
			}

			$seen[$normalized] = true;
		}

		return array_values(array_keys($seen));
	}

	/**
	 * @param array<int, array<string, mixed>> $participants
	 * @return array<int, array<string, mixed>>
	 */
	private static function normalizeParticipants(array $participants): array
	{
		$list = array_is_list($participants) ? $participants : array_values($participants);
		$out = [];
		foreach ($list as $participant) {
			if (!is_array($participant)) {
				continue;
			}
			$out[] = $participant;
		}

		return $out;
	}

	/**
	 * @param array<int, array<string, mixed>> $participants
	 */
	private static function hasChildrenBirthdates(array $participants): bool
	{
		foreach ($participants as $participant) {
			$birthdate = $participant['birthdate'] ?? null;
			if (!is_string($birthdate)) {
				continue;
			}
			if (trim($birthdate) !== '') {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int, string> $operators
	 * @param array<string, bool> $specialMap
	 */
	private static function containsSpecialOperator(array $operators, array $specialMap): bool
	{
		foreach ($operators as $operator) {
			if (isset($specialMap[$operator])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array<int, array{code: string}>
	 */
	private static function buildAllAdultParticipants(int $participantsCount): array
	{
		$participantsCount = max(0, $participantsCount);
		if ($participantsCount === 0) {
			return [];
		}

		return array_fill(0, $participantsCount, ['code' => 'ADULT']);
	}
}
