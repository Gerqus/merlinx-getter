<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Util;

final class DeepMerge
{
	/**
	 * @param array<string|int, mixed> $base
	 * @param array<string|int, mixed> $override
	 * @return array<string|int, mixed>
	 */
	public static function merge(array $base, array $override): array
	{
		if (empty($override)) {
			return $base;
		}
		if (empty($base)) {
			return $override;
		}

		$merged = $base;
		foreach ($override as $key => $overrideValue) {
			if (!array_key_exists($key, $base)) {
				$merged[$key] = $overrideValue;
				continue;
			}

			$baseValue = $base[$key];
			if (is_array($baseValue) && is_array($overrideValue)) {
				$baseIsList = array_is_list($baseValue);
				$overrideIsList = array_is_list($overrideValue);

				if ($baseIsList && $overrideIsList) {
					$merged[$key] = array_values(array_merge($baseValue, $overrideValue));
					continue;
				}

				if (!$baseIsList || !$overrideIsList) {
					$merged[$key] = self::merge($baseValue, $overrideValue);
					continue;
				}

				$merged[$key] = $overrideValue;
				continue;
			}

			$merged[$key] = $overrideValue;
		}

		return $merged;
	}
}
