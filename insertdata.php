<?php
include 'credentials.php';

header('Content-type: text/plain');
if( !isset($_POST['username']) || !isset($_POST['passwd']) || !isset($_POST['serial']) || 
		!preg_match('/^[A-F0-9]{16}$/',$_POST['serial']) || !isset($_POST['temp']) || !is_numeric($_POST['temp']) || 
		!isset($_POST['date']) || !preg_match('/^2[0-9]{3}-[01][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/',$_POST['date']) ) {
	echo "ERROR: missing or incorrect parameters";
}
else {
	$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
	if ($link->connect_errno) {
		die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
	}
	$query = sprintf("select * from users where username='%s' and passwd='%s' and enabled=1",
		$link->real_escape_string($_POST['username']),
		md5($_POST['passwd']));
		
	$result = $link->query($query);
	
	if (!$result) {
		die("ERROR2: query:". $query. ", mysql_error: " . $link->error);
	}
	
	$user = $result->fetch_assoc();
	mysqli_free_result($result);
	if(!$user) {	
		mysqli_close($link);
		die("ERROR3: incorrect authorization!");
	}
	$query = sprintf("insert into temperature_results(sernum, temp,readdate, user_id) values ('%s',%f,'%s',%d)",
		$link->real_escape_string($_POST['serial']),
		$link->real_escape_string($_POST['temp']),
		$link->real_escape_string($_POST['date']),
		$user['id']);
		
	if (!$link->query($query)) {
		die("ERROR4:" . $link->error);
	}

	if($link->affected_rows == 1) {
		echo "OK";
	}
	else {
		echo "ERROR?";
	}
	mysqli_close($link);
}
?>