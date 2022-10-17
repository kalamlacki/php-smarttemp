<?php
include 'credentials.php';
$curve_data1 = array();
if(!isset($_GET["sernum"])) {
	die('ERROR0: missing sernum parameter');
}

header('Content-type: text/plain');
$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
if ($link->connect_errno) {
    die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
}
if( isset($_GET["datafor"]) && preg_match("/^20[0-9]{2}-[0-1][0-9]-[0-3][0-9]$/", $_GET["datafor"]) )
	$query = "select readdate, round(temp,2) as temp from temperature_results where readdate > '{$_GET["datafor"]}' and readdate < '{$_GET["datafor"]}' + interval 1 day and sernum='".  $link->escape_string($_GET["sernum"]). "'";
else
	$query = "select readdate, round(temp,2) as temp from temperature_results where readdate > now() - interval 1 day and sernum='". $link->escape_string($_GET["sernum"]). "'";
$result = $link->query($query);
if (!$result) {
	echo json_encode(array("ERROR" => $link->error()));
	mysqli_close($link);
	die();
}
if($link->affected_rows > 0) {
	while($row=$result->fetch_assoc()) {
		$curve_data1[] = array($row['readdate'],(float)$row['temp']);
	}
	//echo json_encode($curve_data);
	mysqli_free_result($result);	
}
else {
	echo json_encode(array("ERROR" => "There is no data in database"));
	die();
}	

echo json_encode(array($curve_data1));
mysqli_close($link);

?>