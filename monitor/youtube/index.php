<?php

	require_once dirname(__FILE__) . '/../../classes/Webcam.php';
	require_once dirname(__FILE__) . '/../../classes/Monitor.php';
	require_once 'classes/CalAcademyYouTube.php';

	$youtube = new CalAcademyYouTube();
	$streams = $youtube->getLiveStreams();
	$streams = $streams["\0*\0modelData"]['items'];

	if (!empty($streams)) {
		$monitor = new Monitor();
		$myStreams = $monitor->getStreams(true);

		// create lookups for stream names / youtube ids
		$streamLookup = array();
		$streamStatusLookup = array();

		foreach ($myStreams as $myStream) {
			if ($myStream['youtube'] == 1) {
				$streamLookup[$myStream['title']] = $myStream['stream'];
				$streamStatusLookup[$myStream['stream']] = $myStream['privacy_status'];
			}
		}

		$broadcasts = $youtube->getLiveBroadcasts(implode(',', $streamLookup));
		$broadcasts = $broadcasts["\0*\0modelData"]['items'];

		// create a lookup for youtube ids / broadcast status
		$broadcastStatus = array();

		foreach ($broadcasts as $broadcast) {
			$broadcastStatus[$broadcast['id']] = $broadcast['status'];
		}

		echo '<pre>';
		print_r($streamLookup);
		echo '</pre>';

		print '<hr />';

		echo '<pre>';
		print_r($streamStatusLookup);
		echo '</pre>';

		print '<hr />';

		echo '<pre>';
		print_r($broadcastStatus);
		echo '</pre>';

		print '<hr />';

		// iterate each stream and pulse those marked as 'active'
		foreach ($streams as $stream) {
			if (isset($stream['status']['streamStatus'])
				&& isset($stream['snippet']['title'])
				&& isset($streamLookup[$stream['snippet']['title']])) {

				// check client status
				$clientValid = false;
				$youTubeId = $streamLookup[$stream['snippet']['title']];

				if (is_array($broadcastStatus[$youTubeId])) {
					$status = $broadcastStatus[$youTubeId];

					// "public", "private" or "unlisted"
					if ($status['lifeCycleStatus'] == 'live'
						&& $status['privacyStatus'] == $streamStatusLookup[$youTubeId]) {
						$clientValid = true;
					}
				}

				// check stream status
				$streamValid = ($stream['status']['streamStatus'] == 'active');

				if ($clientValid && $streamValid) {
					// send pulse
					print 'pulse ' . $stream['snippet']['title'] . '<br />';

					if ($youtube->isValidHLS($youTubeId, true)) {
						$monitor->pulseYouTube($stream['snippet']['title']);
					} else {
						print 'HLS validation for <strong>' . $stream['snippet']['title'] . '</strong> failed!<br />';
					}
				} else {
					print '<strong>' . $stream['snippet']['title'] . ' is not valid</strong>';
					echo '<pre>';
					print_r($stream['status']);
					echo '</pre>';
				}
			}
		}
	}

?>
