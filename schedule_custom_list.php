<?php
include 'credentials.php';
header('Content-type: text/plain');

$listing = array();

$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
if ($link->connect_errno) {
    die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
}

$query = "select date(start_date) as date, sernum, 
	count(*) as cnt,
    round(avg((min_temp+max_temp)/2), 1) as average, 
    round(min((min_temp+max_temp)/2),1) as min, 
    round( max((min_temp+max_temp)/2),1) as max
from temperature_schedule
group by date(start_date), sernum
order by date desc, sernum";

$result = $link->query($query);

if (!$result) {
	echo json_encode(array("ERROR" => $link->error, "query"=>$query));
	mysqli_close($link);
	die();
}

if($link->affected_rows > 0) {
	while($row=$result->fetch_assoc()) {
		$listing[] = $row;
	}
	mysqli_free_result($result);	
}
else {
	echo json_encode(array("ERROR" => "There is no data in database"));
	die();
}	

echo json_encode($listing);
mysqli_close($link);

?>