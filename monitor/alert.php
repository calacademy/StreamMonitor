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
	$table = $foo->getTable(5500);

	if ($table['isStale'] && count($table['recipients']) > 0) {
		$mail = new PHPMailer();
		$mail->From = 'no-reply@calacademy.org';
		$mail->FromName = 'CalAcademy Webcam Alert';
		$mail->Subject = 'Webcam Stream Outage!';

		foreach ($table['recipients'] as $recipient) {
			$mail->AddAddress($recipient);
		}

		$mail->Body = '<p style="font-size: 30px; font-weight: bold; font-family: sans-serif;">One or more streams are down!</p>' . $table['html'];
		$mail->IsHTML(true);
		$mail->Send();

		echo ':(';
	} else {
		echo ':)';
	}
	
?>
