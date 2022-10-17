<?php
include 'credentials.php';
$curve_data1 = array();
if(!isset($_POST["passkey"]) || md5($_POST["passkey"]) != $passkey_md5) {
	die('ERROR0: missing or incorrect passkey parameter');
}

header('Content-type: text/plain');
$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
if ($link->connect_errno) {
    die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
}

$query = "select * from serial_numbers";

$result = $link->query($query);
if (!$result) {
	echo json_encode(array("ERROR" => $link->error()));
	mysqli_close($link);
	die();
}
if($link->affected_rows > 0) {
	while($row=$result->fetch_assoc()) {
		$curve_data[] = $row;
	}
	//echo json_encode($curve_data);
	mysqli_free_result($result);	
}
else {
	echo json_encode(array("ERROR" => "There is no data in database"));
	die();
}	

echo json_encode($curve_data);
mysqli_close($link);

?>

