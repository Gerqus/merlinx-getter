<?php

declare(strict_types=1);

use Skionline\MerlinxGetter\Details\OfferDetailsCacheKeyResolver;

require __DIR__ . '/bootstrap.php';

try {
	$resolver = new OfferDetailsCacheKeyResolver();

	$sharedMainPrefix = str_repeat('A', 70);
	$offerIdA = $sharedMainPrefix . 'TAIL_A|VITX|Mnx8';
	$offerIdB = $sharedMainPrefix . 'TAIL_B|VITX|Mnx8';

	$resolvedA = $resolver->resolve($offerIdA);
	$resolvedB = $resolver->resolve($offerIdB);

	assertTrue(($resolvedA['ok'] ?? false) === true, 'Expected resolver to resolve valid composite OfferId A.');
	assertTrue(($resolvedB['ok'] ?? false) === true, 'Expected resolver to resolve valid composite OfferId B.');
	assertSameValue(
		$resolvedA['cacheKeySource'] ?? null,
		$resolvedB['cacheKeySource'] ?? null,
		'Expected same cache key source for OfferIds sharing first 70 main chars + operator + pax.'
	);

	$resolvedMalformed = $resolver->resolve('not-a-composite-offer-id');
	assertTrue(($resolvedMalformed['ok'] ?? true) === false, 'Malformed OfferId should be rejected.');
	assertSameValue('malformed_offer_id', (string) ($resolvedMalformed['reason'] ?? ''), 'Malformed OfferId should report malformed_offer_id reason.');

	$resolvedMissingPax = $resolver->resolve($sharedMainPrefix . 'TAIL_C|VITX');
	assertTrue(($resolvedMissingPax['ok'] ?? true) === false, 'OfferId without pax part should be rejected.');
	assertSameValue('missing_pax', (string) ($resolvedMissingPax['reason'] ?? ''), 'OfferId without pax should report missing_pax reason.');

	echo "PASS: OfferDetailsCacheKeyResolver resolves key by main-prefix/operator/pax and rejects malformed inputs.\n";
	exit(0);
} catch (Throwable $e) {
	echo 'FAIL: ' . $e->getMessage() . "\n";
	exit(1);
}
