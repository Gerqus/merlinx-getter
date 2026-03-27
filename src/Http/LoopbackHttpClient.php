<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Http;

use Skionline\MerlinxGetter\Exception\HttpRequestException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class LoopbackHttpClient
{
	private readonly HttpClientInterface $httpClient;

	public function __construct(
		?HttpClientInterface $httpClient = null,
		private readonly float $timeout = 15.0,
	) {
		$this->httpClient = $httpClient ?? HttpClient::create();
	}

	/**
	 * @param array<string, mixed> $options
	 */
	public function request(string $method, string $url, array $options = []): HttpResponse
	{
		if (!isset($options['timeout'])) {
			$options['timeout'] = $this->timeout;
		}

		try {
			$response = $this->httpClient->request($method, $url, $options);
			$status = $response->getStatusCode();
			$headers = $response->getHeaders(false);
			$body = $response->getContent(false);
		} catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
			throw new HttpRequestException('Portal search HTTP request failed.', null, null, $e);
		}

		return new HttpResponse($status, $headers, $body, 1);
	}
}
