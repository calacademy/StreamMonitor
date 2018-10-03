<?php

	require_once dirname(__FILE__) . '/../../classes/Webcam.php';
	require_once dirname(__FILE__) . '/../../classes/Monitor.php';

	$monitor = new Monitor();
	$streams = $monitor->getStreams(true);

	foreach ($streams as $stream) {
		if ($stream['facebook'] != 1) continue;
		
		$facebook_id = $stream['stream'];

		if ($monitor->isFacebookVideoLive($facebook_id, true)) {
			$monitor->pulseFacebook($facebook_id);
		}
	}

?>

facebook
