<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Cache;

use RuntimeException;

final class FileKeyLock
{
	private readonly string $lockDir;
	private readonly int $timeoutMs;
	private readonly int $retryDelayMs;

	public function __construct(string $lockDir, int $timeoutMs, int $retryDelayMs)
	{
		$lockDir = rtrim(trim($lockDir), DIRECTORY_SEPARATOR);
		if ($lockDir === '') {
			throw new RuntimeException('Lock directory cannot be empty.');
		}

		if (!is_dir($lockDir) && !mkdir($lockDir, 0755, true) && !is_dir($lockDir)) {
			throw new RuntimeException('Unable to create lock directory: ' . $lockDir);
		}

		$this->lockDir = $lockDir;
		$this->timeoutMs = max(0, $timeoutMs);
		$this->retryDelayMs = max(1, $retryDelayMs);
	}

	/**
	 * @template T
	 * @param callable():T $callback
	 * @return T
	 */
	public function withLock(string $key, callable $callback): mixed
	{
		$handle = $this->acquire($key);
		if (!is_resource($handle)) {
			return $callback();
		}

		try {
			return $callback();
		} finally {
			flock($handle, LOCK_UN);
			fclose($handle);
		}
	}

	/**
	 * @return resource|null
	 */
	private function acquire(string $key)
	{
		$filePath = $this->lockFilePath($key);
		$handle = fopen($filePath, 'c');
		if (!is_resource($handle)) {
			return null;
		}

		$deadline = microtime(true) + ($this->timeoutMs / 1000);
		do {
			if (flock($handle, LOCK_EX | LOCK_NB)) {
				return $handle;
			}

			usleep($this->retryDelayMs * 1000);
		} while (microtime(true) < $deadline);

		fclose($handle);
		return null;
	}

	private function lockFilePath(string $key): string
	{
		$fingerprint = hash('sha256', $key);
		return $this->lockDir . DIRECTORY_SEPARATOR . 'merlinx-getter-lock-' . $fingerprint . '.lock';
	}
}
