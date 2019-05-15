<?php
    
	function __autoload ($class) {
		$dir = '../../classes/';
		require_once($dir . $class . '.php');
	}

	$arr = array(
		'timestamp' => time()
	);
	
	require_once('../../../../include/php/DatabasePing.php');
	$dbPing = new DatabasePing();
	$isAlive = $dbPing->isAlive('webcam');
	
	if ($isAlive) {
		// cam control queue
		$camControl = new CamControl();
		$arr['camcontrol'] = $camControl->getResponse();

		// hotspots
		$foo = new FarallonesHotspots();
		$hotspots = $foo->getHotspotsData();
		$arr['hotspots'] = $hotspots;
	} else {
		$hotspots = '1';
		$arr['camcontrol'] = array();
		$arr['hotspots'] = array();
	}
	
	header('Content-type: application/json');
	$encoder = new EncodeResponse();
	echo $encoder->getEncodedData($arr); 

?>
