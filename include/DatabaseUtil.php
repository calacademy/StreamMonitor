<?php

	class DatabaseUtil {
		var $db;
		
		function DatabaseUtil ($dbStr, $remoteServer = '') {
			if (empty($dbStr)) die('Unspecified database');
			
			require('/private/globalVars.php');
			
			if (!empty($remoteServer)) $dbserver = $remoteServer;
			$this->db = mysql_connect($dbserver, $dbuser, $dbpass);
			mysql_select_db($dbStr, $this->db) or die("The server encountered an error connecting to $dbStr");
		}
		
		function getConnection () {
			return $this->db;
		}
	}

?>
