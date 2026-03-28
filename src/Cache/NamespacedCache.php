<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Cache;

use Psr\SimpleCache\CacheInterface;

final class NamespacedCache implements CacheInterface
{
	private readonly string $prefix;
	private readonly string $indexKey;

	public function __construct(
		private readonly CacheInterface $inner,
		string $namespace,
	) {
		$namespace = self::sanitizeNamespace($namespace);
		$this->prefix = $namespace . '.';
		$this->indexKey = $this->prefix . '__index';
	}

	public function get(string $key, mixed $default = null): mixed
	{
		return $this->inner->get($this->toInternalKey($key), $default);
	}

	public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
	{
		$internalKey = $this->toInternalKey($key);
		$result = $this->inner->set($internalKey, $value, $ttl);
		if ($result) {
			$this->rememberKey($internalKey);
		}
		return $result;
	}

	public function delete(string $key): bool
	{
		$internalKey = $this->toInternalKey($key);
		$result = $this->inner->delete($internalKey);
		if ($result) {
			$this->forgetKey($internalKey);
		}
		return $result;
	}

	public function clear(): bool
	{
		$keys = $this->readKnownKeys();
		$result = true;
		if ($keys !== []) {
			$result = $this->inner->deleteMultiple($keys);
		}

		$indexDeleted = $this->inner->delete($this->indexKey);
		return $result && $indexDeleted;
	}

	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		$keys = is_array($keys) ? $keys : iterator_to_array($keys, false);
		$mapping = [];
		$internalKeys = [];
		foreach ($keys as $key) {
			if (!is_string($key)) {
				continue;
			}
			$internal = $this->toInternalKey($key);
			$mapping[$internal] = $key;
			$internalKeys[] = $internal;
		}

		$internalValues = $this->inner->getMultiple($internalKeys, $default);
		$out = [];
		foreach ($internalValues as $internal => $value) {
			$original = $mapping[(string) $internal] ?? null;
			if (!is_string($original)) {
				continue;
			}
			$out[$original] = $value;
		}

		return $out;
	}

	public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
	{
		$values = is_array($values) ? $values : iterator_to_array($values, true);
		$internal = [];
		foreach ($values as $key => $value) {
			if (!is_string($key)) {
				continue;
			}
			$internal[$this->toInternalKey($key)] = $value;
		}

		if ($internal === []) {
			return true;
		}

		$result = $this->inner->setMultiple($internal, $ttl);
		if ($result) {
			foreach (array_keys($internal) as $internalKey) {
				$this->rememberKey($internalKey);
			}
		}

		return $result;
	}

	public function deleteMultiple(iterable $keys): bool
	{
		$keys = is_array($keys) ? $keys : iterator_to_array($keys, false);
		$internal = [];
		foreach ($keys as $key) {
			if (!is_string($key)) {
				continue;
			}
			$internal[] = $this->toInternalKey($key);
		}

		if ($internal === []) {
			return true;
		}

		$result = $this->inner->deleteMultiple($internal);
		if ($result) {
			foreach ($internal as $internalKey) {
				$this->forgetKey($internalKey);
			}
		}

		return $result;
	}

	public function has(string $key): bool
	{
		return $this->inner->has($this->toInternalKey($key));
	}

	private function toInternalKey(string $key): string
	{
		return $this->prefix . $key;
	}

	private function rememberKey(string $internalKey): void
	{
		$keys = $this->readKnownKeys();
		$keys[$internalKey] = true;
		$this->inner->set($this->indexKey, $keys);
	}

	private function forgetKey(string $internalKey): void
	{
		$keys = $this->readKnownKeys();
		if (!isset($keys[$internalKey])) {
			return;
		}

		unset($keys[$internalKey]);
		if ($keys === []) {
			$this->inner->delete($this->indexKey);
			return;
		}

		$this->inner->set($this->indexKey, $keys);
	}

	/**
	 * @return array<string, bool>
	 */
	private function readKnownKeys(): array
	{
		$raw = $this->inner->get($this->indexKey, []);
		if (!is_array($raw)) {
			return [];
		}

		$out = [];
		foreach ($raw as $key => $present) {
			if (!is_string($key)) {
				continue;
			}
			$out[$key] = (bool) $present;
		}

		return $out;
	}

	private static function sanitizeNamespace(string $namespace): string
	{
		$namespace = trim($namespace);
		if ($namespace === '') {
			return 'merlinx_getter';
		}

		$namespace = strtolower($namespace);
		$namespace = preg_replace('/[^a-z0-9._-]/', '_', $namespace) ?? '';
		return $namespace !== '' ? $namespace : 'merlinx_getter';
	}
}
