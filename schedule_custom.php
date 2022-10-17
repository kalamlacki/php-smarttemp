<?php
include 'credentials.php';
include_once 'menu.php';
$date = date( "Y-m-d" );

function generate_table_header(){
	for($i=0; $i<24; $i++){
		?><TD><span id="header_<?php echo "d_{$i}"; ?>">xx</span></TD>
		<?php
	}
}
function generate_table_top_rows(){
	for($i=0; $i<24; $i++){
		?><TD><?php echo "$i"; ?>:00<BR/><a href="javascript:increment_temp(<?php echo "$i"; ?>);">+1</a></TD>
		<?php
	}
}
function generate_table_bottom_rows(){
	for($i=0; $i<24; $i++){
		?><TD><a href="javascript:decrement_temp(<?php echo "$i"; ?>);">-1</a></TD>
		<?php
	}
}
function generate_table_plus_01_rows(){
	for($i=0; $i<24; $i++){
		?><TD><a href="javascript:increment_01_temp(<?php echo "$i"; ?>);">+0.1</a></TD>
		<?php
	}
}
function generate_table_minus_01_rows(){
	for($i=0; $i<24; $i++){
		?><TD><a href="javascript:decrement_01_temp(<?php echo "$i"; ?>);">-0.1</a></TD>
		<?php
	}
}
?>
<html>
<head>
<?php menu_in_header(); ?>
<?php $sernum='test';  ?>
<script language="javascript" type="text/javascript" src="./jquery.jqplot/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="./jquery.jqplot/jquery.jqplot.min.js"></script>
<link rel="stylesheet" type="text/css" href="./jquery.jqplot/jquery.jqplot.min.css" />
<script language="javascript" type="text/javascript" src="./jquery.jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
<link rel="stylesheet" href="./jquery-ui-1.12.1.custom/jquery-ui.css">
<script src="./jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<!--link rel="stylesheet" href="./css/normalize.css"-->
<link rel="stylesheet" href="./css/style.css">

<script type="text/javascript">
		var plot;
		var curve;
		var selected_date;
		function fnGetCurves() {
			//alert('fnGetCurves()');
			
			sernum=$("#sernum_selector").val();
			selected_date = $("#date_selector").val();
			$.getJSON('schedule_custom_reader.php?sernum='+sernum+'&date=' + selected_date, 'jsonp' , function(data, textStatus, jqXHR) {
				curve = data.curve;
				fnPlot();
				update_header();
				$("#temprange_selector").val(data.delta).trigger("change");
				$("#orgin").text(data.orgin);
			});
			fnGetScheduleListing();
		}
		function fnPlot() {			
			if(plot) {
				plot.destroy();
			}
			$.jqplot.config.enablePlugins = true;			
			plot = $.jqplot('chart_d', curve, {
				title:'Ustawianie harmonogramu dla '+ selected_date,
				axes:{xaxis:{renderer:$.jqplot.DateAxisRenderer,tickOptions:{formatString:'%H:%M'},tickInterval:'1 hour'}},
				series:[{lineWidth:1, markerOptions:{show:false}}]
				});					
		}
		function fnGetScheduleListing() {
			$.getJSON('schedule_custom_list.php', 'jsonp' , function(data, textStatus, jqXHR) {
				$("#listing tbody > tr").remove();
				for(i=0;i < data.length; i++) {
					$("#listing tbody").append('<tr><td>' + data[i].sernum + 
						'</td><td>' +data[i].date +
						'</td><td>' + data[i].average +
						'</td><td>' + data[i].min +
						'</td><td>' + data[i].max +
						'</td><td><button type="button" onclick="javascript:fnDeleteClicked(\''+data[i].sernum+'\',\''+data[i].date+'\');">Kasuj</button>'+
						'</td><td><button type="button" onclick="javascript:fnEditClicked(\''+data[i].sernum+'\',\''+data[i].date+'\');">Edytuj</button></td></tr>');
				}
			});
		}
		function fnEditClicked(sernum,date) {
			$( "#date_selector" ).datepicker("setDate", new Date(date));
			$("#sernum_selector").val(sernum).trigger("change");
		}
		function fnDeleteClicked(sernum_p,date) {
			$.getJSON('schedule_custom_delete.php?sernum='+sernum_p+'&date='+date, 'jsonp' , function(data, textStatus, jqXHR) {
				fnGetScheduleListing();
				alert("Response: "+ data.response);
				sernum=$("#sernum_selector").val();
				if(sernum_p == sernum && selected_date == date) {
					$("#orgin").text("Deleted, Creating new");
				}
			
			});
		}
		function increment_temp(hrs){
			curve[0][hrs*2][1]++;
			curve[0][hrs*2 + 1][1]++;
			fnPlot();
			update_header();
		}
		function decrement_temp(hrs){
			curve[0][hrs*2][1]--;
			curve[0][hrs*2 + 1][1]--;
			
			fnPlot();
			update_header();
		}
		function increment_01_temp(hrs){
			curve[0][hrs*2][1]+=0.1;
			curve[0][hrs*2 + 1][1]+=0.1;
			curve[0][hrs*2][1]=parseFloat(curve[0][hrs*2][1].toFixed(1));
			curve[0][hrs*2 + 1][1]=parseFloat(curve[0][hrs*2 + 1][1].toFixed(1));
			fnPlot();
			update_header();
		}
		function decrement_01_temp(hrs){
			curve[0][hrs*2][1]-=0.1;
			curve[0][hrs*2 + 1][1]-=0.1;
			curve[0][hrs*2][1]=parseFloat(curve[0][hrs*2][1].toFixed(1));
			curve[0][hrs*2 + 1][1]=parseFloat(curve[0][hrs*2 + 1][1].toFixed(1));
			fnPlot();
			update_header();
		}
		function update_header() {
			length = curve[0].length/2;
			for(i=0; i<length; i++) {
				$( "#header_d_" + i ).text(curve[0][i*2][1]);
			}
			$("#transmit_button").text("Zapisz dla " + selected_date);
		}
		function transmit_changes() {
			var tran_json = {};
			tran_json['curve'] = curve;
			tran_json['temp_range'] = $("#temprange_selector").val();
			tran_json['sernum'] = $("#sernum_selector").val();
			tran_json['date'] = selected_date;
			$.ajax({
				type: "POST",
				url: 'schedule_custom_save.php',
				dataType: 'html',
				data: JSON.stringify(tran_json),
				success: function(data,status,xhr) {
					alert('Received:' + data); 
					fnGetScheduleListing();
					$("#orgin").text('Already saved in DB (Edit mode)');
				}
			});
		}
		$(document).ready( function() {
			$( "#date_selector" ).datepicker();
			$( "#date_selector" ).datepicker("option", "dateFormat", "yy-mm-dd");
			$( "#date_selector" ).datepicker("setDate", new Date($("#date").val()));
			fnGetCurves();
		} );
