<?php

namespace App\Api\YouTrack;

use App\Api\MainApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use \Symfony\Contracts\HttpClient\Exception\{
	ClientExceptionInterface,
	RedirectionExceptionInterface,
	ServerExceptionInterface,
	TransportExceptionInterface,
	DecodingExceptionInterface
};

class YouTrackApi extends MainApiClient
{
	/** @var string Api Service Url */
	public const URL = "https://jester.youtrack.cloud/api";

	/** @var string YouTrack Token */
	private const API_TOKEN = "perm:cm9vdA==.NDctMQ==.mb7KLj3RElZQGSQWRGaAL4uN1nuQxE";

	/** @var string Root user id */
	protected const ROOT_USER_ID = "1-1";

	/** @var string Main project id */
	private const DEMO_PROJECT_ID = "0-1";

	/** @var string "Trello Task" field name */
	public const TRELLO_TASK_FIELD_NAME = "Trello Task";

	/** @var string "Estimate" field name */
	public const ESTIMATE_FIELD_NAME = "Оценка";

	/** @var string "Elapsed time" field name */
	public const ELAPSED_TIME_FIELD_NAME = "Затраченное время";

	public function __construct(HttpClientInterface $client, string $url, array $headers = [])
	{
		parent::__construct($client, $url, $headers);
		$this->addHeaders([
			"Authorization" => "Bearer " . static::API_TOKEN
		]);
	}

	/**
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getListOfUsers(): array|string|null
	{
		return $this->getContent($this->createGETRequest("users", [
			"query" => [
				"fields" => "id,login,fullName,email,banned"
			]
		]));
	}

	/**
	 * @param array $fields
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getProjectsList(array $fields = ["id"]): array|string|null
	{
		return $this->getContent(
			$this->createGETRequest("/admin/projects/", [
				"query" => [
					"fields" => $fields
				]
			])
		);
	}

	/**
	 * @param int $projectId
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getProject(int $projectId): array|string|null
	{
		return $this->getContent(
			$this->createGETRequest("/admin/projects/$projectId/", [
				"query" => [
					"fields" => "id,login,fullName,email"
				]
			])
		);
	}

	/**
	 * Method for getting project issues
	 *
	 * @param string $projectId
	 * @param array $fields
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getProjectIssues(string $fields = "id"): array|string|null
	{
		$projectId = static::DEMO_PROJECT_ID;
		return $this->getContent(
			$this->createGETRequest("admin/projects/$projectId/issues/", [
				"query" => [
					"fields" => $fields
				]
			])
		);
	}

	/**
	 * Method for getting a project custom fields
	 *
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getProjectCustomFields(): array|string|null
	{
		$projectId = static::DEMO_PROJECT_ID;
		return $this->getContent(
			$this->createGETRequest("/admin/projects/$projectId/customFields/", [
				"query" => [
					"fields" => "id,name,aliases"
				]
			])
		);
	}

	/**
	 * Method for getting issue information
	 *
	 * @param string $projectId
	 * @param string $issueId
	 * @param string $fields
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getIssue(string $projectId, string $issueId, string $fields = "id"): array|string|null
	{
		return $this->getContent(
			$this->createGETRequest("/admin/projects/$projectId/issues/$issueId", [
				"query" => [
					"fields" => $fields
				]
			])
		);
	}

	/**
	 * Method for adding a new issue in project
	 *
	 * @param array $newIssue
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function addIssueInProject(array $newIssue): array|string|null
	{
		$projectId = static::DEMO_PROJECT_ID;
		$body = json_encode([
			"project" => [
				"id" => $projectId
			],
			"summary" => $newIssue["summary"],
			"description" => $newIssue["description"],
			"customFields" => [
				[
					"name" => "Trello Task",
					"\$type" => "SimpleIssueCustomField",
					"value" => $newIssue["url"]
				],
				[
					"name" => "Assignee",
					"\$type" => "SingleUserIssueCustomField",
					"value" => [
						"login" => $newIssue["assignee"]
					]
				]
			],
		]);

		return $this->getContent(
			$this->createPOSTRequest("issues", [
				"query" => [
					"fields" => "id,summary,idReadable"
				],
				"body" => $body
			])
		);
	}

	/**
	 * Method for adding a new project
	 *
	 * @param array $projectData
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function addProject(array $projectData): array|string|null
	{
		return $this->getContent(
			$this->createPOSTRequest("/admin/projects", [
				"query" => [
					"id",
					"shortName",
					"name",
					"leader(id,login,name)"
				],
				"body" => [
					"description" => $projectData["description"],
					"name" => $projectData["name"],
					"shortName" => $projectData["shortName"],
					"leader" => [
						"id" => static::ROOT_USER_ID
					]
				]
			])
		);
	}

	/**
	 * Method for adding a new custom field
	 *
	 * @param array $customFieldData
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function addCustomField(array $customFieldData): array|string|null
	{
		return $this->getContent(
			$this->createPOSTRequest("/admin/projects", [
				"query" => [
					"id",
					"name",
					"fieldType(presentation,id)"
				],
				"body" => [
					"fieldType" => [
						"id" => $customFieldData["fieldType"]["id"]
					],
					"name" => $customFieldData["name"],
					"isDisplayedInIssueList" => $customFieldData["isDisplayedInIssueList"],
					"isAutoAttached" => $customFieldData["isAutoAttached"],
					"isPublic" => $customFieldData["isPublic"],
				]
			])
		);
	}

	/**
	 * Method for adding a new custom field in project
	 *
	 * @param string $projectId
	 * @param array $projectCustomFieldData
	 * @return array|string|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function addProjectCustomField(string $projectId, array $projectCustomFieldData): array|string|null
	{
		return $this->getContent(
			$this->createPOSTRequest("/admin/projects/$projectId/customFields", [
				"query" => [
					"field(fieldType(valueType,id),name,id)",
				],
				"body" => [
					"field" => [
						"name" => $projectCustomFieldData["field"]["name"],
						"id" => $projectCustomFieldData["field"]["id"],
						"\$type" => $projectCustomFieldData["field"]["type"],
					],
					"\$type" => $projectCustomFieldData["type"],
				]
			])
		);
	}
}