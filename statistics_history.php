<?php
include 'credentials.php';
header('Content-type: text/plain');

if(!isset($_GET["sernum"]) || !isset($_GET["datafor"])) {
	die('ERROR0: missing sernum parameter or datafor');
}

$temps=read_statistic_data_for_sernum_and_data($_GET["sernum"], $_GET["datafor"]);

echo json_encode($temps);

?>