</script>
<style type="text/css">a {text-decoration: none}</style>
</head>
<body>
<?php render_menu('schedule_custom'); 
echo 'dzisiaj jest ' . $date;
?>
<p>Ustawianie dedykowanego harmonogramu. <span id="orgin"></span></p>
Wybierz datę:
<input type="hidden" id="date" value="<?php echo $date;?>">
<input type="text" id="date_selector" onchange="fnGetCurves()">

Wybierz urządzenie do zmiany harmonogramu:
<select id="sernum_selector" onchange="fnGetCurves()">
<?php	foreach ($sernums as $sernum_) {
			echo "<option value='{$sernum_}'>{$sernum_}</option>";
		}
?>
</select>
 Wybierz przedział temperatur: 
 <select id="temprange_selector">
	<option value="0.5">+/- 0.5 &ordm;C</option>
	<option value="0.4">+/- 0.4 &ordm;C</option>
	<option value="0.3">+/- 0.3 &ordm;C</option>
	<option value="0.2">+/- 0.2 &ordm;C</option>
 </select>
<div id="chart_d" style="height:300px; width:1000px; margin-left: 10px;"></div><br/>
<button id="transmit_button" onclick="transmit_changes()">Zapisz dla <?php echo $date ; ?></button>
<TABLE style="width:1000px; margin-left: 10px;">
<TR>
<?php generate_table_top_rows(); ?>
</TR>
<TR>
<?php generate_table_plus_01_rows(); ?>
</TR>
<TR>
<?php generate_table_minus_01_rows(); ?>
</TR>
<TR>
<?php generate_table_bottom_rows(); ?>
</TR>
<TR>
<?php generate_table_header(); ?>
</TR>
</TABLE><br/>
<table class="responstable" id="listing">
  <thead>
  <tr>
    <th>Nazwa urządzenia</th>
    <th>Data harmonogramu</th>
    <th>Śrenia</th>
	<th>Minimum</th>
	<th>Maximum</th>
    <th>Kasuj</th>
    <th>Edytuj</th>
  </tr>
  </thead>
  <tbody>
  
  </tbody>
</table>
</body>
</html>