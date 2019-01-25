<?php

date_default_timezone_set('America/Los_Angeles');

$includePath = dirname(__FILE__) . '/../../../include/php/';
require_once($includePath . 'DatabaseUtil.php');
require_once($includePath . 'StringUtil.php');
require_once($includePath . 'phpmailer/class.phpmailer.php');

class Webcam {
	protected $db;
	protected $programmer_email = 'developers@calacademy.org';
	protected $debug = false;

	public function Webcam () {
		$db = new DatabaseUtil('webcam');
		$this->db = $db->getConnection();
	}

	protected function getDBResource ($query) {
		if (!$this->db) return false;
		$resource = mysql_query($query, $this->db);

		if (!$resource) {
			//db error
			if ($this->debug) {
				//display debugging info
				die($query . '<br>' . mysql_error());
			} else {
				//collect debugging info for programmer email
				ob_start();
				echo $query . "\n\n";
				echo mysql_error() . "\n\n";
				print_r($_REQUEST);
				print_r($_SERVER);
				$str = ob_get_contents();
				ob_end_clean();

				//send programmer email
				$mail = new PHPMailer();
				$mail->From = 'www@calacademy.org';
				$mail->FromName = 'California Academy of Sciences';
				$mail->Subject = 'Database Error';
				$mail->AddAddress($this->programmer_email);
				$mail->Body = $str;
				$mail->Send();

				return false;
			}
		} else {
			return $resource;
		}
	}

	public function getYouTubeId ($stream) {
		$query = "SELECT
					stream
				  FROM
					stream_monitor
				  WHERE
				  	title = '$stream'
				  	AND
				  	active = 1";

		$resource = $this->getDBResource($query);
		$row = mysql_fetch_assoc($resource);

		if ($row == false) return false;
		if (!isset($row['stream']) || empty($row['stream'])) return false;
		return $row['stream'];
	}
}

?>
