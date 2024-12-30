<?php
$dbhost_name='127.0.0.1';
$dbport=3306;
$dbhost='127.0.0.1:3306';
$dbuser="root";
$dbpasswd="sezamxx";
$database="meteo_station";
$sernums = read_sernums();
$passkey_md5="zonk";

function read_sernums() {
	$sernums = array();
	$link = new mysqli($GLOBALS['dbhost_name'], $GLOBALS['dbuser'], $GLOBALS['dbpasswd'], $GLOBALS['database'], $GLOBALS['dbport']);
	if ($link->connect_errno) {
		die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
	}
	$query = "select sernum from serial_numbers";
	$result = $link->query($query);
		
	if (!$result) {
		die("ERROR2: query:". $query. ", mysqli_errorno: " . $link->error);
	}
	while($row=$result->fetch_assoc()) {
		array_push($sernums,$row['sernum']);
	}
	
	mysqli_free_result($result);
	$link->query("update webpage_counter set counter=counter+1");
	mysqli_close($link);
	return $sernums;
}

function prepare_sernums_array() {
	$js_content = "'". implode("', '", $GLOBALS['sernums']) ."'";
	?>
	<script type="text/javascript">
		var sernums = [ <?php echo $js_content;?> ];
	</script>
	<?php	
}

function read_statistic_data_for_sernum_and_data($sernum, $datafor) {
	$link = new mysqli($GLOBALS['dbhost_name'], $GLOBALS['dbuser'], $GLOBALS['dbpasswd'], $GLOBALS['database'], $GLOBALS['dbport']);
	if ($link->connect_errno) {
		die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
	}
	$query = <<<END_OF_QUERY
	select  count(tr0.temp) as tempcount,round(avg(tr0.temp),2) as avgtemp, round(min(tr0.temp),2) as mintemp, round(max(tr0.temp),2) as maxtemp, 
	 (select round(tr1.temp,2) from temperature_results tr1
		where  sernum='{$sernum}' and readdate > '{$datafor}' and readdate < '{$datafor}' + interval 1 day and tr1.readdate=(select max(tr2.readdate) 
			from temperature_results tr2 where sernum='{$sernum}' and readdate > '{$datafor}' and readdate < '{$datafor}' + interval 1 day))  as lasttemp,
	 (select max(tr3.readdate) 
		from temperature_results tr3 where sernum='{$sernum}' and readdate > '{$datafor}' and readdate < '{$datafor}' + interval 1 day) as lastreaddate,
	(select round(tr1.temp,2) from temperature_results tr1
		where sernum='{$sernum}' and readdate > '{$datafor}' and readdate < '{$datafor}' + interval 1 day and tr1.readdate=(select min(tr2.readdate) 
			from temperature_results tr2 where sernum='{$sernum}' and readdate > '{$datafor}' and readdate < '{$datafor}' + interval 1 day))  as firsttemp,
	 (select min(tr3.readdate) 
		from temperature_results tr3 where sernum='{$sernum}' and readdate > '{$datafor}' and readdate < '{$datafor}' + interval 1 day) as firstreaddate,
	 (select tr4.readdate 
		from temperature_results tr4  where sernum='{$sernum}' and readdate > '{$datafor}' and readdate < '{$datafor}' + interval 1 day and tr4.temp=min(tr0.temp) limit 0,1) as minreaddate,
	 (select tr5.readdate 
		from temperature_results tr5 where sernum='{$sernum}' and readdate > '{$datafor}' and readdate < '{$datafor}' + interval 1 day and tr5.temp=max(tr0.temp) limit 0,1) as maxreaddate		
	 from temperature_results tr0 where sernum='{$sernum}' and readdate > '{$datafor}' and readdate < '{$datafor}' + interval 1 day
END_OF_QUERY;
	$result = $link->query($query);
		
	if (!$result) {
		die("ERROR2: query:". $query. ", mysql_error: " . $link->error);
	}
		
	$temps = $result->fetch_assoc();

	mysqli_free_result($result);
	mysqli_close($link);
	return $temps;
}

function read_statistic_data_for_sernum($sernum) {
	$link = new mysqli($GLOBALS['dbhost_name'], $GLOBALS['dbuser'], $GLOBALS['dbpasswd'], $GLOBALS['database'], $GLOBALS['dbport']);
	if ($link->connect_errno) {
		die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
	}
	$query = <<<END_OF_QUERY
	select  count(tr0.temp) as tempcount,round(avg(tr0.temp),2) as avgtemp, round(min(tr0.temp),2) as mintemp, round(max(tr0.temp),2) as maxtemp, 
	 (select round(tr1.temp,2) from temperature_results tr1
		where  tr1.sernum='{$sernum}' and tr1.readdate=(select max(tr2.readdate) 
			from temperature_results tr2 where tr2.sernum='{$sernum}' and tr2.readdate > now() - interval 1 day))  as nowtemp,
	 (select max(tr3.readdate) 
		from temperature_results tr3 where tr3.sernum='{$sernum}' and tr3.readdate > now() - interval 1 day) as lastreaddate,
	 (select tr4.readdate 
		from temperature_results tr4  where tr4.readdate > now() - interval 1 day and tr4.temp=min(tr0.temp) and tr4.sernum='{$sernum}' limit 0,1) as minreaddate,
	 (select tr5.readdate 
		from temperature_results tr5  where tr5.readdate > now() - interval 1 day and tr5.temp=max(tr0.temp) and tr5.sernum='{$sernum}' limit 0,1) as maxreaddate		
	 from temperature_results tr0 
	 where tr0.readdate > now() - interval 1 day and tr0.sernum = '{$sernum}'
END_OF_QUERY;
	$result = $link->query($query);
		
	if (!$result) {
		die("ERROR2: query:". $query. ", mysql_error: " . $link->error);
	}
		
	$temps = $result->fetch_assoc();

	mysqli_free_result($result);
	mysqli_close($link);
	return $temps;
}

?>