<?php

	require_once dirname(__FILE__) . '/../../classes/Webcam.php';
	require_once dirname(__FILE__) . '/../../classes/Monitor.php';
	require_once dirname(__FILE__) . '/../youtube/classes/CalAcademyYouTube.php';

	$youtube = new CalAcademyYouTube();
	$ids = trim($_REQUEST['ids']);
	$ids = explode(',', $ids);
	$data = array();

	foreach ($ids as $id) {
		$data[$id] = $youtube->getHLS($id);
	}

	header('Content-type: application/json');
	echo json_encode($data);
	die;

?>
