<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Execution;

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;
use Skionline\MerlinxGetter\Search\Policy\InquiryableAvailabilityPolicy;
use Skionline\MerlinxGetter\Search\Policy\VariantOperatorSearchGroups;
use Skionline\MerlinxGetter\Search\Util\DeepMerge;
use Skionline\MerlinxGetter\Search\Util\SearchRequestFingerprint;

final class SearchExecutionRequestBuilder
{
	/**
	 * @return array<int, SearchExecutionRequest>
	 */
	public static function build(MerlinxGetterConfig $config, SearchExecutionRequest $request): array
	{
		$policy = InquiryableAvailabilityPolicy::fromConfig($config);
		$operatorGroups = new VariantOperatorSearchGroups($config->childAsAdultOperators());
		$conditions = $config->searchEngineConditions;
		$queries = [];
		$seenFingerprints = [];

		foreach ($conditions as $condition) {
			if (!is_array($condition)) {
				continue;
			}

			$conditionSearch = is_array($condition['search'] ?? null) ? $condition['search'] : [];
			$conditionFilter = is_array($condition['filter'] ?? null) ? $condition['filter'] : [];
			$conditionResults = is_array($condition['results'] ?? null) ? $condition['results'] : [];
			$conditionViews = is_array($condition['views'] ?? null) ? $condition['views'] : [];

			if (
				self::hasNoIntersection($request->search(), $conditionSearch)
				|| self::hasNoIntersection($request->filter(), $conditionFilter)
			) {
				continue;
			}

			$search = DeepMerge::merge($conditionSearch, $request->search());
			$filter = DeepMerge::merge($conditionFilter, $request->filter());
			$results = DeepMerge::merge($conditionResults, $request->results());
			$views = DeepMerge::merge($conditionViews, $request->views());

			if (self::isNoOpBranch($request, $search, $filter)) {
				continue;
			}

			$search['Base'] = is_array($search['Base'] ?? null) ? $search['Base'] : [];

			if (
				!array_key_exists('Operator', $search['Base'])
				|| $search['Base']['Operator'] === null
				|| $search['Base']['Operator'] === []
			) {
				$search['Base']['Operator'] = $config->searchEngineOperators;
			}

			$normalizedOperators = self::normalizeOperatorList($search['Base']['Operator'] ?? null);
			if ($normalizedOperators === []) {
				unset($search['Base']['Operator']);
			} else {
				$search['Base']['Operator'] = $normalizedOperators;
			}
			$search['Base']['Availability'] = self::normalizeAvailability($search['Base']['Availability'] ?? null, $policy);

			$participants = self::normalizeParticipantsList($search['Base']['ParticipantsList'] ?? null);
			if ($participants !== []) {
				$search['Base']['ParticipantsList'] = $participants;
			}

			foreach ($operatorGroups->build($normalizedOperators, $participants) as $group) {
				$groupSearch = $search;
				if (!empty($group['operators'])) {
					$groupSearch['Base']['Operator'] = $group['operators'];
				}

				if (!empty($group['participants'])) {
					$groupSearch['Base']['ParticipantsList'] = $group['participants'];
				}

				$builtRequest = SearchExecutionRequest::fromArrays(
					$groupSearch,
					$filter,
					$results,
					$views,
					$request->options(),
				);

				$fingerprint = self::requestFingerprint($builtRequest);
				if (isset($seenFingerprints[$fingerprint])) {
					continue;
				}

				$seenFingerprints[$fingerprint] = true;
				$queries[] = $builtRequest;
			}
		}

		return $queries;
	}

