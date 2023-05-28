<?php

namespace App\Services;

use App\Api\MainApiClient;
use App\Api\YouTrack\YouTrackApi;
use Symfony\Component\HttpClient\HttpClient;

use \Symfony\Contracts\HttpClient\Exception\{
	ClientExceptionInterface,
	RedirectionExceptionInterface,
	ServerExceptionInterface,
	TransportExceptionInterface,
	DecodingExceptionInterface,
};


class YouTrackApiHandler
{
	private YouTrackApi $youTrackApi;

	public function __construct()
	{
		$this->createYouTrackApi();
	}

	/**
	 * @return void
	 */
	public function createYouTrackApi(): void
	{
		$this->youTrackApi = new YouTrackApi(
			HttpClient::create(),
			YouTrackApi::URL,
			MainApiClient::DEFAULT_HEADERS
		);
	}

	/**
	 * @return YouTrackApi
	 */
	public function getYouTrackApi(): YouTrackApi
	{
		return $this->youTrackApi;
	}

	/**
	 * @param array $newIssues
	 * @return array|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function addNewIssuesInProject(array $newIssues): ?array
	{
		$youTrackApi = $this->getYouTrackApi();
		$trelloTasksInYouTrack = $this->getProjectIssuesWithTrelloTaskUrl($youTrackApi);

		$newAddedIssues = [];
		foreach ($newIssues as $newIssue) {

			if (!in_array($newIssue["url"], $trelloTasksInYouTrack)) {
				$newAddedIssues[] = $youTrackApi->addIssueInProject($newIssue);
			}
		}

		return $newAddedIssues;
	}

	/**
	 * @param YouTrackApi|null $youTrackApi
	 * @return array
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getProjectIssuesWithTrelloTaskUrl(YouTrackApi $youTrackApi = null): array
	{
		$youTrackApi = $youTrackApi ?? $this->getYouTrackApi();

		$trelloTasksInYouTrack = [];
		$issues = $youTrackApi->getProjectIssues("id,summary,customFields(id,name,value(login,name,presentation))");
		if (!is_string($issues)) {
			foreach ($issues as $issue) {
				$trelloTasksInYouTrack[$issue["id"]] = $this->getTrelloTaskUrl($issue);
			}
		}

		return $trelloTasksInYouTrack;
	}

	public function getTrelloTaskUrl(array $issue) {
		$trelloTaskInYouTrack = "";
		foreach ($issue["customFields"] as $customField) {
			if (!strcmp($customField["name"], YouTrackApi::TRELLO_TASK_FIELD_NAME)) {
				$trelloTaskInYouTrack = $customField["value"];
			}
		}

		return $trelloTaskInYouTrack;
	}

	/**
	 * @return array|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getProjectIssuesWithEstimateNFact(): ?array
	{
		$youTrackApi = $this->getYouTrackApi();
		$issues = $youTrackApi->getProjectIssues("id,summary,customFields(id,name,value(login,name,presentation))");

		$issuesTimeInfo = [];
		foreach ($issues as $issue) {
			$issuesTimeInfo[$issue["id"]]["trelloTask"] = $this->getTrelloTaskUrl($issue);
			$issuesTimeInfo[$issue["id"]] += $this->getEstimatesNFact($issue);
		}

		return $issuesTimeInfo;
	}

	/**
	 * @param array $issue
	 * @return array|null
	 */
	public function getEstimatesNFact(array $issue): ?array
	{
		$estimateNFact = [];
		foreach ($issue["customFields"] as $index => $customField) {
			if ($customField["\$type"] === "PeriodIssueCustomField") {
				switch ($customField["name"]) {
					case YouTrackApi::ESTIMATE_FIELD_NAME:
						$estimateNFact["estimate"] = !empty($customField["value"]) ? $customField["value"]["presentation"] : "";
						break;
					case YouTrackApi::ELAPSED_TIME_FIELD_NAME:
						$estimateNFact["fact"] = !empty($customField["value"]) ? $customField["value"]["presentation"] : "";
						break;
				}
			}
		}

		return $estimateNFact;
	}
}