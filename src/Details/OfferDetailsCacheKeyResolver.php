<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Details;

final class OfferDetailsCacheKeyResolver
{
	private const MAIN_OFFER_ID_PREFIX_LENGTH = 70;

	private OfferIdCompositeParser $offerIdParser;

	public function __construct(?OfferIdCompositeParser $offerIdParser = null)
	{
		$this->offerIdParser = $offerIdParser ?? new OfferIdCompositeParser();
	}

	/**
	 * @return array{ok: bool, cacheKeySource: ?string, reason: string}
	 */
	public function resolve(string $offerIdComposite): array
	{
		$offerIdComposite = trim($offerIdComposite);
		if ($offerIdComposite === '') {
			return $this->fail('empty_offer_id');
		}

		try {
			$parts = $this->offerIdParser->parse($offerIdComposite);
		} catch (\Throwable) {
			return $this->fail('malformed_offer_id');
		}

		$mainOfferId = trim((string) ($parts['mainOfferId'] ?? ''));
		$operatorCode = strtoupper(trim((string) ($parts['operatorCode'] ?? '')));
		$paxMetaBase64 = trim((string) ($parts['paxMetaBase64'] ?? ''));
		if ($mainOfferId === '' || $operatorCode === '') {
			return $this->fail('malformed_offer_id');
		}
		if ($paxMetaBase64 === '') {
			return $this->fail('missing_pax');
		}

		$mainPrefix = substr($mainOfferId, 0, self::MAIN_OFFER_ID_PREFIX_LENGTH);

		return [
			'ok' => true,
			'cacheKeySource' => $mainPrefix . '|' . $operatorCode . '|pax=' . $paxMetaBase64,
			'reason' => 'ok',
		];
	}

	/**
	 * @return array{ok: bool, cacheKeySource: ?string, reason: string}
	 */
	private function fail(string $reason): array
	{
		return [
			'ok' => false,
			'cacheKeySource' => null,
			'reason' => $reason,
		];
	}
}
