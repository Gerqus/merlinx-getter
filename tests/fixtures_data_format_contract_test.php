<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

try {
	$offerPage1 = fixtureJson('search/offer_list_page1.json');
	$offerItems = $offerPage1['offerList']['items'] ?? null;
	assertTrue(is_array($offerItems), 'search fixture: offerList.items should be array.');
	assertTrue(!array_is_list($offerItems), 'search fixture: offerList.items should be object-map style (associative), not list.');
	assertSameValue('offer-key-1|SNOW|NHx8', $offerItems['offer-key-1']['offer']['Base']['OfferId'] ?? null, 'search fixture: OfferId mismatch.');
	assertSameValue('1000.00', $offerItems['offer-key-1']['offer']['Base']['Price']['Total']['amount'] ?? null, 'search fixture: Total amount should be numeric string.');

	$offerPage2 = fixtureJson('search/offer_list_page2.json');
	assertSameValue('bm-2', $offerPage2['offerList']['pageBookmark'] ?? null, 'search fixture page 2 bookmark mismatch.');
	assertSameValue(false, $offerPage2['offerList']['more'] ?? null, 'search fixture page 2 more flag mismatch.');

	$fieldValues = fixtureJson('search/field_values_basic.json');
	assertTrue(is_array($fieldValues['fieldValues']['fieldValues'] ?? null), 'fieldValues fixture: nested fieldValues map missing.');
	assertTrue(is_array($fieldValues['fieldValues']['fieldValues']['Accommodation.Room'] ?? null), 'fieldValues fixture: Accommodation.Room should be map-like object.');
	assertSameValue('Pokój 2 os.', $fieldValues['fieldValues']['fieldValues']['Accommodation.Room']['DBL'] ?? null, 'fieldValues fixture: Accommodation.Room.DBL mismatch.');
	assertTrue(is_array($fieldValues['fieldValues']['fieldValues']['Base.StartDate'] ?? null), 'fieldValues fixture: Base.StartDate should be list.');
	assertSameValue('2026-03-07', $fieldValues['fieldValues']['fieldValues']['Base.StartDate'][0] ?? null, 'fieldValues fixture: Base.StartDate first value mismatch.');

	$checkonline = fixtureJson('checkonline/success.json');
	assertTrue(is_array($checkonline['results'] ?? null), 'checkonline fixture: results should be list.');
	assertSameValue('offer-123|SNOW|NHx8', $checkonline['results'][0]['OfferId'] ?? null, 'checkonline fixture: OfferId key/value mismatch.');
	assertSameValue('checkstatus', $checkonline['results'][0]['action'] ?? null, 'checkonline fixture: action key/value mismatch.');
	assertSameValue('1851.00', $checkonline['results'][0]['offer']['Base']['Price']['FirstPerson']['amount'] ?? null, 'checkonline fixture: FirstPerson amount should be numeric string.');
	assertSameValue('7166.00', $checkonline['results'][0]['offer']['Base']['Price']['Total']['amount'] ?? null, 'checkonline fixture: Total amount should be numeric string.');

	$rateLimitPayload = file_get_contents(__DIR__ . '/fixtures/search/rate_limited_payload.txt');
	assertTrue(is_string($rateLimitPayload), 'rate-limited fixture should be readable text.');
	assertTrue(str_contains(strtolower($rateLimitPayload), 'too many requests'), 'rate-limited fixture should include "Too many requests" marker.');

	echo "PASS: Curated fixtures preserve expected MerlinX key casing, object/list shapes, and value formats.\n";
	exit(0);
} catch (Throwable $e) {
	echo "FAIL: " . $e->getMessage() . "\n";
	exit(1);
}
