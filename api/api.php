<?php
$correct_order = array('triangle', 'circle', 'rectangle');
$counter_file = '/home/qa/counter.txt';
$solution_store = '/home/qa/solutions.txt';
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

function save_solution($res) {
	global $solution_store;
	array_unshift($res, $_SESSION['QA_Count']);
	$q = msg_get_queue(42);
	if (!msg_send($q, 12, json_encode($res), false)) {
		die('unable to save to MSG queue');
	}
}

function check_solution() {
	global $correct_order;
	if (!isset($_SESSION['QA_Count'])) {
		return;
	}
	if ($_SESSION['QA_Count'] != $_COOKIE['QA_Count']) {
		echo("Invalid session!");
		return;
	}
	# Get the JSON data
	$result = json_decode($_REQUEST['result']);
	if ($result === NULL) {
		echo("Invalid JSON provided");
		return;
	}

	# Check if the result is correct and add the smiles
	if (array_diff_assoc($result, $correct_order)) {
		echo("Correct solution");
		array_unshift($result, 'sad');
		array_push($result, 'sad');
	} else {
		echo("Invalid solution");
		array_unshift($result, 'smile');
		array_push($result, 'smile');
	}
	
#	# Send the figures to the board
#	trigger_table($result);
	save_solution($result);
}

function get_last_solution() {
	$q = msg_get_queue(42);
	$status=msg_stat_queue($q);
	if ($status['msg_qnum']>0) {
		msg_receive($q,12,$msgtype,200,$data,false,null,$err);
		echo($data);
	}
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
	setcookie('QA_Count', $count, time()+600, '/api.php', 'qa.siteground.com', 0);
	$_SESSION['QA_Count'] =  $count;
	
}

session_start();
if (isset($_REQUEST['result'])) {
	check_solution();
} elseif (isset($_REQUEST['get'])) {
	echo("get");
	get_last_solution();
} else {
	# generate cookie and solution ID
	prepare_request();
}


?>
