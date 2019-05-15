<?php

	function __autoload ($class) {
		$dir = '../../../classes/';
		require_once($dir . $class . '.php');
	}
	
	$response = false;
	$control = new CamControl();
	$clientInfo = $control->getClientInfo();

	$arr = array();

	if ($control->isActive($clientInfo) && !$control->isAdminDisabled) {
		// attempt to control the camera if currently active and not disabled
		require('/private/globalVars.php');	
		$ptz = new AxisCamControl('http://208.70.28.196:8080/axis-cgi/com/ptz.cgi', $farallones_cam_username, $farallones_cam_password);

		$response = $ptz->connect(array(
			$_REQUEST['action'] => $_REQUEST['arguments'],
			'query' => 'position'
		));
	}
	
	$arr['success'] = ($response === false) ? 0 : 1;
	
	header('Content-type: application/json');
	$encoder = new EncodeResponse();
	echo $encoder->getEncodedData($arr);

?>
