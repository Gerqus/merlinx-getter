<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

try {
	$operationFiles = [
		__DIR__ . '/../src/Operation/SearchOperation.php',
		__DIR__ . '/../src/Operation/GetDetailsOperation.php',
		__DIR__ . '/../src/Operation/GetLiveAvailabilityOperation.php',
	];

	$forbiddenImports = [
		'Http\\Auxiliary\\HttpErrorReporter',
		'Http\\Auxiliary\\RateLimitRetryEngine',
		'Http\\Models\\RetryPolicy',
	];

	foreach ($operationFiles as $path) {
		$content = file_get_contents($path);
		assertTrue(is_string($content), 'Unable to read operation file: ' . $path);

		foreach ($forbiddenImports as $forbiddenImport) {
			assertTrue(
				!str_contains($content, $forbiddenImport),
				'Operation file must not import Http internals (' . $forbiddenImport . '): ' . basename($path)
			);
		}
	}

	echo "PASS: operations depend only on Http module root surface, not Http internals.\n";
	exit(0);
} catch (Throwable $e) {
	echo 'FAIL: ' . $e->getMessage() . "\n";
	exit(1);
}
