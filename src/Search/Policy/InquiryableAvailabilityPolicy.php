<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Policy;

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;

final class InquiryableAvailabilityPolicy
{
	/**
	 * @param array<int, string> $baseStatuses
	 */
	private function __construct(
		private readonly array $baseStatuses,
		private readonly int $onRequestMinDays,
	) {
	}

	public static function fromConfig(MerlinxGetterConfig $config): self
	{
		$statuses = self::normalizeList($config->inquiryableAvailabilityBases());
		$minDays = $config->inquiryableOnrequestMinDays();
		if ($minDays < 0) {
			$minDays = 0;
		}

		return new self($statuses, $minDays);
	}

	/**
	 * @return array<int, string>
	 */
	public function baseStatuses(): array
	{
		return $this->baseStatuses;
	}

	public function onRequestMinDays(): int
	{
		return $this->onRequestMinDays;
	}

	public function isInquiryable(?string $base, ?string $startDate, ?\DateTimeImmutable $now = null): bool
	{
		$normalized = self::normalizeBase($base);
		if (empty($normalized)) {
			return true;
		}

		if (!in_array($normalized, $this->baseStatuses, true)) {
			return false;
		}

		if ($normalized !== 'onrequest') {
			return true;
		}

		$start = self::parseDate($startDate);
		if ($start === null) {
			return false;
		}

		$now = $now ?? new \DateTimeImmutable('now');
		$threshold = $now->setTime(0, 0)->modify('+' . $this->onRequestMinDays . ' days');
		$start = $start->setTime(0, 0);

		return $start >= $threshold;
	}

	private static function normalizeBase(?string $base): string
	{
		$base = is_string($base) ? trim($base) : '';
		return strtolower($base);
	}

	private static function parseDate(?string $value): ?\DateTimeImmutable
	{
		if (!is_string($value) || trim($value) === '') {
			return null;
		}

		$value = trim($value);
		$date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
		if ($date instanceof \DateTimeImmutable && $date->format('Y-m-d') === $value) {
			return $date;
		}

		try {
			return new \DateTimeImmutable($value);
		} catch (\Throwable) {
			return null;
		}
	}

	/**
	 * @param mixed $raw
	 * @return array<int, string>
	 */
	private static function normalizeList(mixed $raw): array
	{
		if (!is_array($raw)) {
			return [];
		}

		$out = [];
		foreach ($raw as $value) {
			if (!is_string($value)) {
				continue;
			}
			$normalized = strtolower(trim($value));
			if ($normalized === '') {
				continue;
			}
			$out[$normalized] = true;
		}

		return array_keys($out);
	}
}
