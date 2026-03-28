<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Cache;

use DateInterval;
use DateTimeImmutable;
use Psr\SimpleCache\CacheInterface;

final class FileSimpleCache implements CacheInterface
{
	private readonly string $namespaceDir;

	public function __construct(
		private readonly string $cacheDir,
		string $namespace,
	) {
		$namespace = self::sanitizeNamespace($namespace);
		$this->namespaceDir = rtrim($this->cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $namespace;
		$this->ensureDirectoryExists($this->namespaceDir);
	}

	public function get(string $key, mixed $default = null): mixed
	{
		$this->assertValidKey($key);
		$entry = $this->readEntry($key);
		if ($entry === null) {
			return $default;
		}

		return $entry['value'];
	}

	public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
	{
		$this->assertValidKey($key);
		$expiresAt = $this->resolveExpiresAt($ttl);
		if ($expiresAt !== null && $expiresAt <= time()) {
			return $this->delete($key);
		}

		$filePath = $this->filePathForKey($key);
		$parentDir = dirname($filePath);
		$this->ensureDirectoryExists($parentDir);

		$payload = [
			'key' => $key,
			'expiresAt' => $expiresAt,
			'encodedValue' => base64_encode(serialize($value)),
		];

		$json = json_encode($payload, JSON_THROW_ON_ERROR);
		$tempPath = $filePath . '.' . bin2hex(random_bytes(6)) . '.tmp';
		if (file_put_contents($tempPath, $json, LOCK_EX) === false) {
			return false;
		}

		if (!rename($tempPath, $filePath)) {
			@unlink($tempPath);
			return false;
		}

		return true;
	}

	public function delete(string $key): bool
	{
		$this->assertValidKey($key);
		$filePath = $this->filePathForKey($key);
		if (!is_file($filePath)) {
			return true;
		}

		return @unlink($filePath);
	}

	public function clear(): bool
	{
		if (!is_dir($this->namespaceDir)) {
			return true;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($this->namespaceDir, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		$ok = true;
		foreach ($iterator as $item) {
			$path = $item->getPathname();
			if ($item->isDir()) {
				if (!@rmdir($path)) {
					$ok = false;
				}
				continue;
			}

			if (!@unlink($path)) {
				$ok = false;
			}
		}

		if (!@rmdir($this->namespaceDir) && is_dir($this->namespaceDir)) {
			$ok = false;
		}

		$this->ensureDirectoryExists($this->namespaceDir);
		return $ok;
	}

	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		$resolvedKeys = $this->normalizeKeys($keys);
		$values = [];
		foreach ($resolvedKeys as $key) {
			$values[$key] = $this->get($key, $default);
		}

		return $values;
	}

	public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
	{
		$allOk = true;
		foreach ($values as $key => $value) {
			if (!is_string($key)) {
				throw new InvalidCacheArgumentException('Cache key must be a string.');
			}
			if (!$this->set($key, $value, $ttl)) {
				$allOk = false;
			}
		}

		return $allOk;
	}

	public function deleteMultiple(iterable $keys): bool
	{
		$resolvedKeys = $this->normalizeKeys($keys);
		$allOk = true;
		foreach ($resolvedKeys as $key) {
			if (!$this->delete($key)) {
				$allOk = false;
			}
		}

		return $allOk;
	}

	public function has(string $key): bool
	{
		$this->assertValidKey($key);
		return $this->readEntry($key) !== null;
	}

	private function filePathForKey(string $key): string
	{
		$fingerprint = hash('sha256', $key);
		$segA = substr($fingerprint, 0, 2);
		$segB = substr($fingerprint, 2, 2);
		return $this->namespaceDir
			. DIRECTORY_SEPARATOR
			. $segA
			. DIRECTORY_SEPARATOR
			. $segB
			. DIRECTORY_SEPARATOR
			. $fingerprint
			. '.json';
	}

	private function assertValidKey(string $key): void
	{
		if ($key === '') {
			throw new InvalidCacheArgumentException('Cache key cannot be empty.');
		}

		if (preg_match('/[{}()\\/\\\\@:]/', $key) === 1) {
			throw new InvalidCacheArgumentException('Cache key contains reserved characters.');
		}
	}

	/**
	 * @param iterable<mixed> $keys
	 * @return array<int, string>
	 */
	private function normalizeKeys(iterable $keys): array
	{
		$out = [];
		foreach ($keys as $key) {
			if (!is_string($key)) {
				throw new InvalidCacheArgumentException('Cache key must be a string.');
			}
			$this->assertValidKey($key);
			$out[] = $key;
		}

		return $out;
	}

	/**
	 * @return array{value:mixed}|null
	 */
	private function readEntry(string $key): ?array
	{
		$filePath = $this->filePathForKey($key);
		if (!is_file($filePath)) {
			return null;
		}

		$content = @file_get_contents($filePath);
		if (!is_string($content) || $content === '') {
			@unlink($filePath);
			return null;
		}

		$payload = json_decode($content, true);
		if (!is_array($payload)) {
			@unlink($filePath);
			return null;
		}

		$storedKey = $payload['key'] ?? null;
		$encodedValue = $payload['encodedValue'] ?? null;
		$expiresAt = $payload['expiresAt'] ?? null;
		if (!is_string($storedKey) || $storedKey !== $key || !is_string($encodedValue)) {
			@unlink($filePath);
			return null;
		}

		if ($expiresAt !== null) {
			if (!is_int($expiresAt) && !(is_string($expiresAt) && ctype_digit($expiresAt))) {
				@unlink($filePath);
				return null;
			}

			if ((int) $expiresAt <= time()) {
				@unlink($filePath);
				return null;
			}
		}

		$serialized = base64_decode($encodedValue, true);
		if (!is_string($serialized)) {
			@unlink($filePath);
			return null;
		}

		$decoded = @unserialize($serialized, ['allowed_classes' => true]);
		if ($decoded === false && $serialized !== 'b:0;') {
			@unlink($filePath);
			return null;
		}

		return ['value' => $decoded];
	}

	private function resolveExpiresAt(null|int|DateInterval $ttl): ?int
	{
		if ($ttl === null) {
			return null;
		}

		if (is_int($ttl)) {
			return time() + $ttl;
		}

		$expiresAt = (new DateTimeImmutable())->add($ttl)->getTimestamp();
		return $expiresAt;
	}

	private function ensureDirectoryExists(string $dir): void
	{
		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			throw new \RuntimeException('Unable to create cache directory: ' . $dir);
		}
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
