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
			
			print <<<END
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>CalAcademy Stream Monitor</title>
		<meta charset="UTF-8"/>

		<link rel="shortcut icon" href="">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, maximum-scale=1.0">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

		<script>

			var _onHLSData = function (data) {
				for (var id in data) {
					var hls = data[id];
					var td = $('#' + id).find('td').last();
					
					td.css('word-break', 'break-all');
					td.html(hls);
				}
			}

			$(document).ready(function () {
				// get YouTube ids
				var ids = [];

				$('.youtube').each(function () {
					var tr = $(this).closest('tr');

					if (tr.attr('id')) {
						ids.push($.trim(tr.attr('id')));
						tr.append('<td>-</td>');
					}
				});

				if (ids.length == 0) return;

				// append header cell
				$('tr').eq(0).append('<th>HLS Manifest</th>');

				// get HLS data
				$.getJSON('ajax/', {
					ids: ids.join(',')
				}, _onHLSData);
			});

		</script>
	</head>

	<body>
		
		{$table['html']}

	</body>
</html>

END;


		} else {
			echo $foo->getStreams();	
		}
	}
	
?>
