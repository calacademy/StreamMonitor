<?php
    
	function __autoload ($class) {
		$dir = '../../../classes/';
		require_once($dir . $class . '.php');
	}
    
	$foo = new CamControlKill();
	$data = $foo->getResponse();
	$encoder = new EncodeResponse();
	
	header('Content-type: application/json');
	echo $encoder->getEncodedData($data);

?>