<?php

namespace App\Controller;

use App\Services\TrelloApiHandler;
use App\Services\YouTrackApiHandler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use App\Api\{
	MainApiClient,
	Trello\TrelloApi,
	YouTrack\YouTrackApi
};
use Symfony\Contracts\HttpClient\Exception\ {
	ClientExceptionInterface,
	DecodingExceptionInterface,
	RedirectionExceptionInterface,
	ServerExceptionInterface,
	TransportExceptionInterface
};

class ApiController
{
	/**
	 * @throws TransportExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws DecodingExceptionInterface
	 * @throws ClientExceptionInterface
	 */
	public function index(): Response
	{
		$trelloApiHandler = new TrelloApiHandler();
		$youTrackApiHandler = new YouTrackApiHandler();


		$trelloTasks = $trelloApiHandler->getNewTrelloTasksForYouTrack();
		$response = $youTrackApiHandler->addNewIssuesInProject($trelloTasks);

		$youTrackTasks = $youTrackApiHandler->getProjectIssuesWithEstimateNFact();
		$response += $trelloApiHandler->updateEstimateNFactOnCard($youTrackTasks);


		return new Response(json_encode($response));
	}
}