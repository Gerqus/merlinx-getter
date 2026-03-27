<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Details;

use InvalidArgumentException;

final class OfferIdCompositeParser
{
	/**
	 * @return array{mainOfferId:string, operatorCode:string, paxMetaBase64:?string}
	 */
	public function parse(string $offerIdComposite): array
	{
		$offerIdComposite = trim($offerIdComposite);
		if ($offerIdComposite === '') {
			throw new InvalidArgumentException('offerIdComposite is required.');
		}

		$parts = explode('|', $offerIdComposite, 3);
		$mainOfferId = trim((string) ($parts[0] ?? ''));
		$operatorCode = trim((string) ($parts[1] ?? ''));
		$paxMetaBase64 = isset($parts[2]) ? trim((string) $parts[2]) : null;
		$paxMetaBase64 = $paxMetaBase64 !== '' ? $paxMetaBase64 : null;

		if ($mainOfferId === '') {
			throw new InvalidArgumentException('Composite offerId is missing main offerId.');
		}
		if ($operatorCode === '') {
			throw new InvalidArgumentException('Composite offerId is missing operatorCode.');
		}

		return [
			'mainOfferId' => $mainOfferId,
			'operatorCode' => $operatorCode,
			'paxMetaBase64' => $paxMetaBase64,
		];
	}
}
