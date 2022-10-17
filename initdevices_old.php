<?php
include 'credentials.php';
header('Content-type: text/plain');

#init_weekly_schedule("sypialnia-rpi-3");
#echo "Zakończono inicjalizację harmonogramu tygodniowego dla rpi-3";

function init_weekly_schedule($sernum) {
	$curve_data1 = array();

	$curve_data1[] = array('00:00:00',21.0);
	$curve_data1[] = array('00:59:59',21.0);
	$curve_data1[] = array('01:00:00',21.0);
	$curve_data1[] = array('01:59:59',21.0);
	$curve_data1[] = array('02:00:00',21.0);
	$curve_data1[] = array('02:59:59',21.0);
	$curve_data1[] = array('03:00:00',21.0);
	$curve_data1[] = array('03:59:59',21.0);
	$curve_data1[] = array('04:00:00',21.0);
	$curve_data1[] = array('04:59:59',21.0);
	$curve_data1[] = array('05:00:00',21.0);
	$curve_data1[] = array('05:59:59',21.0);
	$curve_data1[] = array('06:00:00',21.0);
	$curve_data1[] = array('06:59:59',21.0);
	$curve_data1[] = array('07:00:00',21.0);
	$curve_data1[] = array('07:59:59',21.0);
	$curve_data1[] = array('08:00:00',21.0);
	$curve_data1[] = array('08:59:59',21.0);
	$curve_data1[] = array('09:00:00',21.0);
	$curve_data1[] = array('09:59:59',21.0);
	$curve_data1[] = array('10:00:00',21.0);
	$curve_data1[] = array('10:59:59',21.0);
	$curve_data1[] = array('11:00:00',21.0);
	$curve_data1[] = array('11:59:59',21.0);
	$curve_data1[] = array('12:00:00',21.0);
	$curve_data1[] = array('12:59:59',21.0);
	$curve_data1[] = array('13:00:00',21.0);
	$curve_data1[] = array('13:59:59',21.0);
	$curve_data1[] = array('14:00:00',21.0);
	$curve_data1[] = array('14:59:59',21.0);
	$curve_data1[] = array('15:00:00',21.0);
	$curve_data1[] = array('15:59:59',21.0);
	$curve_data1[] = array('16:00:00',21.0);
	$curve_data1[] = array('16:59:59',21.0);
	$curve_data1[] = array('17:00:00',21.0);
	$curve_data1[] = array('17:59:59',21.0);
	$curve_data1[] = array('18:00:00',21.0);
	$curve_data1[] = array('18:59:59',21.0);
	$curve_data1[] = array('19:00:00',21.0);
	$curve_data1[] = array('19:59:59',21.0);
	$curve_data1[] = array('20:00:00',21.0);
	$curve_data1[] = array('20:59:59',21.0);
	$curve_data1[] = array('21:00:00',21.0);
	$curve_data1[] = array('21:59:59',21.0);
	$curve_data1[] = array('22:00:00',21.0);
	$curve_data1[] = array('22:59:59',21.0);
	$curve_data1[] = array('23:00:00',21.0);
	$curve_data1[] = array('23:59:59',21.0);

	$link = new mysqli($GLOBALS['dbhost_name'], $GLOBALS['dbuser'], $GLOBALS['dbpasswd'], $GLOBALS['database'], $GLOBALS['dbport']);
	if ($link->connect_errno) {
		die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
	}


	if (!($stmt = $link->prepare("INSERT INTO temperature_schedule_weekly(user_id,sernum,min_temp,max_temp,start_time,stop_time,weekday)
									VALUES (3, ?, ?, ?, ?, ?, ?)"))) {
		 echo "Prepare failed: (" . $link->errno . ") " . $link->error;
	}

	if(!$stmt->bind_param('sddssi', $sernum, $min_temp, $max_temp, $start_time, $stop_time, $weekday )) {
		echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}


	for($i=1; $i<=7; $i++) {
		$len = count($curve_data1)/2;
		for($j=0; $j<$len; $j++) {
			$min_temp = $curve_data1[$j*2][1] - 0.25;
			$max_temp = $curve_data1[$j*2][1] + 0.25;
			$start_time = $curve_data1[$j*2][0];
			$stop_time = $curve_data1[$j*2+1][0];
			$weekday = $i;
			
			if (!$stmt->execute()) {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
		}
	}

	$stmt->close();
	mysqli_close($link);
}


?>