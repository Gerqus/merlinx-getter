<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Cache\FileSimpleCache;
use Skionline\MerlinxGetter\Cache\FilesystemCacheFactory;
use Skionline\MerlinxGetter\Cache\InvalidCacheArgumentException;

require __DIR__ . '/bootstrap.php';

try {
	$cacheDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
		. DIRECTORY_SEPARATOR
		. 'merlinx-getter-file-cache-test-'
		. str_replace('.', '-', uniqid('', true));
	if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
		throw new RuntimeException('Unable to create cache test directory.');
	}

	$factory = new FilesystemCacheFactory($cacheDir);
	$cache = $factory->create('cache-test');
	$otherNamespaceCache = $factory->create('cache-test-2');

	assertTrue($cache instanceof FileSimpleCache, 'Factory should build FileSimpleCache implementation.');

	assertSameValue(true, $cache->set('basic-key', ['foo' => 'bar']), 'set() should succeed for valid key.');
	assertSameValue(['foo' => 'bar'], $cache->get('basic-key'), 'get() should return previously stored value.');	
	assertSameValue(true, $cache->set('false-key', false), 'set() should persist false value.');
	assertSameValue(false, $cache->get('false-key', true), 'get() should preserve boolean false and not fallback to default.');
	assertSameValue(true, $cache->has('false-key'), 'has() should return true for existing key.');

	assertSameValue(true, $cache->setMultiple(['multi-a' => 1, 'multi-b' => 2]), 'setMultiple() should persist all keys.');
	$multi = $cache->getMultiple(['multi-a', 'multi-b', 'multi-missing'], 'fallback');
	assertSameValue(1, $multi['multi-a'] ?? null, 'getMultiple() value mismatch for multi-a.');
	assertSameValue(2, $multi['multi-b'] ?? null, 'getMultiple() value mismatch for multi-b.');
	assertSameValue('fallback', $multi['multi-missing'] ?? null, 'getMultiple() default mismatch for missing key.');
	assertSameValue(true, $cache->deleteMultiple(['multi-a', 'multi-b']), 'deleteMultiple() should delete existing keys.');
	assertSameValue(false, $cache->has('multi-a'), 'deleteMultiple() should remove multi-a.');
	assertSameValue(false, $cache->has('multi-b'), 'deleteMultiple() should remove multi-b.');

	assertSameValue(true, $cache->set('ttl-key', 'short-lived', 1), 'set() with ttl should succeed.');
	sleep(2);
	assertSameValue('expired-default', $cache->get('ttl-key', 'expired-default'), 'Expired key should fallback to default value.');
	assertSameValue(false, $cache->has('ttl-key'), 'Expired key should not be reported as existing.');

	assertThrows(
		static fn() => $cache->set('', 'x'),
		InvalidCacheArgumentException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'cannot be empty'), 'Expected invalid empty key error message.');
		}
	);
	assertThrows(
		static fn() => $cache->get('invalid/key'),
		InvalidCacheArgumentException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'reserved'), 'Expected reserved character key error message.');
		}
	);
	assertThrows(
		static fn() => $cache->setMultiple([123 => 'x']),
		InvalidCacheArgumentException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'must be a string'), 'Expected non-string key error for setMultiple.');
		}
	);
	assertThrows(
		static fn() => $cache->getMultiple(['ok', 123]),
		InvalidCacheArgumentException::class,
		static function (Throwable $e): void {
			assertTrue(str_contains($e->getMessage(), 'must be a string'), 'Expected non-string key error for getMultiple.');
		}
	);

	assertSameValue(true, $cache->set('namespace-key', 'ns-1'), 'Namespace value write should succeed for first namespace.');
	assertSameValue(true, $otherNamespaceCache->set('namespace-key', 'ns-2'), 'Namespace value write should succeed for second namespace.');
	assertSameValue('ns-1', $cache->get('namespace-key'), 'Namespace isolation failed for first namespace.');
	assertSameValue('ns-2', $otherNamespaceCache->get('namespace-key'), 'Namespace isolation failed for second namespace.');

	$namespace = 'cache-test';
	$fingerprint = hash('sha256', 'corrupt-json-key');
	$corruptFile = $cacheDir
		. DIRECTORY_SEPARATOR
		. $namespace
		. DIRECTORY_SEPARATOR
		. substr($fingerprint, 0, 2)
		. DIRECTORY_SEPARATOR
		. substr($fingerprint, 2, 2)
		. DIRECTORY_SEPARATOR
		. $fingerprint
		. '.json';

	assertSameValue(true, $cache->set('corrupt-json-key', 'ok'), 'Setup write for corrupt-json-key failed.');
	assertTrue(is_file($corruptFile), 'Corruption test setup failed - cache file not found.');
	if (file_put_contents($corruptFile, '{broken-json') === false) {
		throw new RuntimeException('Unable to write corrupt JSON cache payload.');
	}
	assertSameValue('corrupt-default', $cache->get('corrupt-json-key', 'corrupt-default'), 'Corrupted JSON should fallback to default.');
	assertSameValue(false, $cache->has('corrupt-json-key'), 'Corrupted JSON entry should be treated as missing.');

	$mismatchFingerprint = hash('sha256', 'mismatch-key');
	$mismatchFile = $cacheDir
		. DIRECTORY_SEPARATOR
		. $namespace
		. DIRECTORY_SEPARATOR
		. substr($mismatchFingerprint, 0, 2)
		. DIRECTORY_SEPARATOR
		. substr($mismatchFingerprint, 2, 2)
		. DIRECTORY_SEPARATOR
		. $mismatchFingerprint
		. '.json';

	assertSameValue(true, $cache->set('mismatch-key', 'ok'), 'Setup write for mismatch-key failed.');
	$mismatchPayload = [
		'key' => 'other-key',
		'expiresAt' => null,
		'encodedValue' => base64_encode(serialize('ok')),
	];
	if (file_put_contents($mismatchFile, json_encode($mismatchPayload, JSON_THROW_ON_ERROR)) === false) {
		throw new RuntimeException('Unable to write mismatch cache payload.');
	}
	assertSameValue('mismatch-default', $cache->get('mismatch-key', 'mismatch-default'), 'Mismatched key payload should fallback to default.');
	assertSameValue(false, $cache->has('mismatch-key'), 'Mismatched key payload should be treated as missing.');

	assertSameValue(true, $cache->set('clear-a', 1), 'Setup write clear-a failed.');
	assertSameValue(true, $cache->set('clear-b', 2), 'Setup write clear-b failed.');
	assertSameValue(true, $cache->clear(), 'clear() should remove namespace entries.');
	assertSameValue(false, $cache->has('clear-a'), 'clear() should remove clear-a.');
	assertSameValue(false, $cache->has('clear-b'), 'clear() should remove clear-b.');

	echo "PASS: FileSimpleCache handles core PSR-16 operations, TTL, invalid keys, corruption recovery, and namespace isolation.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
