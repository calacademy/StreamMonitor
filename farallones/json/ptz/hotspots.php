<?php

	function __autoload ($class) {
		$dir = '../../../classes/';
		require_once($dir . $class . '.php');
	}
	
	require('/private/globalVars.php');

	$ptz = new AxisCamControl('http://208.70.28.196:8080/axis-cgi/com/ptz.cgi', $farallones_cam_username, $farallones_cam_password);

	$response = $ptz->connect(array(
		'query' => 'presetposall'
	));

	print_r($response);

?>
