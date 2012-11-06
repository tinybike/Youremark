<?php

// Connect to MySQL
function makeDBConnection() {
	
	// If three arguments received, then connect to MySQL
	// but do not select DB
	if (func_num_args() == 3) {
		list($db_host, $db_user, $db_pass) = func_get_args();
	}
	
	// Otherwise, connect to specified MySQL process & DB
	else {
		list($db_host, $db_user, $db_pass, $db_name) = func_get_args();
	}

	$connection = mysql_connect($db_host, $db_user, $db_pass);
	if (!$connection) {
		exit("Error: can't connect to database!");
	}
	if (isset($db_name)) {
		if (!mysql_select_db($db_name, $connection)) {
			exit("Error: can't select database!");
		}
	}
	return $connection;
}

function dbSafe($value) {
	return '"' . mysql_real_escape_string($value) . '"';
}

// Convert SQL datetime to a more readable format
function processDateTime($sqldatetime, $mm_format) {
	$months = array('01' => 'January', '02' => 'February', '03' => 'March', 
					'04' => 'April', '05' => 'May', '06' => 'June',
					'07' => 'July', '08' => 'August', '09' => 'September',
					'10' => 'October', '11' => 'November', '12' => 'December');
	list($yyyy, $mm, $dd_time) = explode('-', $sqldatetime);
	list($dd, $time) = explode(' ', $dd_time);
	$dd = strval(intval($dd + 0));
	$mm = strval(intval($mm + 0));
	if ($mm_format == 1) {
		$mm = $months[$mm];
	}
	list($hour, $min, $sec) = explode(':', $time);
	$ampm = 'AM';
	if (intval($hour) > 12) {
		$hour = strval(intval($hour) - 12);
		$ampm = 'PM';
	}
	return array($yyyy, $mm, $dd, $hour, $min, $sec, $ampm);
}
?>