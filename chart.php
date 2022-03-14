<?php
require_once 'embedded.php';
?>
<!doctype html>
<html lang="bg">
<head>
<base href="" target="_self" />
<meta charset="utf-8" />
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chart | Machine Monitoring</title>
<link rel="stylesheet" type="text/css" href="theme/styles/chart.css" />
<script type="text/javascript" src="theme/scripts/loader.js"></script>
<script src="jquery-3.5.1.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap" rel="stylesheet">
<script type="text/javascript">
<!--
//<![CDATA[
(function () {
	google.charts.load('current', {packages:['timeline', 'corechart', 'line']});
})();

// CHART PRINTING

function PrintDiv() {
	var originalContents = document.createElement('div');
	originalContents.setAttribute('id', 'original-contents');
	var printContents = document.getElementById('printdivcontent').innerHTML;
	originalContents.setAttribute('style', 'background:#fff;display:block;position:fixed;width:100%;height:100%;padding:0;margin:0;');
	originalContents.innerHTML = printContents;
	if (typeof drawChart == 'function') {
	drawChart();
	}
	window.print();
	originalContents.remove();
};
//]]>
//-->
</script>
</head>
<body style="background:#fff !important;padding:0 !important;margin:0 !important;box-sizing:border-box;">
<?php

if (!isset($_POST['machine']) || !is_array($_POST['machine'])) {
	$_POST['machine'] = '';
}

$selectedType = ''; //default type

$types = [];
$types[0] = 'Натоварване\Въртене';
$types[1] = 'Статуси по проценти';
$types[2] = 'Статуси по времетраене';
$types[3] = 'Списък с аларми';

if (isset($_POST['type']) && is_string($_POST['type']) && !empty($types[$_POST['type']])) {
		$selectedType = $_POST['type'];
} else {
	echo '<h1 style="font-family:Manrope;font-weight:400;padding:100px 0 0 0;text-align:center;">Моля,<br /> изберете параметри за да започнете.</h1>';
}

$currentDateTimeObj = new DateTime('now', null);

if (isset($_POST['start-date'])) {
	if (is_string($_POST['start-date'])) {
		$startDateTimeObj = DateTime::createFromFormat('Y-m-d', $_POST['start-date'], null);
		
		if (!$startDateTimeObj) {
			$_POST['start-date'] = null;
		}
	} else {
		$_POST['start-date'] = null;
	}
}

if (isset($_POST['end-date'])) {
	if (is_string($_POST['end-date'])) {
		$endDateTimeObj = DateTime::createFromFormat('Y-m-d', $_POST['end-date'], null);
		
		if (!$endDateTimeObj) {
			$_POST['end-date'] = null;
		}
	} else {
		$_POST['end-date'] = null;
	}
}

if (!isset($_POST['start-date'])) {
	$startDateTimeObj = $currentDateTimeObj;
}

if (!isset($_POST['end-date'])) {
	$endDateTimeObj = $currentDateTimeObj;
}

if (!isset($_POST['start-time'])) {
	$_POST['start-time'] = '00:00';
}

if (!isset($_POST['end-time'])) {
	date_default_timezone_set("Europe/Sofia");
	$_POST['end-time'] = date("H:i");
}

$server = 'CPC-MONITOR-PC\CPC_MSSQL';
$options = [];
$options['CharacterSet'] = 'UTF-8'; //charset
$options['ConnectionPooling'] = 0; //true or false
$options['Database'] = 'CPC_MONITORING'; //database name
$options['Uid'] = 'sa'; //username
$options['PWD'] = '7601'; //password

$connection = sqlsrv_connect($server, $options);

if (!$connection) {
	echo sqlsrv_errors(SQLSRV_ERR_ALL);
	exit;
}
?>

<div class="appChartForm">

<form action="chart.php" method="post" autocomplete="on" rel="next">

<div class="form-elements">

<div class="form-column-inputs">

<select id="machine" name="machine[]" style="height:200px;" multiple>
<?php
$sql = 'SELECT MachineName';
$sql .= ' FROM CNCStatusData';
$sql .= ' ORDER BY MachineName ASC';
//$sql .= ' OFFSET 0 ROWS FETCH NEXT 255 ROWS ONLY';
$parameteres = [];
$options = [];
$options['Scrollable'] = 'buffered';

