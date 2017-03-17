<?php
$correct_order = array('triangle', 'circle', 'rectangle');
$counter_file = '/home/qa/counter.txt';
$srv_sock = 'unix:///tmp/table.sock';

# Send the result to the LED matrix
function trigger_table($res) {
	global $srv_sock;
	$sock = stream_socket_client($srv_sock, $errno, $errstr, 30);
	if (!$sock) {
		echo("$errstr ($errno)<br />\n");
	} else {
		fwrite($sock, json_encode($res));
		fclose($sock);
	}
}

function check_solution() {
	global $correct_order;
	# Get the JSON data
	$result = json_decode($_REQUEST['result']);
	if ($result === NULL) {
		echo("Invalid JSON provided");
		return;
	}

	# Check if the result is correct and add the smiles
	if (array_diff_assoc($result, $correct_order)) {
		array_unshift($result, 'sad');
		array_push($result, 'sad');
	} else {
		array_unshift($result, 'smile');
		array_push($result, 'smile');
	}
	
	# Send the figures to the board
	trigger_table($result);
}

function check_cookie() {
	# check the IP
	
}

function prepare_request() {
	$count = 0;
	global $counter_file;
	$f = fopen($counter_file, 'r+');
	if (!$f) {
		return;
	}
	# Do not allow concurrent writes to the file
	flock($f, LOCK_EX);
	$fsize = filesize($counter_file);
	if ($fsize == 0) {
		$fsize = 1;
	}
	$count = fread($f, $fsize);
	$count++;
	fseek($f, 0);
	fwrite($f, $count);
	fclose($f);
	# set cookie with the counter, expire in 10min
	setcookie("QA_Count", $count, time()+600, "/api.php", "qa.siteground.com", 0);

	# seve in the session its IP and counter
	
}

if (isset($_REQUEST['order'])) {
	check_solution();
} else {
	# generate cookie and solution ID
	prepare_request();
}


?>
