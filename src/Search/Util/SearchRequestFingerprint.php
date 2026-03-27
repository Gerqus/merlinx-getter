<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Util;

use JsonException;

final class SearchRequestFingerprint
{
	/**
	 * @param array<string, mixed> $payload
	 */
	public static function hash(array $payload): string
	{
		$normalized = self::normalize($payload);
		try {
			$json = json_encode($normalized, JSON_THROW_ON_ERROR);
			return hash('sha256', $json);
		} catch (JsonException) {
			return hash('sha256', serialize($normalized));
		}
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array<string, mixed>
	 */
	public static function normalize(array $payload): array
	{
		$normalized = self::normalizeValue($payload, []);
		return is_array($normalized) ? $normalized : [];
	}

	private static function normalizeValue(mixed $value, array $path): mixed
	{
		if (is_object($value)) {
			$value = get_object_vars($value);
		}

		if (!is_array($value)) {
			return $value;
		}

		if (array_is_list($value)) {
			$list = [];
			foreach ($value as $item) {
				$list[] = self::normalizeValue($item, array_merge($path, ['*']));
			}

			if (self::isOrderInsensitiveScalarListPath($path) && self::isScalarList($list)) {
				$list = array_values(array_unique(array_map(static fn($v): string => (string) $v, $list)));
				sort($list, SORT_STRING);
			}

			return $list;
		}

		$out = [];
		$keys = array_keys($value);
		sort($keys, SORT_STRING);
		foreach ($keys as $key) {
			if (!is_string($key) && !is_int($key)) {
				continue;
			}
			$out[$key] = self::normalizeValue($value[$key], array_merge($path, [(string) $key]));
		}

		return $out;
	}

	/**
	 * @param array<int, mixed> $list
	 */
	private static function isScalarList(array $list): bool
	{
		foreach ($list as $value) {
			if (is_array($value) || is_object($value) || is_resource($value)) {
				return false;
			}
		}

		return true;
	}

	private static function isOrderInsensitiveScalarListPath(array $path): bool
	{
		$last = end($path);
		if (!is_string($last)) {
			return false;
		}

		return in_array($last, ['Operator', 'Availability', 'Attributes', 'fieldList'], true);
	}
}
