<?php

function menu_in_header() {
	?>
	<link href="./menu_assets/styles.css" rel="stylesheet" type="text/css" />
	<title>Cyfrowy kontroler temperatur</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php
}

function render_menu($active_page) {
	echo "<div class='cssmenu'><ul>";
	if($active_page == 'main')
		echo "<li class='active '><a href='./'><span>Strona startowa</span></a></li>";
	else 
		echo "<li><a href='./'><span>Strona startowa</span></a></li>";
	if($active_page == 'history')
		echo "<li class='active '><a href='./history.php'><span>Historia temperatur</span></a></li>";
	else
		echo "<li><a href='./history.php'><span>Historia temperatur</span></a></li>";
	if($active_page == 'schedule')
		echo "<li class='active '><a href='./schedule.php'><span>Ustawianie harmonogramu tygodniowego</span></a></li>";
	else
		echo "<li><a href='./schedule.php'><span>Ustawianie harmonogramu tygodniowego</span></a></li>";
	if($active_page == 'schedule_custom')
		echo "<li class='active '><a href='./schedule_custom.php'><span>Ustawianie harmonogramu wybiórczego</span></a></li>";
	else
		echo "<li><a href='./schedule_custom.php'><span>Ustawianie harmonogramu wybiórczego</span></a></li>";
	echo "</ul></div>";
}
?>