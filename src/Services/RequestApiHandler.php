<?php

namespace App\Services;

use Symfony\Component\HttpClient\HttpClient;

use App\Api\{
	MainApiClient,
	Trello\TrelloApi,
	YouTrack\YouTrackApi
};

class RequestApiHandler
{

	private YouTrackApi $youTrackApi;

	public function __construct()
	{

	}

	/**
	 * @return void
	 */


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

	public function getTrelloTask()
	{


	}
}