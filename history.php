<?php
include 'credentials.php';
include_once 'menu.php';

function generate_js_charts() {
	?>
	<script type="text/javascript">
	function fnPlot(sernum) {	
	  $.getJSON('curve_data.php?datafor=<?php echo $_GET['datafor']; ?>&sernum='+sernum, 'jsonp', function(data, textStatus, jqXHR) {
		 $('#wgif_'+sernum).css('display','none');
		 var idx = sernums.indexOf(sernum);
		 if(idx + 1 < sernums.length ) {
			fnPlot(sernums[idx+1]);
		 }
		 var plot1 = $.jqplot('chart_'+sernum, data, {
		   title:'Temperatura powietrza dla '+ sernum +' w &ordm;C',
		   axes:{xaxis:{renderer:$.jqplot.DateAxisRenderer}},
		   series:[{lineWidth:1, markerOptions:{show:false}}]
		 });
	  });
	}
	$(document).ready(fnPlot(sernums[0]));
	</script>
	<?php	
}

function download_statistics() {
	?>
<script type="text/javascript">
	function fnDownloadStat(sernum) {
		$.getJSON('statistics_history.php?datafor=<?php echo $_GET['datafor']; ?>&sernum='+sernum, 'jsonp' , function(data, textStatus, jqXHR) {
			$('#'+sernum+'_tempcount').html(data.tempcount);
			$('#'+sernum+'_tempcount_wait_img').css('display','none');
			$('#'+sernum+'_avgtemp').html(data.avgtemp + '&ordm;C');
			$('#'+sernum+'_avgtemp_wait_img').css('display','none');
			$('#'+sernum+'_mintemp').html(data.mintemp + '&ordm;C');
			$('#'+sernum+'_minreaddate').html(data.minreaddate);
			$('#'+sernum+'_mintemp_wait_img').css('display','none');
			$('#'+sernum+'_maxtemp').html(data.maxtemp + '&ordm;C');
			$('#'+sernum+'_maxreaddate').html(data.maxreaddate);
			$('#'+sernum+'_maxtemp_wait_img').css('display','none');
			$('#'+sernum+'_lasttemp').html(data.lasttemp + '&ordm;C');
			$('#'+sernum+'_lastreaddate').html(data.lastreaddate);
			$('#'+sernum+'_lasttemp_wait_img').css('display','none');
			$('#'+sernum+'_firsttemp').html(data.firsttemp + '&ordm;C');
			$('#'+sernum+'_firstreaddate').html(data.firstreaddate);
			$('#'+sernum+'_firsttemp_wait_img').css('display','none');
			var idx = sernums.indexOf(sernum);
			if(idx + 1 < sernums.length ) {
				fnDownloadStat(sernums[idx+1]);
			}
		});
	}
	$(document).ready(fnDownloadStat(sernums[0]));
</script>	
	<?php
}

if( isset($_GET["datafor"]) && preg_match("/^20[0-9]{2}-[0-1][0-9]-[0-3][0-9]$/", $_GET["datafor"])) {

?><!DOCTYPE html>
<html>
<head>
<?php menu_in_header(); ?>
<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="jquery.jqplot/excanvas.min.js"></script><![endif]-->
<script type="text/javascript" src="jquery.jqplot/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="jquery.jqplot/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="jquery.jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
 <link rel="stylesheet" type="text/css" href="jquery.jqplot/jquery.jqplot.min.css" />
<?php 
	prepare_sernums_array();
	generate_js_charts(); 
	download_statistics();
?>
</head>
<body>
<?php render_menu('history'); ?>
Wartości temperatur z 24h<br/>
<br/><br/>

<?php foreach ($sernums as $sernum) {  ?>
<table border="0">
<tr>
<td>Ilość punktów pomiarowych w przedziale 24h: </td><td><span id="<?=$sernum?>_tempcount"></span><img id="<?=$sernum?>_tempcount_wait_img" src="small_wait.gif" /></td></tr><tr>
<td>Wartość średnia arytmetyczna: </td><td><span id="<?=$sernum?>_avgtemp"></span><img id="<?=$sernum?>_avgtemp_wait_img" src="small_wait.gif" /></td></tr><tr>
<td>Wartość minimum z <span id="<?=$sernum?>_minreaddate"></span>: </td><td><span id="<?=$sernum?>_mintemp"></span><img id="<?=$sernum?>_mintemp_wait_img" src="small_wait.gif" /></td></tr><tr>
<td>Wartość maksimum z <span id="<?=$sernum?>_maxreaddate"></span>: </td><td><span id="<?=$sernum?>_maxtemp"></span><img id="<?=$sernum?>_maxtemp_wait_img" src="small_wait.gif" /></td></tr><tr>
<td>Ostania temperatura z <span id="<?=$sernum?>_lastreaddate"></span> to </td><td><span id="<?=$sernum?>_lasttemp"></span><img id="<?=$sernum?>_lasttemp_wait_img" src="small_wait.gif" /></td></tr>
<td>Pierwsza temperatura z <span id="<?=$sernum?>_firstreaddate"></span> to </td><td><span id="<?=$sernum?>_firsttemp"></span><img id="<?=$sernum?>_firsttemp_wait_img" src="small_wait.gif" /></td></tr>
</table><br/>
<div id="chart_<?=$sernum?>" style="height:300px; width:1000px;  margin-left: 10px">
  <table style="width: 100%; height: 100%; text-align: center; vertical-align: middle;">
    <tr><td>
        <img id="wgif_<?=$sernum?>" src="wait.gif" />
    </td></tr>
  </table>
</div><br/>
<br/><br/>
<?php } ?>
</body>
</html>
<?php
}
else {
	$link = new mysqli($dbhost_name, $dbuser, $dbpasswd, $database, $dbport);
	if ($link->connect_errno) {
		die( "Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
	}
	$query = "select DATE_FORMAT(min(readdate), '%Y-%m-%d 00:00:00') as mindate from temperature_results";
	$result = $link->query($query);
		
	if (!$result) {
		die("ERROR2: query:". $query. ", mysql_error: " . $link->error);
	}
	$dates = array();	
	$readasoc = $result->fetch_assoc();
	$mindate = strtotime($readasoc["mindate"]);
	$diffdate = strtotime(date("Y-M-d")) - $mindate;
	$diffdate = $diffdate/86400;
	for( $i=0; $i < $diffdate; $i++ ) {
		$dates[] = strftime("%Y-%m-%d",$mindate+($i*86400));
	}
	mysqli_free_result($result);
	mysqli_close($link);
?>
<html>
<head>
<?php menu_in_header(); ?>
</head>
<body>
<?php render_menu('history'); ?>
<form method="GET">
Wybierz dzień dla którego mają być wyświetlone dane temperatur:
<select name="datafor">
	<?php 
		for($j=0; $j<count($dates); $j++) {
			echo "<option>" .$dates[$j] ."</option>\n";
		}
	?>
</select>
<input type="submit">
</form>
</body>
</html>
<?php
}
?>