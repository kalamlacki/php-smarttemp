<?php
include 'credentials.php';
$link = mysql_connect($dbhost, $dbuser, $dbpasswd);
mysql_select_db ($database);
if (!$link) {
	die('ERROR1: ' . mysql_error());
}
$result = mysql_query("select counter from webpage_counter");
	
if (!$result) {
	die("ERROR2: query:". $query. ", mysql_error: " . mysql_error());
}
$licznik = mysql_fetch_assoc($result);	

mysql_free_result($result);
mysql_close($link);
?>
<html>
<head>
<title>Amatorska stacja meteorologiczna w Katowicach</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250" />
</head>
<body>
Licznik: <?php echo $licznik['counter'] ?>
</body>
</html>