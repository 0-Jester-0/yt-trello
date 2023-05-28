<?php

namespace App\Api\Trello;

use App\Api\MainApiClient;
use Symfony\Contracts\HttpClient\ResponseInterface;

use \Symfony\Contracts\HttpClient\Exception\{
	ClientExceptionInterface,
	RedirectionExceptionInterface,
	ServerExceptionInterface,
	TransportExceptionInterface,
	DecodingExceptionInterface
};

class TrelloApi extends MainApiClient
{
	/** @var string Api Service Token */
	private const API_TOKEN = "ATTA7b3b670643f2ecce58fee31f8904cf71f7196deb8e4a9d4196ea1df6061624b3AA3D219E";

	/** @var string Api Service Key */
	private const API_KEY = "d74351bf99549898ec08ca2173839022";

	/** @var string Api Service Url */
	public const URL = "https://api.trello.com/1";

	/** @var string Project ID */
	private const PROJECT_ID = "644903c95fb4c2018fa31abd";

	/** @var string Root user ID */
	private const ROOT_ID = "620abba316dedf84698d79b5";

	/** @var string "Estimate" field name */
	public const ESTIMATE_FIELD_NAME = "Estimate";

	/** @var string "Fact" field name */
	public const FACT_TIME_FIELD_NAME = "Fact";

	/**
	 * Request for getting card list from project board
	 *
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getBoardCards(): array|string|null
	{
		return $this->getContent(
			$this->createGETRequest("/boards/" . static::PROJECT_ID . "/cards", [
				"query" => [
					"key" => static::API_KEY,
					"token" => static::API_TOKEN
				]
			])
		);
	}

	/**
	 * Method for getting a board's lists
	 *
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getBoardLists(): array|string|null
	{
		return $this->getContent(
			$this->createGETRequest("/board/" . static::PROJECT_ID . "/lists", [
				"query" => [
					"key" => static::API_KEY,
					"token" => static::API_TOKEN
				]
			])
		);
	}

	/**
	 * Method for getting a list's cards
	 *
	 * @param string $listId
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getListCards(string $listId): array|string|null
	{
		return $this->getContent(
			$this->createGETRequest("/lists/$listId/cards", [
				"query" => [
					"key" => static::API_KEY,
					"token" => static::API_TOKEN
				]
			])
		);
	}

	/**
	 * Method for getting a board "Demo Project"
	 *
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getBoard(): array|string|null
	{
		return $this->getContent(
			$this->createGETRequest("boards/" . static::PROJECT_ID, [
				"query" => [
					"key" => static::API_KEY,
					"token" => static::API_TOKEN
				]
			])
		);
	}

	/**
	 * @param string $cardID
	 * @param array $customFieldInfo
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function updateCustomFieldOnCard(string $cardID, array $customFieldInfo): array|string|null
	{
		$body["value"][$customFieldInfo["type"]] = $customFieldInfo["value"];
		$body = json_encode($body);

		return $this->getContent(
			$this->createPUTRequest("cards/$cardID/customField/{$customFieldInfo["id"]}/item", [
				"query" => [
					"key" => static::API_KEY,
					"token" => static::API_TOKEN
				],
				"body" => $body
			])
		);
	}

	/**
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getCustomFieldsOnBoard(): array|string|null
	{
		$boardId = static::PROJECT_ID;
		return $this->getContent(
			$this->createGETRequest("boards/$boardId/customFields", [
				"query" => [
					"key" => static::API_KEY,
					"token" => static::API_TOKEN
				],
			])
		);
	}

	/**
	 * @param string $memberId
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getMember(string $memberId): array|string|null
	{
		return $this->getContent(
			$this->createGETRequest("/members/$memberId", [
				"query" => [
					"key" => static::API_KEY,
					"token" => static::API_TOKEN
				]
			])
		);
	}

}