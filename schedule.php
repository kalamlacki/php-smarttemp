<?php
include 'credentials.php';
include_once 'menu.php';
$dni_tygodnia = array( 'Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota' );
$date = date( "w" );

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
<script type="text/javascript">
		var dni_tygodnia = [ 'Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota' ];
		var plot;
		var curve;
		var selected_day;
		function fnGetCurves() {
			sernum=$("#sernum_selector").val();
			selected_day = $("#daynum_selector").val();
			$.getJSON('schedule_reader.php?sernum='+sernum+'&weekday=' + selected_day, 'jsonp' , function(data, textStatus, jqXHR) {
				curve = data.curve;
				fnPlot();
				update_header();
				$("#temprange_selector").val(data.delta).trigger("change");
			});
			
		}
		function fnPlot() {			
			if(plot) {
				plot.destroy();
			}				
			plot = $.jqplot('chart_d', curve, {
				title:'Ustawianie harmonogramu dla '+ dni_tygodnia[selected_day],
				axes:{xaxis:{renderer:$.jqplot.DateAxisRenderer,tickOptions:{formatString:'%H:%M'},tickInterval:'1 hour'}},
				series:[{lineWidth:1, markerOptions:{show:false}}]
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
			$("#transmit_button").text("Zapisz dla " + dni_tygodnia[selected_day]);
		}
		function transmit_changes() {
			var tran_json = {};
			tran_json['curve'] = curve;
			tran_json['temp_range'] = $("#temprange_selector").val();
			tran_json['sernum'] = $("#sernum_selector").val();
			tran_json['day'] = selected_day;
			$.ajax({
				type: "POST",
				url: 'schedule_save.php',
				dataType: 'html',
				data: JSON.stringify(tran_json),
				success: function(data,status,xhr) {
					alert('Received:' + data); 
				}
			});
		}
		$(document).ready(fnGetCurves);
</script>
<style type="text/css">a {text-decoration: none}</style>
</head>
<body>
<?php render_menu('schedule'); 
echo 'dzisiaj jest ' . $dni_tygodnia[ $date ];
?>
<p>Ustawianie tygodniowego harmonogramu.</p>
Wybierz dzień tygodnia:
<select id="daynum_selector" onchange="fnGetCurves()">
<?php
	for($i=0; $i<count($dni_tygodnia); $i++) {
		if( $i == $date) {
			echo "<option selected value='{$i}'>{$dni_tygodnia[$i]}</option>";
		}
		else {
			echo "<option value='{$i}'>{$dni_tygodnia[$i]}</option>";
		}
	}
?>
</select>
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
<button id="transmit_button" onclick="transmit_changes()">Zapisz dla <?php echo  $dni_tygodnia[ $date ]; ?></button>
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
</body>
</html>