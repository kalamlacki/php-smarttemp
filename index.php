<?php
include 'credentials.php';
include_once 'menu.php';

function generate_js_charts() {
	?>
	<script type="text/javascript">
	var plot = [];
	function fnPlot(sernum) {
		$.getJSON('curve_data.php?sernum='+sernum, 'jsonp' , function(data, textStatus, jqXHR) {
			if(plot[sernum]) {
				plot[sernum].destroy();
			}		
			$('#wgif_'+sernum).css('display','none');
			var idx = sernums.indexOf(sernum);
			if(idx + 1 < sernums.length ) {
				fnPlot(sernums[idx+1]);
			}
			plot[sernum] = $.jqplot('chart_'+sernum, data, {
				title:'Temperatura powietrza dla ' + sernum +' w &ordm;C z ostatnich 24h',
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
		$.getJSON('statistics.php?sernum='+sernum, 'jsonp' , function(data, textStatus, jqXHR) {
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
			$('#'+sernum+'_nowtemp').html(data.nowtemp + '&ordm;C');
			$('#'+sernum+'_lastreaddate').html(data.lastreaddate);
			$('#'+sernum+'_nowtemp_wait_img').css('display','none');
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

?><!DOCTYPE html>
<html>
<head>
<?php menu_in_header(); ?>
<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="jquery.jqplot/excanvas.min.js"></script><![endif]-->

<script language="javascript" type="text/javascript" src="./jquery.jqplot/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="./jquery.jqplot/jquery.jqplot.min.js"></script>
<script language="javascript" type="text/javascript" src="./jquery.jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
<link rel="stylesheet" type="text/css" href="./jquery.jqplot/jquery.jqplot.min.css" />
<?php
	prepare_sernums_array();
	generate_js_charts();
	download_statistics();	
?>
</head>
<body>
<?php render_menu('main'); ?>
<br/><br/>
<?php foreach ($sernums as $sernum) {  ?>
<table border="0">
<tr>
<td>Ilość punktów pomiarowych w ostatnich 24h: </td><td><span id="<?=$sernum?>_tempcount"></span><img id="<?=$sernum?>_tempcount_wait_img" src="small_wait.gif" /></td></tr><tr>
<td>Wartość średnia arytmetyczna: </td><td><span id="<?=$sernum?>_avgtemp"></span><img id="<?=$sernum?>_avgtemp_wait_img" src="small_wait.gif" /></td></tr><tr>
<td>Wartość minimum z <span id="<?=$sernum?>_minreaddate"></span>: </td><td><span id="<?=$sernum?>_mintemp"></span><img id="<?=$sernum?>_mintemp_wait_img" src="small_wait.gif" /></td></tr><tr>
<td>Wartość maksimum z <span id="<?=$sernum?>_maxreaddate"></span>: </td><td><span id="<?=$sernum?>_maxtemp"></span><img id="<?=$sernum?>_maxtemp_wait_img" src="small_wait.gif" /></td></tr><tr>
<td>Temperatura z <span id="<?=$sernum?>_lastreaddate"></span> to </td><td><span id="<?=$sernum?>_nowtemp"></span><img id="<?=$sernum?>_nowtemp_wait_img" src="small_wait.gif" /></td></tr>
</table><br/>
<div id="chart_<?=$sernum?>" style="height:300px; width:1000px; margin-left: 10px;">
 <table style="width: 100%; height: 100%; text-align: center; vertical-align: middle;">
    <tr><td>
        <img id="wgif_<?=$sernum?>" src="wait.gif" />
    </td></tr>
  </table>
</div><br/>
<?php } ?>
</body>
</html>