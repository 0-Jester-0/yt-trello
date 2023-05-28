<?php

namespace App\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use \Symfony\Contracts\HttpClient\Exception\{
	ClientExceptionInterface,
	RedirectionExceptionInterface,
	ServerExceptionInterface,
	TransportExceptionInterface,
	DecodingExceptionInterface,
};

use Psr\Log\LoggerInterface;

abstract class MainApiClient
{
	/**
	 * Api Service Token
	 * @var string
	 */
	private const API_TOKEN = "";

	/**
	 * Api Service Url
	 * @var string
	 */
	protected const URL = "";

	/**
	 * GET request method code
	 * @var string
	 */
	protected const GET_METHOD_CODE = "GET";

	/**
	 * POST request method code
	 * @var string
	 */
	protected const POST_METHOD_CODE = "POST";

	/**
	 * PUT request method code
	 * @var string
	 */
	protected const PUT_METHOD_CODE = "PUT";

	/**
	 * Default headers for create request
	 * @var string[]
	 */
	public const DEFAULT_HEADERS = [
		"Accept" => "application/json",
		"Content-Type" => "application/json",
	];

	/**
	 * Response status messages
	 * @var string[]
	 */
	protected const STATUS_CODES = [
		"200" => "OK",
		"401" => "Unauthorized",
		"403" => "Forbidden",
		"404" => "Not Found",
		"408" => "Request Timeout",
		"500" => "Internal Server Error",
		"502" => "Bad Gateway",
		"503" => "Service Unavailable",
		"504" => "Gateway Timeout",
		"505" => "HTTP Version Not Supported",
	];

	/**
	 * @param string $url
	 * @param HttpClientInterface $client
	 * @param array $headers
	 */
	public function __construct(
		private readonly HttpClientInterface $client,
		private string                       $url = self::URL,
		private array                        $headers = [],
	)
	{
		$this->setUrl(static::URL);
		$this->setHeaders(static::DEFAULT_HEADERS);
	}

	/**
	 * Method for creating GET request using RequestInterface
	 *
	 * @param array $queryParams
	 * @param string $entityNameForUrl
	 * @return ResponseInterface
	 * @throws TransportExceptionInterface
	 */
	public function createGETRequest(string $entityNameForUrl, array $queryParams): ResponseInterface
	{
		$url = $entityNameForUrl ? "{$this->getUrl()}/$entityNameForUrl" : $this->getUrl();
		return $this->client->request(static::GET_METHOD_CODE, $url, [
				"headers" => $this->getHeaders(),
				"query" => $queryParams["query"]
			]
		);
	}

	/**
	 * Method for creating POST request using RequestInterface
	 *
	 * @param array $queryParams
	 * @param string $entityNameForUrl
	 * @return ResponseInterface
	 * @throws TransportExceptionInterface
	 */
	public function createPOSTRequest(string $entityNameForUrl, array $queryParams): ResponseInterface
	{
		$url = $entityNameForUrl ? "{$this->getUrl()}/$entityNameForUrl" : $this->getUrl();
		return $this->client->request(static::POST_METHOD_CODE, $url, [
				"headers" => $this->getHeaders(),
				"query" => $queryParams["query"],
				"body" => $queryParams["body"]
			]
		);
	}

	/**
	 * Method for creating PUT request using RequestInterface
	 *
	 * @param string $entityNameForUrl
	 * @param array $queryParams
	 * @return ResponseInterface
	 * @throws TransportExceptionInterface
	 */
	public function createPUTRequest(string $entityNameForUrl, array $queryParams): ResponseInterface
	{
		$url = $entityNameForUrl ? "{$this->getUrl()}/$entityNameForUrl" : $this->getUrl();
		return $this->client->request(static::PUT_METHOD_CODE, $url, [
				"headers" => $this->getHeaders(),
				"query" => $queryParams["query"],
				"body" => $queryParams["body"]
			]
		);
	}

	/**
	 * Method for setting url address api server
	 *
	 * @param string $url
	 * @return void
	 */
	public function setUrl(string $url): void
	{
		$this->url = $url;
	}

	/**
	 * Method for getting url address api server
	 *
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * Method for setting request headers
	 *
	 * @param array $headers
	 * @return void
	 */
	public function setHeaders(array $headers): void
	{
		$this->headers = $headers;
	}

	/**
	 * Method for add new request headers
	 *
	 * @param array $additionalHeaders
	 * @return void
	 */
	public function addHeaders(array $additionalHeaders): void
	{
		$this->headers += $additionalHeaders;
	}

	/**
	 * Method for getting request headers
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * Method getting content form response
	 *
	 * @param ResponseInterface $response
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getContent(ResponseInterface $response): array|string|null
	{
		$statusCode = $response->getStatusCode();
		if (static::STATUS_CODES[$statusCode] === "OK") {
			return $response->getHeaders()['content-type'][0] ? $response->toArray() : "";
		} else {
			return static::STATUS_CODES[$response->getStatusCode()];
		}
	}
}