<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Execution;

final class SearchExecutionResult
{
	/**
	 * @param array<string, mixed> $response
	 * @param array<string, mixed> $meta
	 */
	public function __construct(
		private readonly array $response,
		private readonly array $meta = ['limitHits' => []],
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
	 * @return array{limitHits: array<string, bool>}
	 */
	public function meta(): array
	{
		$limitHits = [];
		$rawLimitHits = $this->meta['limitHits'] ?? null;
		if (is_array($rawLimitHits)) {
			foreach ($rawLimitHits as $viewName => $isHit) {
				if (!is_string($viewName) || $viewName === '' || $isHit !== true) {
					continue;
				}

				$limitHits[$viewName] = true;
			}
		}

		return ['limitHits' => $limitHits];
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
