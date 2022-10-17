<?php
include 'credentials.php';
header('Content-type: text/plain');

$curve_data1 = array();

if(!isset($_GET["sernum"]) || !isset($_GET["date"])) {
	die('ERROR0: missing sernum parameter or weekday');
}

$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
if ($link->connect_errno) {
    die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
}
$link->query("SET SQL_SAFE_UPDATES = 0");


$query = "delete from temperature_schedule where sernum='". $link->escape_string($_GET["sernum"]). "' and date(start_date)='" . $link->escape_string($_GET["date"]) . "'";

$result = $link->query($query);
if (!$result) {
	echo json_encode(array("ERROR" => $link->error, "query"=>$query));
	mysqli_close($link);
	die();
}

if($link->affected_rows > 0) {
	echo json_encode(array("response"=>"Successfuly deleted from database", "sernum"=>$_GET["sernum"], "date"=>$_GET["date"]));
}
else {
	echo json_encode(array("response" => "ERROR: There is no data in database"));
}	
mysqli_close($link);

?>