	/**
	 * @param array<string, mixed> $requestValues
	 * @param array<string, mixed> $conditionValues
	 */
	private static function hasNoIntersection(array $requestValues, array $conditionValues): bool
	{
		foreach ($conditionValues as $key => $conditionValue) {
			if (!array_key_exists($key, $requestValues)) {
				continue;
			}

			if (self::valuesConflict($requestValues[$key], $conditionValue)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<string, mixed> $search
	 * @param array<string, mixed> $filter
	 */
	private static function isNoOpBranch(
		SearchExecutionRequest $request,
		array $search,
		array $filter,
	): bool {
		return ($request->search() !== [] || $request->filter() !== [])
			&& $search === $request->search()
			&& $filter === $request->filter();
	}

	private static function valuesConflict(mixed $requestValue, mixed $conditionValue): bool
	{
		if (is_array($requestValue) && is_array($conditionValue)) {
			$requestIsList = array_is_list($requestValue);
			$conditionIsList = array_is_list($conditionValue);

			if ($requestIsList && $conditionIsList) {
				return !self::listsIntersect($requestValue, $conditionValue);
			}

			if (!$requestIsList && !$conditionIsList) {
				return self::hasNoIntersection($requestValue, $conditionValue);
			}

			return self::normalizeComparableValue($requestValue) !== self::normalizeComparableValue($conditionValue);
		}

		if (is_array($requestValue)) {
			return array_is_list($requestValue)
				? !self::listContainsValue($requestValue, $conditionValue)
				: self::normalizeComparableValue($requestValue) !== self::normalizeComparableValue($conditionValue);
		}

		if (is_array($conditionValue)) {
			return array_is_list($conditionValue)
				? !self::listContainsValue($conditionValue, $requestValue)
				: self::normalizeComparableValue($requestValue) !== self::normalizeComparableValue($conditionValue);
		}

		return self::normalizeComparableValue($requestValue) !== self::normalizeComparableValue($conditionValue);
	}

	/**
	 * @param array<int, mixed> $left
	 * @param array<int, mixed> $right
	 */
	private static function listsIntersect(array $left, array $right): bool
	{
		$seen = [];
		foreach ($left as $value) {
			$seen[self::normalizeComparableValue($value)] = true;
		}

		foreach ($right as $value) {
			if (isset($seen[self::normalizeComparableValue($value)])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int, mixed> $list
	 */
	private static function listContainsValue(array $list, mixed $value): bool
	{
		$needle = self::normalizeComparableValue($value);
		foreach ($list as $item) {
			if (self::normalizeComparableValue($item) === $needle) {
				return true;
			}
		}

		return false;
	}

	private static function normalizeComparableValue(mixed $value): string
	{
		if (!is_array($value)) {
			return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null';
		}

		if (array_is_list($value)) {
			$normalized = [];
			foreach ($value as $item) {
				$normalized[] = self::normalizeComparableValue($item);
			}

			sort($normalized);
			return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
		}

		ksort($value);
		$normalized = [];
		foreach ($value as $key => $item) {
			$normalized[(string) $key] = self::normalizeComparableValue($item);
		}

		return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
	}

	/**
	 * @return array<int, string>
	 */
	private static function normalizeOperatorList(mixed $raw): array
	{
		if (is_string($raw) || is_int($raw)) {
			$raw = [$raw];
		}
		if (!is_array($raw)) {
			return [];
		}

		$seen = [];
		foreach ($raw as $value) {
			if (!is_string($value) && !is_int($value)) {
				continue;
			}

			$normalized = strtoupper(trim((string) $value));
			if ($normalized === '' || isset($seen[$normalized])) {
				continue;
			}

			$seen[$normalized] = true;
		}

		return array_keys($seen);
	}

	/**
	 * @return array<int, string>
	 */
	private static function normalizeAvailability(mixed $rawAvailability, InquiryableAvailabilityPolicy $policy): array
	{
		if (!is_array($rawAvailability) && (!is_string($rawAvailability) || trim($rawAvailability) === '')) {
			return $policy->baseStatuses();
		}

		$rawValues = is_array($rawAvailability) ? $rawAvailability : [$rawAvailability];
		$normalized = [];
		foreach ($rawValues as $value) {
			if (!is_string($value) && !is_int($value)) {
				continue;
			}

			$value = strtolower(trim((string) $value));
			if ($value === '') {
				continue;
			}

			$normalized[$value] = true;
		}

		return array_keys($normalized);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private static function normalizeParticipantsList(mixed $raw): array
	{
		if (!is_array($raw)) {
			return [];
		}

		$list = array_is_list($raw) ? $raw : array_values($raw);
		$out = [];
		foreach ($list as $participant) {
			if (!is_array($participant)) {
				continue;
			}

			$out[] = $participant;
		}

		return $out;
	}

	private static function requestFingerprint(SearchExecutionRequest $request): string
	{
		return SearchRequestFingerprint::hash([
			'search' => $request->search(),
			'filter' => $request->filter(),
			'results' => $request->results(),
			'views' => $request->views(),
			'options' => $request->options(),
		]);
	}
}
