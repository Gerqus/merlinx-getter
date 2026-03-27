<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Util;

final class ToObjectDeep
{
	public static function apply(mixed $value): mixed
	{
		if (is_array($value)) {
			foreach ($value as $key => $item) {
				if (is_array($item)) {
					$value[$key] = self::apply($item);
				}
			}
			if (array_keys($value) === range(0, count($value) - 1)) {
				return $value;
			}
			if ($value === []) {
				return new \stdClass();
			}
			return (object) $value;
		}

		if (is_object($value)) {
			foreach (get_object_vars($value) as $key => $item) {
				if (is_array($item)) {
					$value->$key = self::apply($item);
				}
			}
			return $value;
		}

		return $value;
	}
}
