<?php include "dbfun.php"; ?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" media="all" href="css/comments.css" />
</head>
<body>

<?php

echo '<h3>Comments setup</h3>';

// If there are any required fields not filled out, send the user back to the form 
$filled = TRUE; 
$required = array('db_host', 'db_user', 'db_pass', 'db_name');
foreach ($required as &$value) {
	if ($_POST[$value] == '') {
		$filled = FALSE;
	}
}
if (!$filled) {
	echo '<p>Error: required fields not filled out.</p>';
	echo '<p><a href="createdb.html">Try again.</a></p>';
	exit();
}

// Otherwise, assign DB variables from POST
$db_host = $_POST['db_host'];
$db_user = $_POST['db_user'];
$db_pass = $_POST['db_pass'];
$db_name = $_POST['db_name'];

// Connect to MySQL
$connection = makeDBConnection($db_host, $db_user, $db_pass);
if (!$connection) {
	exit("Error: can't connect to database " . $db_name . ".");
}

// If comments database does not exist, then create it
if (!mysql_select_db($db_name, $connection)) {
	$query = 'CREATE DATABASE ' . $db_name . ';';
	$result = mysql_query($query, $connection);
	if (!$result) {
		exit("Error: can't create database.");
	}
	echo '<p>Database ' . $db_name . ' created.</p>';
	mysql_select_db($db_name, $connection);
	echo '<p>Database ' . $db_name . ' selected.</p>';
}
else {
	echo '<p>Database selected.</p>';
}

// Create comments table
// Edit this query if you want to re-define your columns!
$query = 'CREATE TABLE comments (
		commentid INT NOT NULL AUTO_INCREMENT,
		eventid INT,
		userid VARCHAR(25),
		replyto INT,
		depth INT,
		commentdate DATETIME,
		body TEXT,
		PRIMARY KEY(commentid));';
if (!mysql_query($query, $connection)) {
	exit("Error: can't create comments table.");
}
echo '<p>Comments table created successfully in ' . $db_name . '.</p>';
		
mysql_close($connection);

?>
			
</body>
</html>