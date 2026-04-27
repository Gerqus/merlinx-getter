<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Search\Execution;

final class SearchExecutionQuery
{
	/**
	 * @param array<string, array<int, string>> $responseFilters
	 */
	public function __construct(
		private readonly SearchExecutionRequest $request,
		private readonly array $responseFilters,
	) {
	}

	public function request(): SearchExecutionRequest
	{
		return $this->request;
	}

	/**
	 * @return array<string, array<int, string>>
	 */
	public function responseFilters(): array
	{
		return $this->responseFilters;
	}
}
