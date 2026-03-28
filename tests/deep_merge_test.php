<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Search\Util\DeepMerge;

require __DIR__ . '/helpers/bootstrap.php';

try {
	$sample = ['a' => 1, 'nested' => ['x' => true]];
	assertSameValue($sample, DeepMerge::merge([], $sample), 'Empty base should preserve override side as-is.');
	assertSameValue($sample, DeepMerge::merge($sample, []), 'Empty override should preserve base side as-is.');

	assertSameValue(
		['a' => 2],
		DeepMerge::merge(['a' => 1], ['a' => 2]),
		'Scalar values should be overridden by the override side.'
	);

	assertSameValue(
		['a' => [1, 2, 3, 4]],
		DeepMerge::merge(['a' => [1, 2]], ['a' => [3, 4]]),
		'List + list should concatenate in order.'
	);

	assertSameValue(
		['a' => ['x' => 1, 'y' => 2]],
		DeepMerge::merge(['a' => ['x' => 1]], ['a' => ['y' => 2]]),
		'Nested associative arrays should be merged recursively.'
	);

	$realWorldMerged = DeepMerge::merge(
		['Accommodation' => ['XCode' => [216116]]],
		['Accommodation' => ['Attributes' => ['-ski']]]
	);
	assertSameValue([216116], $realWorldMerged['Accommodation']['XCode'] ?? null, 'Accommodation.XCode should be preserved.');
	assertSameValue(['-ski'], $realWorldMerged['Accommodation']['Attributes'] ?? null, 'Accommodation.Attributes should coexist with XCode.');

	echo "PASS: DeepMerge preserves empty-side behavior, scalar overrides, list concat, and nested coexistence.\n";
	exit(0);
} catch (Throwable $e) {
	echo 'FAIL: ' . $e->getMessage() . "\n";
	exit(1);
}
