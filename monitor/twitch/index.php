<?php

	require_once dirname(__FILE__) . '/../../classes/Webcam.php';
	require_once dirname(__FILE__) . '/../../classes/Monitor.php';

	$monitor = new Monitor();
	$streams = $monitor->getStreams(true);

	foreach ($streams as $stream) {
		if ($stream['twitch'] != 1) continue;
		
		$uid_stream = $stream['uid_stream'];

		if ($monitor->isTwitchVideoLive(true)) {
			$monitor->pulseById($uid_stream);
		}
	}

?>

twitch
