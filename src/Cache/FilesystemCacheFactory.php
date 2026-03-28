<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Cache;

use Psr\SimpleCache\CacheInterface;
use RuntimeException;

final class FilesystemCacheFactory
{
	private readonly string $cacheDir;

	public function __construct(string $cacheDir)
	{
		$cacheDir = rtrim(trim($cacheDir), DIRECTORY_SEPARATOR);
		if ($cacheDir === '') {
			throw new RuntimeException('Cache directory cannot be empty.');
		}

		if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
			throw new RuntimeException('Unable to create cache directory: ' . $cacheDir);
		}

		$this->cacheDir = $cacheDir;
	}

	public function create(string $namespace): CacheInterface
	{
		$namespace = self::sanitizeNamespace($namespace);
		return new FileSimpleCache($this->cacheDir, $namespace);
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
