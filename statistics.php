<?php
include 'credentials.php';
header('Content-type: text/plain');

if(!isset($_GET["sernum"])) {
	die('ERROR0: missing sernum parameter');
}

$temps=read_statistic_data_for_sernum($_GET["sernum"]);

echo json_encode($temps);

?>