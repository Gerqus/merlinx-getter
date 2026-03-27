<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Http\Auxiliary;

use Skionline\MerlinxGetter\Http\Models\RetryPolicy;

final class RateLimitRetryEngine
{
	public function isRateLimited(int $status, string $body): bool
	{
		return $status === 429 || $this->isRateLimitedPayload($body);
	}

	public function isRateLimitedThrowable(\Throwable $exception): bool
	{
		$message = strtolower($exception->getMessage());
		return str_contains($message, 'too many requests') || str_contains($message, 'status 429');
	}

	/**
	 * @param array<string, array<int, string>> $headers
	 */
	public function extractRetryAfterMs(array $headers): ?int
	{
		$retryAfter = $headers['retry-after'] ?? $headers['Retry-After'] ?? null;
		if (!is_array($retryAfter) && !is_string($retryAfter) && !is_int($retryAfter) && !is_float($retryAfter)) {
			return null;
		}

		$value = is_array($retryAfter) ? ($retryAfter[0] ?? null) : $retryAfter;
		if (!is_string($value) && !is_int($value) && !is_float($value)) {
			return null;
		}

		$stringValue = trim((string) $value);
		if ($stringValue === '') {
			return null;
		}

		if (is_numeric($stringValue)) {
			$seconds = max(0.0, (float) $stringValue);
			return (int) round($seconds * 1000);
		}

		$timestamp = strtotime($stringValue);
		if ($timestamp === false) {
			return null;
		}

		$seconds = max(0, $timestamp - time());
		return $seconds * 1000;
	}

	public function nextDelayMs(int $currentDelayMs, RetryPolicy $policy): int
	{
		if ($currentDelayMs <= 0) {
			return 0;
		}

		$maxDelayMs = $policy->maxDelayMs();
		if ($maxDelayMs <= 0) {
			return 0;
		}

		$nextDelayMs = (int) round($currentDelayMs * $policy->backoffMultiplier());
		$nextDelayMs = max($currentDelayMs, $nextDelayMs);

		return min($nextDelayMs, $maxDelayMs);
	}

	public function wait(int $delayMs, ?int $retryAfterMs = null): void
	{
		$finalDelayMs = max($delayMs, $retryAfterMs ?? 0);
		if ($finalDelayMs > 0) {
			usleep($finalDelayMs * 1000);
		}
	}

	private function isRateLimitedPayload(string $body): bool
	{
		$payload = strtolower(trim($body));
		if ($payload === '') {
			return false;
		}

		return str_contains($payload, 'too many requests');
	}
}
