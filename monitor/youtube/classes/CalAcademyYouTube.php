<?php

$gPath = dirname(__FILE__) . '/../';
set_include_path(get_include_path() . PATH_SEPARATOR . $gPath);

require_once 'Google/Client.php';
require_once 'Google/Service/YouTube.php';

class CalAcademyYouTube {
  private $_client;
  private $_service;
  private $_configUrl = 'https://s3.amazonaws.com/data.calacademy.org/penguins/data.json';
  private $_config = false;

  public function __construct () {
  	require('/private/globalVars.php');

	$this->_client = new Google_Client();
	$this->_client->setClientId($youTubeCredentials['client_id']);
	$this->_client->setClientSecret($youTubeCredentials['client_secret']);
	$this->_client->setScopes('https://www.googleapis.com/auth/youtube.readonly');
	$this->_client->setAccessType('offline');
	$this->_client->refreshToken($youTubeCredentials['refresh_token']);

	$this->_service = new Google_Service_YouTube($this->_client);
	$this->_setConfig();
  }

  private function _setConfig () {
  	$str = @file_get_contents($this->_configUrl);
  	if (!$str) return;

  	$json = json_decode($str, true);
  	if (is_null($json)) return;

  	if (empty($json['endpoint'])) return;
  	if (empty($json['key'])) return;

  	$this->_config = $json;
  }

  public function isValidHLS ($id, $debug = false) {
  	if (!$this->_config) return false;

  	$url = $this->_config['endpoint'] . '=' . $id;
  	$str = @file_get_contents($url);
  	if (!$str) return false;

  	parse_str($str, $output);
  	
    if (is_string($output[$this->_config['key']])) {
      if ($debug) echo $output[$this->_config['key']] . '<hr />';
      return true;
    } else {
      return false;
    }
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
