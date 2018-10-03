<?php

$gPath = dirname(__FILE__) . '/../';
set_include_path(get_include_path() . PATH_SEPARATOR . $gPath);

require_once 'Google/Client.php';
require_once 'Google/Service/YouTube.php';

class CalAcademyYouTube {
  private $_client;
  private $_service;

  public function __construct () {
  	require('/private/globalVars.php');

	$this->_client = new Google_Client();
	$this->_client->setClientId($youTubeCredentials['client_id']);
	$this->_client->setClientSecret($youTubeCredentials['client_secret']);
	$this->_client->setScopes('https://www.googleapis.com/auth/youtube.readonly');
	$this->_client->setAccessType('offline');
	$this->_client->refreshToken($youTubeCredentials['refresh_token']);

	$this->_service = new Google_Service_YouTube($this->_client);
  }

  public function getLiveStreams () {
	return (array) $this->_service->liveStreams->listLiveStreams(
		'snippet,status',
		array(
			'mine' => true,
			'maxResults' => 50
		)
	);
  }

  public function  getLiveBroadcasts ($ids) {
  	return (array) $this->_service->liveBroadcasts->listLiveBroadcasts(
		'id,snippet,contentDetails,status',
		array(
			'id' => $ids,
			'maxResults' => 50
		)
	);
  }
}

?>
