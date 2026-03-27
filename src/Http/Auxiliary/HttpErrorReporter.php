<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Http\Auxiliary;

final class HttpErrorReporter
{
	public function buildMessage(
		string $summary,
		string $method,
		string $endpoint,
		?int $attempt = null,
		?int $maxAttempts = null,
		?string $responseBody = null,
		?string $requestSnippet = null,
		?string $queryFingerprint = null,
	): string {
		$parts = [
			rtrim(trim($summary), '.') . '.',
			'Endpoint: ' . strtoupper($method) . ' ' . trim($endpoint) . '.',
		];

		if ($attempt !== null && $maxAttempts !== null) {
			$parts[] = 'Attempt: ' . $attempt . '/' . $maxAttempts . '.';
		} elseif ($attempt !== null) {
			$parts[] = 'Attempt: ' . $attempt . '.';
		}

		if (is_string($queryFingerprint) && trim($queryFingerprint) !== '') {
			$parts[] = 'Query fingerprint: ' . trim($queryFingerprint) . '.';
		}

		if ($responseBody !== null) {
			$parts[] = 'Response snippet: ' . $this->buildSnippet($responseBody) . '.';
		}

		if ($requestSnippet !== null) {
			$parts[] = 'Request payload snippet: ' . $this->buildSnippet($requestSnippet) . '.';
		}

		return implode(' ', $parts);
	}

	public function buildSnippet(mixed $value, int $maxLength = 5000): string
	{
		if (is_string($value)) {
			$serialized = trim($value);
		} else {
			$encoded = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
			$serialized = is_string($encoded) ? $encoded : '[unserializable payload]';
		}

		$normalized = preg_replace('/\s+/', ' ', trim($serialized));
		if (!is_string($normalized) || $normalized === '') {
			$normalized = '[empty]';
		}

		if (strlen($normalized) <= $maxLength) {
			return $normalized;
		}

		return substr($normalized, 0, $maxLength) . '...(truncated)';
	}
}
