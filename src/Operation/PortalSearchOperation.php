<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Operation;

use JsonException;
use Skionline\MerlinxGetter\Contract\OperationInterface;
use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Skionline\MerlinxGetter\Exception\ResponseFormatException;
use Skionline\MerlinxGetter\Http\LoopbackHttpClient;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;

final class PortalSearchOperation implements OperationInterface
{
	private const ENDPOINT_URL = 'https://www.skionline.pl/wxp/?p=ofertyResultsJson';
	private const INVALID_JSON_PREVIEW_LIMIT = 180;
	private const TIMEOUT_RETRY_ATTEMPTS = 1;
	private const FALLBACK_ERROR_MESSAGE = 'Nie udało się pobrać ofert. Spróbuj ponownie za chwilę.';

	public function __construct(private readonly LoopbackHttpClient $client)
	{
	}

	public function key(): string
	{
		return 'portalSearch';
	}

	/**
	 * @param array<string, mixed> $params
	 * @return array{offers: array<int, array<string, mixed>>, query: array<string, mixed>, error: ?string, limitHit: bool}
	 */
	public function execute(array $params = []): array
	{
		$requestOptions = [
			'body' => http_build_query($params, '', '&', PHP_QUERY_RFC3986),
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
		];

		$response = null;
		for ($attempt = 0; $attempt <= self::TIMEOUT_RETRY_ATTEMPTS; $attempt++) {
			try {
				$response = $this->client->request('POST', self::ENDPOINT_URL, $requestOptions);
				break;
			} catch (HttpRequestException $e) {
				if (!$this->isTimeoutFailure($e)) {
					throw $e;
				}

				if ($attempt < self::TIMEOUT_RETRY_ATTEMPTS) {
					continue;
				}

				return $this->fallbackPayload();
			}
		}

		if ($response === null) {
			return $this->fallbackPayload();
		}

		try {
			$data = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			throw new ResponseFormatException(
				'Portal search response is invalid JSON. Body preview: ' . $this->bodyPreview($response->body()),
				0,
				$e
			);
		}

		if (!is_array($data)) {
			throw new ResponseFormatException('Portal search response has unexpected format.');
		}

		return $data;
	}

	private function isTimeoutFailure(HttpRequestException $exception): bool
	{
		return $exception->getPrevious() instanceof TimeoutExceptionInterface;
	}

	/**
	 * @return array{offers: array<int, array<string, mixed>>, query: array<string, mixed>, error: string, limitHit: bool}
	 */
	private function fallbackPayload(): array
	{
		return [
			'offers' => [],
			'query' => [],
			'error' => self::FALLBACK_ERROR_MESSAGE,
			'limitHit' => false,
		];
	}

	private function bodyPreview(string $body): string
	{
		$normalized = preg_replace('/\s+/u', ' ', trim($body));
		$normalized = is_string($normalized) ? trim($normalized) : trim($body);
		if ($normalized === '') {
			return '[empty body]';
		}

		if (strlen($normalized) <= self::INVALID_JSON_PREVIEW_LIMIT) {
			return $normalized;
		}

		return substr($normalized, 0, self::INVALID_JSON_PREVIEW_LIMIT) . '...';
	}
}
