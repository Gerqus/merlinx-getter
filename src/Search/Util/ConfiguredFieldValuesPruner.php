<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Util;

final class ConfiguredFieldValuesPruner
{
	/**
	 * @param array<string, true> $enforcedAccommodationAttributes
	 */
	public function __construct(
		private readonly array $enforcedAccommodationAttributes,
	) {
	}

	/**
	 * @param array<string, mixed> $response
	 * @return array<string, mixed>
	 */
	public function apply(array $response): array
	{
		if ($this->enforcedAccommodationAttributes === []) {
			return $response;
		}

		foreach (['fieldValues', 'unfilteredFieldValues'] as $viewName) {
			$view = $response[$viewName] ?? null;
			if (!is_array($view)) {
				continue;
			}

			$response[$viewName] = $this->pruneAccommodationAttributes($view);
		}

		return $response;
	}

	/**
	 * @param array<string, mixed> $fieldValues
	 * @return array<string, mixed>
	 */
	private function pruneAccommodationAttributes(array $fieldValues): array
	{
		$values = $fieldValues['Accommodation.Attributes'] ?? null;
		if (!is_array($values)) {
			return $fieldValues;
		}

		if (array_is_list($values)) {
			$pruned = [];
			$seen = [];
			foreach ($values as $value) {
				$normalized = $this->normalizeAttributeCode($value);
				if ($normalized === '' || isset($this->enforcedAccommodationAttributes[$normalized]) || isset($seen[$normalized])) {
					continue;
				}

				$seen[$normalized] = true;
				$pruned[] = trim((string) $value);
			}

			$fieldValues['Accommodation.Attributes'] = $pruned;

			return $fieldValues;
		}

		$pruned = [];
		$seen = [];
		foreach ($values as $key => $value) {
			$attributeKey = is_string($key) || is_int($key) ? trim((string) $key) : '';
			$normalized = $this->normalizeAttributeCode($attributeKey !== '' ? $attributeKey : $value);
			if ($normalized === '' || isset($this->enforcedAccommodationAttributes[$normalized]) || isset($seen[$normalized])) {
				continue;
			}

			$seen[$normalized] = true;
			$pruned[$key] = $value;
		}

		$fieldValues['Accommodation.Attributes'] = $pruned;

		return $fieldValues;
	}

	private function normalizeAttributeCode(mixed $value): string
	{
		if (!is_string($value) && !is_int($value) && !is_float($value)) {
			return '';
		}

		$value = trim((string) $value);
		if ($value === '') {
			return '';
		}

		return ltrim($value, '+-');
	}
}
