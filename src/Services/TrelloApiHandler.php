<?php

namespace App\Services;

use App\Api\MainApiClient;
use App\Api\Trello\TrelloApi;
use Symfony\Component\HttpClient\HttpClient;

use \Symfony\Contracts\HttpClient\Exception\{
	ClientExceptionInterface,
	RedirectionExceptionInterface,
	ServerExceptionInterface,
	TransportExceptionInterface,
	DecodingExceptionInterface,
};

class TrelloApiHandler
{
	private TrelloApi $trelloApi;

	public function __construct()
	{
		$this->createTrelloApi();
	}

	public function createTrelloApi(): void
	{
		$this->trelloApi = new TrelloApi(
			HttpClient::create(),
			TrelloApi::URL,
			MainApiClient::DEFAULT_HEADERS
		);
	}

	/**
	 * @return TrelloApi
	 */
	public function getTrelloApi(): TrelloApi
	{
		return $this->trelloApi;
	}

	/**
	 * @return array
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getNewTrelloTasksForYouTrack(): array
	{
		$trelloApi = $this->getTrelloApi();

		$taskList = [];
		$boardLists = $trelloApi->getBoardLists();
		foreach ($boardLists as $list) {
			if ($list["name"] === "New") {
				$cards = $trelloApi->getListCards($list["id"]);
				foreach ($cards as $card) {
					$memberLogin = "";
					if (!empty($card["idMembers"])) {
						$memberLogin = $this->getMemberLoginById($card["idMembers"][0]);
					}
					$taskList[$card["id"]] = [
						"id" => $card["id"],
						"summary" => $card["name"],
						"description" => $card["desc"],
						"url" => $card["url"],
						"assignee" => $memberLogin,
					];
				}
			}
		}

		return $taskList;
	}

	/**
	 * @return array
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getAllNotCompletedCards(): array
	{
		$trelloApi = $this->getTrelloApi();

		$taskList = [];
		$boardLists = $trelloApi->getBoardLists();
		foreach ($boardLists as $list) {
			if ($list["name"] !== "Complete") {
				$cards = $trelloApi->getListCards($list["id"]);
				foreach ($cards as $card) {
					$taskList[$card["id"]] = [
						"url" => $card["url"],
					];
				}
			}
		}

		return $taskList;
	}

	/**
	 * @param TrelloApi|null $trelloApi
	 * @return array
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getEstimateNFactFieldsInfo(TrelloApi $trelloApi = null): array
	{
		$trelloApi = $trelloApi ?? $this->getTrelloApi();

		$estimateNFactInfo = [];
		$customFields = $trelloApi->getCustomFieldsOnBoard();
		foreach ($customFields as $customField) {
			switch ($customField["name"]) {
				case TrelloApi::ESTIMATE_FIELD_NAME:
					$estimateNFactInfo["estimate"] = [
						"id" => $customField["id"],
						"type" => $customField["type"],
					];
					break;
				case TrelloApi::FACT_TIME_FIELD_NAME:
					$estimateNFactInfo["fact"] = [
						"id" => $customField["id"],
						"type" => $customField["type"],
					];
					break;
			}
		}

		return $estimateNFactInfo;
	}

	/**
	 * @param array $youTrackIssues
	 * @return array|null
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function updateEstimateNFactOnCard(array $youTrackIssues): ?array
	{
		$trelloApi = $this->getTrelloApi();
		$estimateNFactInfo = $this->getEstimateNFactFieldsInfo();
		$notCompletedTasks = $this->getAllNotCompletedCards();

		$response = [];
		foreach ($notCompletedTasks as $cardId => $notCompletedTask) {
			$dataForUpdate = [];
			foreach ($youTrackIssues as $youTrackIssue) {
				if ($notCompletedTask["url"] === $youTrackIssue["trelloTask"]) {
					$dataForUpdate["estimate"] = $estimateNFactInfo["estimate"];
					$dataForUpdate["estimate"]["value"] = $youTrackIssue["estimate"];

					$dataForUpdate["fact"] = $estimateNFactInfo["fact"];
					$dataForUpdate["fact"]["value"] = $youTrackIssue["fact"];
				}
			}

			$response[$cardId]["estimate"] = $trelloApi->updateCustomFieldOnCard($cardId, $dataForUpdate["estimate"]);
			$response[$cardId]["fact"] = $trelloApi->updateCustomFieldOnCard($cardId, $dataForUpdate["fact"]);
		}

		return $response;
	}

	/**
	 * @param string $memberId
	 * @return string
	 * @throws ClientExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function getMemberLoginById(string $memberId): string
	{
		$memberInfo = $this->getTrelloApi()->getMember($memberId);
		return $memberInfo["username"];
	}
}