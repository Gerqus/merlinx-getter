<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Http;

final class HttpResponse
{
	/**
	 * @param array<string, array<int, string>> $headers
	 */
	public function __construct(
		private readonly int $statusCode,
		private readonly array $headers,
		private readonly string $body,
		private readonly int $attemptsMade = 1,
	) {
	}

	public function statusCode(): int
	{
		return $this->statusCode;
	}

	/**
	 * @return array<string, array<int, string>>
	 */
	public function headers(): array
	{
		return $this->headers;
	}

	public function body(): string
	{
		return $this->body;
	}

	public function attemptsMade(): int
	{
		return $this->attemptsMade;
	}
}
