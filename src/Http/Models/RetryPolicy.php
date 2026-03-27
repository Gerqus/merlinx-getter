<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Http\Models;

use Skionline\MerlinxGetter\Config\MerlinxGetterConfig;

final class RetryPolicy
{
	private const DEFAULT_MAX_ATTEMPTS = 4;
	private const DEFAULT_INITIAL_DELAY_MS = 500;
	private const DEFAULT_BACKOFF_MULTIPLIER = 2.0;
	private const DEFAULT_MAX_DELAY_MS = 8000;

	private readonly int $maxAttempts;
	private readonly int $initialDelayMs;
	private readonly float $backoffMultiplier;
	private readonly int $maxDelayMs;

	/**
	 * @param array<string, mixed> $values
	 */
	private function __construct(array $values)
	{
		$this->maxAttempts = self::toNonNegativeInt($values['maxAttempts'] ?? null, self::DEFAULT_MAX_ATTEMPTS);
		$this->initialDelayMs = self::toNonNegativeInt($values['initialDelayMs'] ?? null, self::DEFAULT_INITIAL_DELAY_MS);
		$this->backoffMultiplier = self::toPositiveFloat($values['backoffMultiplier'] ?? null, self::DEFAULT_BACKOFF_MULTIPLIER);
		$this->maxDelayMs = self::toNonNegativeInt($values['maxDelayMs'] ?? null, self::DEFAULT_MAX_DELAY_MS);
	}

	public static function fromConfig(MerlinxGetterConfig $config): self
	{
		return self::fromOptions($config->defaultSearchOptions);
	}

	/**
	 * @param array<string, mixed> $options
	 */
	public static function fromOptions(array $options): self
	{
		return new self([
			'maxAttempts' => $options['rateLimitRetryMaxAttempts'] ?? null,
			'initialDelayMs' => $options['rateLimitRetryDelayMs'] ?? null,
			'backoffMultiplier' => $options['rateLimitRetryBackoffMultiplier'] ?? null,
			'maxDelayMs' => $options['rateLimitRetryMaxDelayMs'] ?? null,
		]);
	}

	public function maxAttempts(): int
	{
		return $this->maxAttempts;
	}

	public function initialDelayMs(): int
	{
		return $this->initialDelayMs;
	}

	public function backoffMultiplier(): float
	{
		return $this->backoffMultiplier;
	}

	public function maxDelayMs(): int
	{
		return $this->maxDelayMs;
	}

	private static function toNonNegativeInt(mixed $value, int $default): int
	{
		if (is_int($value)) {
			return max(0, $value);
		}

		if (is_string($value) && ctype_digit(trim($value))) {
			return (int) trim($value);
		}

		return $default;
	}

	private static function toPositiveFloat(mixed $value, float $default): float
	{
		if (is_int($value) || is_float($value)) {
			return (float) max(1.0, $value);
		}

		if (is_string($value) && is_numeric($value)) {
			return max(1.0, (float) $value);
		}

		return $default;
	}
}