$statement = sqlsrv_query($connection, $sql, $parameteres, $options);

if ($statement) {
	if (sqlsrv_has_rows($statement)) {
		while ($row = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_NEXT)) { //get row by row
			if (!isset($row['MachineName'])) {
				$row['MachineName'] = '???';
			}
			
			echo '<option value="'.htmlspecialchars($row['MachineName']).'"';
			if (isset($_POST['machine']) && is_array($_POST['machine'])) {
				if (in_array(''.$row['MachineName'].'', $_POST['machine'])) {
					echo ' selected="selected"';
				}
			}
			echo '>'.htmlspecialchars($row['MachineName']).'</option>';
		}
	}

	sqlsrv_free_stmt($statement);
}
?>
</select>

</div>

<div class="padding1_5"></div>

<div class="form-column-inputs">

<select id="type" name="type">
<?php
if (isset($types) && is_array($types) && count($types) > 0) {
	foreach ($types as $k => $v) {
		echo '<option value="'.htmlspecialchars($k).'"';
		if ($selectedType === (string) $k) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($v).'</option>';
	}
}
?>
</select>

</div>
<div class="padding1_5"></div>
<div class="form-column-inputs">

<?php
echo '<input id="start-date" type="date" name="start-date" min="2000-01-01" max="2030-01-01" value="';
echo htmlspecialchars($startDateTimeObj->format('Y-m-d'));
echo '" />';
?>

</div>
<div class="padding1_5"></div>
<div class="form-column-inputs">

<?php
if (isset($_POST['start-time']) && is_string($_POST['start-time'])) {
echo '<input type="time" id="startTime" name="start-time" value="'.$_POST['start-time'].'" min="00:00" max="24:00" required>';
} else {
echo '<input type="time" id="startTime" name="start-time" value="00:00" min="00:00" max="24:00" required>';
}
?>

</div>

<div class="padding1_5"></div>
<div class="form-column-inputs">

<?php
echo '<input id="end-date" type="date" name="end-date" min="2000-01-01" max="2030-01-01" value="';
echo htmlspecialchars($endDateTimeObj->format('Y-m-d'));
echo '" />';
?>

</div>
<div class="padding1_5"></div>
<div class="form-column-inputs">

<?php
if (isset($_POST['end-time']) && is_string($_POST['end-time'])) {
echo '<input type="time" id="endTime" name="end-time" value="'.$_POST['end-time'].'" min="00:00" max="23:59" required>';
} else {
echo '<input type="time" id="endTime" name="end-time" value="23:59" min="00:00" max="24:00" required>';
}
?>

</div>

<div class="padding1_5"></div>

<div class="form-column-buttons">
<label></label>
<input type="submit" value="Покажи" />
</div>
</form>
</div>

</div>

<div id="printdivcontent" style="width:100%;background:#fff;margin:0;padding:0;">
<?php

