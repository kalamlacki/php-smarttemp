<?php
include 'credentials.php';
header('Content-type: text/plain');
$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);
//echo file_get_contents('php://input');

$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
if ($link->connect_errno) {
    echo "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error;
}

if (!($sel_stmt = $link->prepare("select id, start_time from  temperature_schedule_weekly where sernum=? and weekday=?"))) {
     echo "Prepare failed: (" . $link->errno . ") " . $link->error;
	 mysqli_close($link);
	 die();
}

$data["day"]++;
if(!$sel_stmt->bind_param('si', $data["sernum"], $data["day"] )) {
	echo "Binding parameters failed: (" . $sel_stmt->errno . ") " . $sel_stmt->error;
	$sel_stmt->close();
	mysqli_close($link);
	die();
}

if (!$sel_stmt->execute()) {
	echo "Execute failed: (" . $sel_stmt->errno . ") " . $sel_stmt->error;
	$sel_stmt->close();
	mysqli_close($link);
	die();
}

$sel_stmt->store_result();
if($sel_stmt->num_rows != count($data["curve"][0])/2) {
	echo "Incorrect input parameter size data=" . (count($data["curve"][0])/2) . " db=". $sel_stmt->num_rows;
	$sel_stmt->close();
	mysqli_close($link);
	die();
}

$id = NULL;
$start_time = NULL;

if (!$sel_stmt->bind_result($id, $start_time)) {
    echo "Binding output parameters failed: (" . $sel_stmt->errno . ") " . $sel_stmt->error;
	$sel_stmt->close();
	mysqli_close($link);
	die();
}

if (!($upd_stmt = $link->prepare("update temperature_schedule_weekly set min_temp=?, max_temp=? where id=?"))) {
    echo "Prepare failed: (" . $link->errno . ") " . $link->error;
	$sel_stmt->close();
	mysqli_close($link);
	die();
}

$min_temp = NULL;
$max_temp = NULL;
if(!$upd_stmt->bind_param('ddi', $min_temp, $max_temp, $id )) {
	echo "Binding parameters failed: (" . $upd_stmt->errno . ") " . $upd_stmt->error;
	$upd_stmt->close();
	$sel_stmt->close();
	mysqli_close($link);
	die();
}

$i = 0;
while ($sel_stmt->fetch()) {
	$min_temp = $data["curve"][0][$i*2][1] - $data["temp_range"];
	$max_temp = $data["curve"][0][$i*2][1] + $data["temp_range"];
	if(!$upd_stmt->execute()) {
		echo "Execute failed: (" . $upd_stmt->errno . ") " . $upd_stmt->error;
		$upd_stmt->close();
		$sel_stmt->close();
		mysqli_close($link);
		die();
	}
	$i++;
}

$upd_stmt->close();
$sel_stmt->close();
mysqli_close($link);

echo "Temperatures have been saved to DB!";

?>