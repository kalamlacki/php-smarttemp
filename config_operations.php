<?php
include 'credentials.php';
include 'initdevices.php';

header('Content-type: text/plain');
$data = json_decode(file_get_contents('php://input'), true);
if($data == null) {
	die("I accept only JSON format in input data!");
}

if(!isset($data["passkey"]) || md5($data["passkey"]) != $passkey_md5) {
	die('ERROR0: missing or incorrect passkey parameter');
}

header('Content-type: text/plain');
$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
if ($link->connect_errno) {
    die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
}

switch ($data["operation"]) {
    case "insert":
    	insert_new_device($link, $data["params"]);
        break;
    case "delete":
        delete_device($link, $data["params"]);
        break;
    case "update":
	update_device($link, $data["params"]);
        break;
    default:
        echo "Unsupported operation!";
}

mysqli_close($link);

?>

