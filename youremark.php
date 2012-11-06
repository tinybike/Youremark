<!-- COPY AND PASTE THIS SECTION AT THE VERY TOP OF YOUR ARTICLE FILE -->
<?php
session_start();

// ENTER YOUR DATABASE INFORMATION HERE
// (These are the same values you entered into createdb.html.)
$db_host = '';	// Hostname (e.g., localhost)
$db_user = '';	// Username
$db_pass = '';	// Password
$db_name = '';	// Database name

include "dbfun.php";

// If the user has submitted a comment, add the eventid to the URL
if (isset($_POST['comment_text'])) {
	$id = $_POST['id'];
	$comment_text = $_POST['comment_text'];
	header('Location:' . $PHP_SELF . '?id=' . strval($id));	
}
else {
	$id = urldecode($_GET['id']);
}

$connection = makeDBConnection($db_host, $db_user, $db_pass, $db_name);

// If the user has submitted a comment, add it to the database
if (isset($comment_text)) {
	if (isset($_POST['replyto'])) {
		$query = 'INSERT INTO comments (eventid, userid, replyto, body, commentdate, depth) VALUES (' . 
			$id . ', ' . dbSafe($_SESSION['username']) . ', ' . dbSafe($_POST['replyto']) . ', ' .
			dbSafe($comment_text) . ', ' . dbSafe(date('Y-m-d H:i:s')) . ', ' . dbSafe($_POST['depth']) . ');';
	}
	else {
		$query = 'INSERT INTO comments (eventid, userid, body, commentdate, depth) VALUES (' . 
			$id . ', ' . dbSafe($_SESSION['username']) . ', ' .
			dbSafe($comment_text) . ', ' . dbSafe(date('Y-m-d H:i:s')) . ', ' . dbSafe($_POST['depth']) . ');';
	}
	mysql_query($query, $connection);
}
?>
<!--- END COPY-AND-PASTE -->

<!DOCTYPE html>
<html>
<head>
        
<link rel="stylesheet" type="text/css" media="all" href="css/comments.css" />
        
</head>
	
<body>

<!-- COPY AND PASTE THIS SECTION WHERE YOU WANT COMMENTS TO APPEAR -->			
<div id="comments">
<?php
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == TRUE) {
	echo '<div id="postcomment">';
	echo '<form action="' . $PHP_SELF . '" method="post" class="form">';
	echo '<p><textarea name="comment_text" ></textarea></p>';
	echo '<input type="hidden" name="id" value="' . strval($id) . '" />';
	echo '<input type="hidden" name="depth" value="0" />';
	echo '</div>';
	echo '<p><input class="button" type="submit" value="Post comment" /></p>';
	echo '</form>';
}
else {
	echo '<div class="pleasejoin"><p>Please create an account or login to post a comment!</p></div>';
}

function formatPostHTML($row, $id, $db_host, $db_user, $db_pass, $db_name) {
	// Process comment SQL datetime for display
	list($com_yyyy, $com_mm, $com_dd, $com_hour, $com_min, $com_sec, $com_ampm) = processDateTime($row['commentdate'], 0);
	
	// Create and display HTML
	if (isset($row['replyto'])) {
		if ($row['depth'] <= 4) {
			$depth = $row['depth'];
		}
		else {
			$depth = 4;
		}
		for ($i = 0; $i < $depth; $i++) {
			echo '<div class="commentindent">';	
		}
		echo '<div class="commentreply">';
	}
	else {
		echo '<div class="comment">';
	}
	echo '<a name="' . $row['commentid'] . '">';
	if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == TRUE) {
		echo '<p><b>' . $row['userid'] . '</b> &#183; <b>' . $com_mm . '/' . 
			$com_dd . '/' . $com_yyyy . '</b> at <b>' . $com_hour . ':' . $com_min . ' ' . 
			$com_ampm . '</b> &#183; <a href="' . $PHP_SELF . '?id=' . strval($id) . 
			'&comment=' . strval($row['commentid']) . '#' . 
			strval($row['commentid']) . '">Reply</a></p>';
	}
	else {
		echo '<p><b>' . $row['userid'] . '</b> &#183; <b>' . $com_mm . '/' . 
			$com_dd . '/' . $com_yyyy . '</b> at <b>' . $com_hour . ':' . $com_min . ' ' . 
			$com_ampm . '</b></p>';
	}
	echo '<p>' . $row['body'] . '</p>';
	echo '</a>';
	if (isset($_GET['comment']) && $_GET['comment'] == $row['commentid']) {
		echo '<div id="postreply">';
		echo '<form action="' . $PHP_SELF . '" method="post" class="form">';
		echo '<p><textarea name="comment_text"></textarea></p>';
		echo '<input type="hidden" name="id" value="' . strval($id) . '" />';
		echo '<input type="hidden" name="replyto" value="' . strval($row['commentid']) . '" />';
		echo '<input type="hidden" name="depth" value="' . strval($row['depth'] + 1) . '" />';
		echo '</div>';
		echo '<p><input class="button" type="submit" value="Post reply" /></p>';
		echo '</form>';
	}
	if (isset($row['replyto'])) {
		for ($i = 0; $i < $depth; $i++) {
			echo '</div>';	
		}
		echo '</div>';
	}
	else {
		echo '</div>';
	}

	// Create another connection and query for replies to this comment
	$inconnection = makeDBConnection($db_host, $db_user, $db_pass, $db_name);
	$inquery = 'SELECT * FROM comments WHERE replyto = ' . $row['commentid'] . ';';				
	$inresult = mysql_query($inquery, $inconnection);
	while ($inrow = mysql_fetch_array($inresult)) {
		$reply_array[] = $inrow;
	}
	foreach ($reply_array as $inrow) {
		formatPostHTML($inrow, $id, $db_host, $db_user, $db_pass, $db_name);
	}
	mysql_close($inconnection);
}

$query = 'SELECT * FROM comments WHERE eventid = ' . $id . ' AND replyto IS NULL;';
$result = mysql_query($query, $connection);
if ($result) {
	while ($row = mysql_fetch_array($result)) {
		$base_array[] = $row;
	}
	foreach ($base_array as $row) {
		formatPostHTML($row, $id, $db_host, $db_user, $db_pass, $db_name);
	}
}
else {
	echo '<p>No comments yet!</p>';
}

mysql_close($connection);
?>
</div>
<!-- END COPY-AND-PASTE COMMENT CODE -->

</body>
</html>