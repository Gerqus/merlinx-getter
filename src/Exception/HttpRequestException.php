<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Exception;

use Throwable;

final class HttpRequestException extends MerlinxGetterException
{
	public function __construct(
		string $message,
		private readonly ?int $statusCode = null,
		private readonly ?string $responseBody = null,
		?Throwable $previous = null
	) {
		parent::__construct($message, 0, $previous);
	}

	public function statusCode(): ?int
	{
		return $this->statusCode;
	}

	public function responseBody(): ?string
	{
		return $this->responseBody;
	}
}