if ($selectedType === '1') { //pie
	$chartData = getChartingData($connection, $_POST['machine'], $startDateTimeObj->format('Y-m-d'), $endDateTimeObj->format('Y-m-d'), $_POST['start-time'],$_POST['end-time']);

	if (count($chartData) > 0) {
		echo '<h4>';
		foreach ($_POST['machine'] as $mcnames) {
			echo '<span style="margin:0 20px 0 0;">'.$mcnames.'</span>';
		}
		echo '</h4>';
		echo '<h4>ОТ '.$startDateTimeObj->format('Y-m-d').' ДО '.$endDateTimeObj->format('Y-m-d').'</h4>';
		echo '<script type="text/javascript">';
		echo 'google.charts.setOnLoadCallback(drawChart);';
		echo 'function drawChart() {';
		echo 'var data = google.visualization.arrayToDataTable([';
		echo '[\'NC Mode\', \'%\']';

		foreach ($chartData as $k => $v) {
			echo ', [\''.$k.'\', '.$v.']';
		}

		echo ']);';
		echo 'var options = {';
		echo 'colors: [';
		
		foreach ($chartData as $k => $v) {
		$statuscolor = $k;
		switch ($k) {
		case 'Offline':
		$statuscolor = '#dddddd';
		break;
		case 'Auto':
		$statuscolor = '#4daf4a';
		break;
		case 'Idling':
		$statuscolor = '#ebc934';
		break;
		case 'Setup':
		$statuscolor = '#3399ff';
		break;
		case 'Alarm':
		$statuscolor = '#ca3433';
		break;
		default:
		$statuscolor = 'INVALID';
		}
		
		echo '\''.$statuscolor.'\', ';
		
		}
		
		echo '],';
		echo 'legend: {position: \'labeled\'},';
		echo 'title: \'\',';
		echo 'chartArea:{left:0,top:0,width:\'100%\',height:\'100%\'},';
		echo 'is3D: false';
		echo '};';
		echo 'var chart = new google.visualization.PieChart(document.getElementById(\'chart1\'));';
		echo 'chart.draw(data, options);';
		echo '}';
		echo 'window.onresize = drawChart;';
		echo '</script>';
		echo '<div id="chart1" style="background:#fff;width: 100%; min-height:450px; box-sizing:border-box;"></div>';
		echo '';
}
} else if ($selectedType === '2') { //timeline
	$chartData = getChartingData2($connection, $_POST['machine'], $startDateTimeObj->format('Y-m-d'), $endDateTimeObj->format('Y-m-d'), $_POST['start-time'], $_POST['end-time']);

	if (count($chartData) > 0) {
		foreach ($chartData as $k => $v) {

			echo '<h4>'.$k.'</h4>';
			
			foreach ($v as $k2 => $v2) {
				
				$randomid = rand(1, 1500);
			
				echo '<h4>'.$k2.'</h4> <br />';
				
				echo '<script type="text/javascript">';
				echo 'google.charts.setOnLoadCallback(drawChart);';
				echo 'function drawChart() {';
				echo 'var chart = new google.visualization.Timeline(document.getElementById(\'chart2-'.$randomid.'\'));';
				echo 'var dataTable = new google.visualization.DataTable();';
				echo 'dataTable.addColumn({ type: \'string\', id: \'Role\' });';
				echo 'dataTable.addColumn({ type: \'string\', id: \'Name\' });';
				echo 'dataTable.addColumn({ type: \'string\', id: \'style\', role: \'style\' });';
				echo 'dataTable.addColumn({ type: \'date\', id: \'Start\' });';
				echo 'dataTable.addColumn({ type: \'date\', id: \'End\' });';
				echo 'dataTable.addRows([';
				
				foreach ($v2 as $k3 => $v3) {
						
				$statuscolor = $v3[0];
				switch ($v3[0]) {
				case 'Offline':
				$statuscolor = '#dddddd';
				break;
				case 'Auto':
				$statuscolor = '#4daf4a';
				break;
				case 'Idling':
				$statuscolor = '#ebc934';
				break;
				case 'Setup':
				$statuscolor = '#3399ff';
				break;
				case 'Alarm':
				$statuscolor = '#ca3433';
				break;
				default:
				$v3[0] = 'INVALID';
				}
				if ($k3 > 0) {
					echo ', ';
				}
				echo '[\'-\', \''.$v3[0].'\', \''.$statuscolor.'\', new Date('.$v3[1].', '.$v3[2].', '.$v3[3].', '.$v3[4].', '.$v3[5].', '.$v3[6].')';
				echo ', new Date('.$v3[7].', '.$v3[8].', '.$v3[9].', '.$v3[10].', '.$v3[11].', '.$v3[12].')]';
			}
		echo ']);';
		echo 'var options = {';
		echo 'colors: [\'#cbb69d\', \'#603913\', \'#c69c6e\'],';
		echo '};';
		echo 'chart.draw(dataTable, options);';
		echo '};';
		echo 'window.onresize = drawChart;';
		echo '</script>';
		echo '<div id="chart2-'.$randomid.'" style="width:100%;height:100px;"></div>';
			
			}
	}
	}
} else if ($selectedType === '3') { //Alarm list

	$chartData = getChartingData3($connection, $_POST['machine'], $startDateTimeObj->format('Y-m-d'), $endDateTimeObj->format('Y-m-d'), $_POST['start-time'], $_POST['end-time']);
	
	//var_dump($chartData);
	
	if (count($chartData) > 0) {
	foreach ($chartData as $k => $v) {
		
		if (!empty($v[0]) && !empty($v[1])) {
		
		echo '<h4>'.$k.'</h4>';
		echo '<table id="customers" style="margin:0 0 30px 0;">';
		echo '<tr><th>Начало</th><th>Край</th><th>Код на алармата</th><th>Съобщение</th></tr>';
		
		foreach ($v as $k2 => $v2) {
			if (empty($v2[0]) && empty($v2[1]) && empty($v2[4])) {
			
			} else {
			
			switch ($v2[2]) {
			case '16711680':
			$v2[2] = 'blue';
			break;
			case '255':
			$v2[2] = 'red';
			break;
			case '0':
			$v2[2] = 'black';
			break;
			default:
			$v2[2] = ' | Invalid';
			}
			
			switch ($v2[3]) {
			case '16777215':
			$v2[3] = 'white';
			break;
			case '65535':
			$v2[3] = 'yellow';
			break;
			case '255':
			$v2[3] = 'red';
			break;
			case '0':
			$v2[3] = 'white';
			break;
			default:
			$v2[3] = ' | Invalid';
			}
			
			if (empty($v2[1])) {
			$v2[1] = 'Липсва информация.';
			} else {
			$v2[1] = $v2[1];
			}
			if (empty($v2[4])) {
			$v2[4] = 'Липсва информация.';
			} else {
			$v2[4] = $v2[4];
			}
			
			echo '<tr><td>'.$v2[5].'-'.$v2[6].'-'.$v2[7].' '.$v2[8].':'.$v2[9].':'.$v2[10].'</td><td>'.$v2[11].'-'.$v2[12].'-'.$v2[13].' '.$v2[14].':'.$v2[15].':'.$v2[16].'</td><td>'.$v2[1].'</td><td style="border:1px solid '.$v2[2].' !important;background:'.$v2[2].';color:'.$v2[3].' !important;">'.$v2[4].'</td></tr>';
		}
		}
		
		echo '</table>';
		}
		}
		
	}

} else if ($selectedType === '0') {
	$chartData = getChartingData4($connection, $_POST['machine'], $startDateTimeObj->format('Y-m-d'), $endDateTimeObj->format('Y-m-d'), $_POST['start-time'], $_POST['end-time']);

	if (count($chartData) > 0) {
		foreach ($chartData as $k => $v) {

			echo '<h4>'.$k.'</h4>';
			
			foreach ($v as $k2 => $v2) {
				
				$randomid = rand(1, 1500);
			
				echo '<h4>'.$k2.'</h4> <br />';
				echo '<script type="text/javascript">';
				//echo 'google.charts.load(\'current\', {packages:[\'corechart\']});';
				echo 'google.charts.setOnLoadCallback(drawChart);';
				echo 'function drawChart() {';
				echo 'var data = new google.visualization.DataTable();';
				echo 'data.addColumn(\'datetime\', \'Time of Day\');';
				echo 'data.addColumn(\'number\', \'Spindle load\');';
				echo 'data.addColumn(\'number\', \'Spindle speed\');';
				echo 'data.addRows([';
				foreach ($v2 as $k3 => $v3) {
					echo '[new Date('.$v3[2].', '.$v3[3].', '.$v3[4].', '.$v3[5].', '.$v3[6].', '.$v3[7].'), '.$v3[0].', '.$v3[1].'], ';
				}
				echo ']);';
				echo 'var formatter = new google.visualization.DateFormat({formatType: \'long\'});';
				echo 'var options = {';
				echo 'series: {';
				echo '0: {targetAxisIndex: 0,lineWidth: 1},';
				echo '1: {targetAxisIndex: 1,lineWidth: 1}';
				echo '},legend: { position: \'bottom\' }';
				echo '};';
				echo 'var chart = new google.visualization.LineChart(document.getElementById(\'chart3-'.$randomid.'\'));';
				echo 'chart.draw(data, options);';
				echo '};';
				echo 'window.onresize = drawChart;';
				echo '</script>';
				echo '<div id="chart3-'.$randomid.'" style="min-height:300px; margin:0 auto 30px auto;padding:0;"></div>';
			}
		}
} else {

	echo '<h1 style="font-family:Manrope;font-weight:400;padding:100px 0 0 0;text-align:center;">Няма данни за избраният период.</h1>';

}
}

sqlsrv_close($connection); //close the connection
?>
</div>
</body>
</html>