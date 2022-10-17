<?php
include 'credentials.php';
header('Content-type: text/plain');

$curve_data1 = array();
$data_orgin = "Initial value";

if(!isset($_GET["sernum"]) || !isset($_GET["date"])) {
	die('ERROR0: missing sernum parameter or weekday');
}

if (($timestamp_start = strtotime($_GET["date"])) === -1) {
	die("Date text {$_GET["date"]} is incorrect");
}
$weekday = date("w", $timestamp_start);
$timestamp_end = strtotime('+1 day', $timestamp_start);
$start_date = date("Y-m-d", $timestamp_start);
$end_date = date("Y-m-d", $timestamp_end);

$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
if ($link->connect_errno) {
    die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
}


$query = "select * from  temperature_schedule where sernum='". $link->escape_string($_GET["sernum"])
	. "' and start_date>='" . $start_date . "' and start_date <'" . $end_date . "' order by start_date";

$result = $link->query($query);
if (!$result) {
	echo json_encode(array("ERROR" => $link->error, "query"=>$query));
	mysqli_close($link);
	die();
}

$delta = 0.0;
if($link->affected_rows > 0) {
	while($row=$result->fetch_assoc()) {
		$averrage_temp = round(($row["min_temp"] + $row["max_temp"]) / 2, 1);
		$delta = round($row["max_temp"] - $averrage_temp,1);
		$curve_data1[] = array($row['start_date'],$averrage_temp);
		$curve_data1[] = array($row['stop_date'],$averrage_temp);
	}
	mysqli_free_result($result);	
	$data_orgin = "Already saved in DB (Edit mode)";
}
else {
	mysqli_free_result($result);
	$query = "select * from  temperature_schedule_weekly where sernum='". $link->escape_string($_GET["sernum"]).
		"' and weekday=" . ($weekday+1) ." order by start_time";

	$result = $link->query($query);

	if (!$result) {
		echo json_encode(array("ERROR" => $link->error, "query"=>$query));
		mysqli_close($link);
		die();
	}
	$delta = 0.0;
	if($link->affected_rows > 0) {
		while($row=$result->fetch_assoc()) {
			$averrage_temp = round(($row["min_temp"] + $row["max_temp"]) / 2,1);
			$delta = round($row["max_temp"] - $averrage_temp, 1);
			$curve_data1[] = array($_GET["date"] ." ". $row['start_time'],$averrage_temp);
			$curve_data1[] = array($_GET["date"] ." ". $row['stop_time'],$averrage_temp);
		}
		mysqli_free_result($result);	
		$data_orgin = "Data copied from weekly schedule (Creating new)";
	}
	else {
		echo json_encode(array("ERROR" => "There is no data in database"));
		die();
	}	
}
echo json_encode(array("curve"=>array($curve_data1), "sernum"=>$_GET["sernum"], "date"=>$_GET["date"], "delta"=>$delta, "orgin"=>$data_orgin));
mysqli_close($link);

?>