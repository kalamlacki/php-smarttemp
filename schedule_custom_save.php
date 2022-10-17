<?php
include 'credentials.php';
header('Content-type: text/plain');
$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);
//echo file_get_contents('php://input');

$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
if ($link->connect_errno) {
    die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
}

if (($timestamp_start = strtotime($data["date"])) === -1) {
	die("Date text {$data["date"]} is incorrect");
}

$timestamp_end = strtotime('+1 day', $timestamp_start);

$start_date = date("Y-m-d", $timestamp_start);
$end_date = date("Y-m-d", $timestamp_end);



if (!($sel_stmt = $link->prepare("select id, start_date from  temperature_schedule where sernum=? and start_date>=? and start_date<? order by start_date"))) {
     echo "Prepare failed: (" . $link->errno . ") " . $link->error;
	 mysqli_close($link);
	 die();
}

$data["day"]++;
if(!$sel_stmt->bind_param('sss', $data["sernum"], $start_date, $end_date )) {
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

// insert new records
if($sel_stmt->num_rows == 0) { 
	$sel_stmt->close();
	if(count($data["curve"][0]) != 48){
		mysqli_close($link);
		echo "Incorrect input parameters!";
		die();
	}
	if (!($ins_stmt = $link->prepare("insert into temperature_schedule(sernum,min_temp,max_temp,start_date,stop_date,user_id) values (?,?,?,?,?,3)"))) {
		echo "Prepare failed: (" . $link->errno . ") " . $link->error;
		$ins_stmt->close();
		mysqli_close($link);
		die();
	}
	
	if(!$ins_stmt->bind_param('sddss', $data["sernum"], $min_temp, $max_temp, $start_date, $stop_date )) {
		echo "Binding parameters failed: (" . $ins_stmt->errno . ") " . $ins_stmt->error;
		$ins_stmt->close();
		mysqli_close($link);
		die();
	}
	$cnt = count($data["curve"][0])/2;
	for($i=0;$i < $cnt; $i++) {
		$min_temp = $data["curve"][0][$i*2][1] - $data["temp_range"];
		$max_temp = $data["curve"][0][$i*2][1] + $data["temp_range"];
		$start_date = $data["curve"][0][$i*2][0]; 
		$stop_date = $data["curve"][0][$i*2 + 1][0];
		if(!$ins_stmt->execute()) {
			echo "Execute failed: (" . $ins_stmt->errno . ") " . $ins_stmt->error;
			$ins_stmt->close();
			mysqli_close($link);
			die();
		}
	}
	
	$ins_stmt->close();
}
// records already in db, updateing
else {
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

	if (!($upd_stmt = $link->prepare("update temperature_schedule set min_temp=?, max_temp=? where id=?"))) {
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
}

mysqli_close($link);

echo "Temperatures have been saved to DB!";

?>