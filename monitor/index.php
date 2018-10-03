<?php
	
	function __autoload ($class) {
		$dir = dirname(__FILE__) . '/../classes/';
		require($dir . $class . '.php');
	}
	
	// prevent caching
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');

	$foo = new Monitor();
	
	if (isset($_REQUEST['stream'])) {
		$foo->pulse($_REQUEST['server'], $_REQUEST['stream'], $_REQUEST['level']);
		echo '1';
	} else {
		if (intval($_REQUEST['view']) == 1) {
			$table = $foo->getTable();
			echo $table['html'];
		} else {
			echo $foo->getStreams();	
		}
	}
	
?>
