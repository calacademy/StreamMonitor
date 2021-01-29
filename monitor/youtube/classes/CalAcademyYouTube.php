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

  public function isValidHLS ($id, $debug = false, $returnOutput = false) {
  	if (!$this->_config) return false;

  	$url = $this->_config['endpoint'] . '=' . $id;
  	$str = @file_get_contents($url);
  	if (!$str) return false;

    if ($returnOutput) return $str;

  	parse_str($str, $output);
  	
    if (is_string($output[$this->_config['key']])) {
      if ($debug) echo $output[$this->_config['key']] . '<hr />';
      return true;
    } else {
      return false;
    }
  }

  public function getHLS ($id) {
    $output = $this->isValidHLS($id, false, true);
    if ($output === false) $output = 'false';
    
    return $output;
  }

  public function getLiveStreams ($items = array(), $pageToken = '') {
    $params = array(
      'mine' => true,
      'maxResults' => 50
    );

    if (!empty($pageToken)) {
      $params['pageToken'] = $pageToken;
    }

    $arr = (array) $this->_service->liveStreams->listLiveStreams(
      'snippet,status',
      $params
    );

    // concatenate
    $items = array_merge($items, $arr["\0*\0modelData"]['items']);

    // done with pagination
    if (empty($arr['nextPageToken'])) return $items;

    // get next page
    return $this->getLiveStreams($items, $arr['nextPageToken']);
  }

  public function getLiveBroadcasts ($ids, $items = array(), $pageToken = '') {
    $params = array(
      // 'id' => $ids,
      'broadcastStatus' => 'active',
      'maxResults' => 50
    );
    
    if (!empty($pageToken)) {
      $params['pageToken'] = $pageToken;
    }

    try {
      $arr = (array) $this->_service->liveBroadcasts->listLiveBroadcasts(
        'id,snippet,contentDetails,status',
        $params
      );
    } catch (Google_Service_Exception $e) {
      echo $e->getMessage();
    } catch (Google_Exception $e) {
      echo $e->getMessage();
    }

    // concatenate
    $items = array_merge($items, $arr["\0*\0modelData"]['items']);

    // done with pagination
    if (empty($arr['nextPageToken'])) return $items;

    // get next page
    return $this->getLiveBroadcasts($ids, $items, $arr['nextPageToken']);
  }
}

?>
