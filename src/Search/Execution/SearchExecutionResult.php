<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Execution;

final class SearchExecutionResult
{
	/**
	 * @param array<string, mixed> $response
	 */
	public function __construct(
		private readonly array $response,
	) {
	}

	/**
	 * @return array<string, mixed>
	 */
	public function response(): array
	{
		return $this->response;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function view(string $name): ?array
	{
		$view = $this->response[$name] ?? null;
		return is_array($view) ? $view : null;
	}
}
