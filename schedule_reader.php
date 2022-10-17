<?php
include 'credentials.php';
header('Content-type: text/plain');

$curve_data1 = array();

if(!isset($_GET["sernum"]) || !isset($_GET["weekday"])) {
	die('ERROR0: missing sernum parameter or weekday');
}

$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
if ($link->connect_errno) {
    echo "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error;
}

$query = "select * from  temperature_schedule_weekly where sernum='". $link->escape_string($_GET["sernum"]). 
	"' and weekday=" . ($link->escape_string($_GET["weekday"])+1) . " order by start_time";

$result = $link->query($query);

if (!$result) {
	echo json_encode(array("ERROR" => mysql_error(), "query"=>$query));
	mysqli_close($link);
	die();
}
$delta = 0.0;
if($link->affected_rows > 0) {
	while($row=$result->fetch_assoc()) {
		$averrage_temp = round(($row["min_temp"] + $row["max_temp"]) / 2, 1);
		$delta = round($row["max_temp"] - $averrage_temp, 1 );
		$curve_data1[] = array($row['start_time'],$averrage_temp);
		$curve_data1[] = array($row['stop_time'],$averrage_temp);
	}
	mysqli_free_result($result);	
}
else {
	echo json_encode(array("ERROR" => "There is no data in database"));
	die();
}	

echo json_encode(array("curve"=>array($curve_data1), "sernum"=>$_GET["sernum"], "day"=>$_GET["weekday"], "delta"=>$delta));
mysqli_close($link);

?>