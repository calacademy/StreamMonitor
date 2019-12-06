<?php

	class Monitor extends Webcam {	
		protected $_twitchAttempts = 0;
		protected $_maxTwitchAttempts = 3;

		public function Monitor () {
			parent::__construct();
		}
		
		public function pulse ($server, $stream, $level) {
			$data = !isset($_SERVER['REMOTE_ADDR']) ? array('REMOTE_ADDR' => '-') : $_SERVER;
			$data['server'] = $server;
			$data['stream'] = $stream;
			$data['level'] = $level;
			
			$data = StringUtil::getCleanArray($data);

			$query = "UPDATE
						stream_monitor
					  SET
						ip = '{$data['REMOTE_ADDR']}',
						last_pulse = CURRENT_TIMESTAMP
					  WHERE
						server = '{$data['server']}'
						AND
						stream = '{$data['stream']}'
						AND
						level = '{$data['level']}'
						AND
						youtube = 0
						AND
						facebook = 0";
		
			$resource = $this->getDBResource($query);
		}
		
		public function pulseById ($uid_stream) {
			$data = !isset($_SERVER['REMOTE_ADDR']) ? array('REMOTE_ADDR' => '-') : $_SERVER;	
			$data = StringUtil::getCleanArray($data);
			$uid_stream = intval($uid_stream);

			$query = "UPDATE
						stream_monitor
					  SET
						ip = '{$data['REMOTE_ADDR']}',
						last_pulse = CURRENT_TIMESTAMP
					  WHERE
						uid_stream = {$uid_stream}";
		
			$resource = $this->getDBResource($query);
		}

		public function isTwitchVideoLive ($debug = false) {
			$ch = curl_init('https://legacy.calacademy.org/webcams/twitch/');
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			
			$obj = json_decode($result);
			
			if (!is_null($obj)) {
				if ($debug) {
					echo '<pre>';
					print_r($obj);
					echo '</pre>';
				}

				if (isset($obj->hls)) {
					if (strpos($obj->hls, 'http') === 0) {
						$this->_twitchAttempts = 0;
						return true;
					}
				}
			}
			
			$this->_twitchAttempts++;

			if ($this->_twitchAttempts < $this->_maxTwitchAttempts) {
				return $this->isTwitchVideoLive($debug);
			}

			$this->_twitchAttempts = 0;
			return false;
		}

		public function isFacebookVideoLive ($id, $debug = false) {
			include('/private/globalVars.php');

			$get = array(
				'access_token' => $facebook_api_token,
				'fields' => 'live_status'
			);
			
			$ch = curl_init('https://graph.facebook.com/v2.7/' . $id . '?' . http_build_query($get));
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			
			$obj = json_decode($result);
			if (is_null($obj)) return false;

			if ($debug) {
				echo '<pre>';
				print_r($obj);
				echo '</pre>';
			}

			return ($obj->live_status == 'LIVE');
		}

		public function pulseFacebook ($facebook_id) {
			$data = !isset($_SERVER['REMOTE_ADDR']) ? array('REMOTE_ADDR' => '-') : $_SERVER;
			$data['stream'] = $facebook_id;
			$data = StringUtil::getCleanArray($data);

			$query = "UPDATE
						stream_monitor
					  SET
						ip = '{$data['REMOTE_ADDR']}',
						last_pulse = CURRENT_TIMESTAMP
					  WHERE
						stream = '{$data['stream']}'
						AND
						facebook = 1";
		
			$resource = $this->getDBResource($query);	
		}

		public function pulseYouTube ($streamTitle) {
			$data = !isset($_SERVER['REMOTE_ADDR']) ? array('REMOTE_ADDR' => '-') : $_SERVER;
			$data['streamTitle'] = $streamTitle;
			$data = StringUtil::getCleanArray($data);

			$query = "UPDATE
						stream_monitor
					  SET
						ip = '{$data['REMOTE_ADDR']}',
						last_pulse = CURRENT_TIMESTAMP
					  WHERE
						title = '{$data['streamTitle']}'
						AND
						youtube = 1";
		
			$resource = $this->getDBResource($query);	
		}

		public function getFlashStreams ($data = false) {
			return $this->getStreams($data, true);
		}

		public function getStreams ($data = false, $flashOnly = false) {
			$query = "SELECT
						uid_stream,
						title,
						youtube,
						facebook,
						twitch,
						privacy_status,
						recipients,
						server,
						stream,
						level,
						bitrate,
						fcsubscribe,
						width,
						height,
						last_pulse
					FROM
						stream_monitor
					WHERE
						active = 1";

			if ($flashOnly) {
				$query .= ' AND youtube = 0';
			}

			$resource = $this->getDBResource($query);

			$arr = array(
				'data' => array(),
				'string' => array()
			);

			while ($row = mysql_fetch_assoc($resource)) {
				$arr['data'][] = $row;
				$arr['string'][] = $row['server'] . $row['stream'] . $row['level'] . '|' . $row['last_pulse'];
			}

			if ($data) return $arr['data'];
			return implode("\n", $arr['string']);	
		}

		public function getTable ($staleTime = 3600) {
			$isStale = false;
			$html = '';
			$recipients = array();

			$now = time();
			$data = $this->getStreams(true);

			$html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; font-family: sans-serif;"><tbody><tr><th>Title</th><th>Stream</th><th>Notification Recipients</th><th>Last Pulse</th></tr>';

			foreach ($data as $row) {
				$bg = '#ffffff';
				$font = '#000000';
				$link = '#000000';
				$arr = explode(',', $row['recipients']);

				// check if stale
				if ($now - strtotime($row['last_pulse']) > $staleTime) {
					$bg = '#ff0000';
					$font = '#ffffff';
					$link = '#ffffff';
					$isStale = true;

					$recipients = array_merge($recipients, $arr);
				}

				$uri = $row['server'] . $row['stream'] . $row['level'];

				if ($row['youtube']) {
					$uri = "<a class='youtube' style='text-decoration: underline; color: {$link};' href='{$uri}?autoplay=1'>$uri</a>";
				} else {
					$uri = "<a style='text-decoration: underline; color: {$link};' href='{$uri}'>$uri</a>";
				}

				$recipientList = '<ul style="margin: 0px; padding-left: 2em; padding-right: 2em;">';

				foreach ($arr as $r) {
					$recipientList .= '<li>' . $r . '</li>';
				}

				$recipientList .= '<ul>';

				$html .= <<<end
				<tr id="{$row['stream']}" style="color: {$font}; background-color: {$bg};">
					<td>{$row['title']}</td>
					<td>{$uri}</td>
					<td>{$recipientList}</td>
					<td>{$row['last_pulse']}</td>
				</tr>
end;
			}

			$html .= '</tbody></table>';

			return array(
				'html' => $html,
				'recipients' => array_unique($recipients),
				'isStale' => $isStale
			);
		}
	}

?>
