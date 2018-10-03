<?php

class StringUtil {
	public static function getRandomizedString ($length = 10, $repeatOK = false) {
		$password = "";

		$possible = "23456789abcdefghjkmnpqrstuvwxyz";

		$i = 0;

		while($i < $length) { 
			$char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
			
			if ($repeatOK) {
				$password .= $char;
				$i++;
				continue;
			}
			
			if (!strstr($password, $char)) { 
				$password .= $char;
				$i++;
			}
		}

		return $password;
	}
	
	public static function getCleanArray ($arr, $skipkey = '') {
		foreach ($arr as $key => $val) {
			if (!empty($skipkey) && $key == $skipkey) continue;
			
			if (get_magic_quotes_gpc()) $val = stripslashes($val);
			$arr[$key] = mysql_real_escape_string(trim($val));
		}	
		
		return $arr;
	}
	
	public static function getHTMLArray ($arr, $skipkey = '', $request = true) {
		foreach ($arr as $key => $val) {
			if (!is_string($val)) continue;
			if (!empty($skipkey) && $key == $skipkey) continue;
			
			if ($request) {
				if (get_magic_quotes_gpc()) $val = stripslashes($val);
			}
			
			$arr[$key] = htmlentities($val);
			$arr[$key] = str_replace("'", "&rsquo;", $arr[$key]);
		}

		return $arr;
	}
	
	public static function getCleanString ($input, $skipcheck = '') {
		$str = '';

		$i = 0;

		while ($i < strlen($input)) {
			$char = substr($input, $i, 1);
			
			if (is_array($skipcheck)) {
				if (in_array($char, $skipcheck)) {
					$str .= $char;
					$i++;
					continue;
				}
			}
			
			if (ereg('[A-Za-z0-9\-]', $char)) {
				$str .= $char;
			}

			$i++;
		}

		return $str;
	}
	
	public static function getCleanFolderTitle ($str) {
		$str = trim($str);
		$str = strtolower($str);
		$str = stripslashes($str);
		$str = ereg_replace("[[:space:]]+", "-", $str);
		$str = ereg_replace("[^a-z0-9\-]", "", $str);

		return $str;
	}
	
	public static function getCleanFileName ($str) {
		$str = trim($str);
		$str = strtolower($str);
		$str = ereg_replace("[[:space:]]+", "-", $str);
		$str = ereg_replace("[^a-z0-9\.\-\_]", "", $str);

		return $str;
	}
	
	public static function getOrdinalSuffix ($num) {
		if ($num >= 10 && $num <= 20) return 'th';

		switch ($num % 10) {
			case 0 :
			case 4 :
			case 5 :
			case 6 :
			case 7 :
			case 8 :
			case 9 :
				return 'th';
			case 3 :
				return 'rd';
			case 2 :
				return 'nd';
			case 1 :
				return 'st';
		}
	}
	
	public static function encryptData ($source) {
		//Character limit of 117
		$fp = fopen('/private/server.crt', 'r');
		$pub_key = fread($fp, 8192);
		fclose($fp);

		openssl_get_publickey($pub_key);
		openssl_public_encrypt($source, $crypttext, $pub_key);

		return base64_encode($crypttext);
	}

	public static function decryptData ($source) {
		$fp = fopen('/private/server.key', 'r');
		$priv_key = fread($fp, 8192);
		fclose($fp);

		$res = openssl_get_privatekey($priv_key, $passphrase);
		$decoded_source = base64_decode($source);
		openssl_private_decrypt($decoded_source, $newsource, $res);

		return $newsource;
	}
	
	public static function isExpired ($str) {
		//ARG MUST BE IN FORMAT "20090520 15:05" (May 20th, 2009 3:05pm)
		$now = intval(time());
		$expires = intval(strtotime($str));

		return ($now > $expires);
	}
}	

?>
