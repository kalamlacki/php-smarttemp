<?php
    $time = strtotime("2009-09-30 20:24:00");
	$stop_date = date('Y-m-d H:i:s', $time);

    echo 'date before day adding: '.$stop_date; 

    $stop_date = date('Y-m-d H:i:s', strtotime('+1 day', $time));

    echo ' date after adding one day. SHOULD be rolled over to the next month: '.$stop_date;
?>