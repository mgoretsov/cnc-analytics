<?php

// Charting data retrieve for each chart type

//Pie chart

function getChartingData($connection, $machine, $startDate, $endDate, $startTime, $endTime): array {
	$sql = 'SELECT NCStatus,AlarmFlag, DATEDIFF(MINUTE, StartDate, EndDate) as Period';
	$sql .= ' FROM CNCChart_data';
	$sql .= ' WHERE MachineName IN (';
	$sql .= '\'\', ';
	if (isset($machine) && is_array($machine)) {
	foreach ($machine as $mcname_group){
    $sql .= '\''.$mcname_group.'\', ';
	}
	}
	$sql .= '\'\'';
	$sql .= ')';
	$sql .= ' and StartDate >= \''.$startDate.' '.$startTime.':00\'';
	$sql .= ' and EndDate <= \''.$endDate.' '.$endTime.':59\'';
	$sql .= ' ORDER BY StartDate ASC';
	$parameters = [];
	$options = [];
	$options['Scrollable'] = 'buffered';

	$statement = sqlsrv_query($connection, $sql, $parameters, $options);

	if (!$statement) {
		return [];
	}

	if (!sqlsrv_has_rows($statement)) {
		return [];
	}
	
	$rows = [];

	while ($row = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_NEXT)) { //get row by row
		if (!isset($row['NCStatus'])) {
			$row['NCStatus'] = 0;
		}
		
		$row['NCStatus'] .= $row['AlarmFlag'];
		
		$row['NCStatus'] = convertNCStatusValue($row['NCStatus']);

		$rows[] = [$row['NCStatus'], $row['Period'], $row['AlarmFlag']]; //status, start date
		
	}
	
	sqlsrv_free_stmt($statement);
	
	$result = [];
	
	foreach ($rows as $k => $v) {
		if (!isset($result[$v[0]])) {
			$result[$v[0]] = 0;
		}
		
		if (isset($rows[$k])) {
			$result[$v[0]] += $rows[$k + 0][0] - $v[1];
		}
	}

	//find the biggest value
	
	$max = 0;
	
	foreach ($result as $v) {
		$max += $v;
	}
	
	//convert to %
	
	$coefficient = 100 / $max;
	
	foreach ($result as $k => $v) {
		$result[$k] = $coefficient * $v;
	}
	
	return $result;
}

// Timeline chart

function getChartingData2($connection, $machine, $startDate, $endDate, $startTime, $endTime): array {
	
	$sql = 'SELECT NCStatus, AlarmFlag, YEAR(StartDate) AS ys, MONTH(StartDate) AS ms, DAY(StartDate) AS ds, DATEPART(hour, StartDate) AS hs, DATEPART(minute, StartDate) AS ns, DATEPART(second, StartDate) AS ss, YEAR(EndDate) AS ye, MONTH(EndDate) AS me, DAY(EndDate) AS de, DATEPART(hour, EndDate) AS he, DATEPART(minute, EndDate) AS ne, DATEPART(second, EndDate) AS se, MachineName';
	$sql .= ' FROM CNCChart_data';
	$sql .= ' WHERE MachineName IN (';
	$sql .= '\'\', ';
	if (isset($machine) && is_array($machine)) {
	foreach ($machine as $mcname_group){
    $sql .= '\''.$mcname_group.'\', ';
	}
	}
	$sql .= '\'\'';
	$sql .= ')';
	$sql .= ' and StartDate >= \''.$startDate.' '.$startTime.':00\'';
	$sql .= ' and EndDate <= \''.$endDate.' '.$endTime.':59\'';
	$sql .= ' ORDER BY StartDate ASC';
	$parameteres = [];
	$options = [];
	$options['Scrollable'] = 'buffered';

	$statement = sqlsrv_query($connection, $sql, $parameteres, $options);

	if (!$statement) {
		var_dump(sqlsrv_errors(SQLSRV_ERR_ALL));
		return [];
	}

	if (!sqlsrv_has_rows($statement)) {
		return [];
	}
	
	$rows = [];

	while ($row = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_NEXT)) { //get row by row
		if (!isset($row['NCStatus'])) {
			$row['NCStatus'] = 0;
		}

		$row['NCStatus'] .= $row['AlarmFlag'];
		
		$row['NCStatus'] = convertNCStatusValue($row['NCStatus']);

		$rows[] = [$row['NCStatus'], $row['ys'], $row['ms'], $row['ds'], $row['hs'], $row['ns'], $row['ss'], $row['ye'], $row['me'], $row['de'], $row['he'], $row['ne'], $row['se'], $row['MachineName']];
	}
	
	sqlsrv_free_stmt($statement);
	
	$result = [];
	
	foreach ($rows as $k => $v) {

		$row = [$v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6], $v[7], $v[8], $v[9], $v[10], $v[11], $v[12]];
		
		$y = $v[13];
		
		$x = $v[1].'-'.$v[2].'-'.$v[3];
		
		if (!isset($result[$y][$x])) {
			$result[$y][$x] = [];
		}
	
		$result[$y][$x][] = $row;
	}
	
	return $result;
}

// Alarm list chart

function getChartingData3($connection, $machine, $startDate, $endDate): array {
	
	$sql = 'SELECT AlarmFlag, AlarmNo, BackColor, ForeColor, AlarmMess, YEAR(StartDate) AS ys, MONTH(StartDate) AS ms, DAY(StartDate) AS ds, DATEPART(hour, StartDate) AS hs, DATEPART(minute, StartDate) AS ns, DATEPART(second, StartDate) AS ss, YEAR(EndDate) AS ye, MONTH(EndDate) AS me, DAY(EndDate) AS de, DATEPART(hour, EndDate) AS he, DATEPART(minute, EndDate) AS ne, DATEPART(second, EndDate) AS se';
	$sql .= ' FROM CNCChart_data';
	$sql .= ' WHERE MachineName = \''.$machine.'\'';
	$sql .= ' and StartDate >= \''.$startDate.' 00:00:00\'';
	$sql .= ' and EndDate <= \''.$endDate.' 23:59:59\'';
	$sql .= ' ORDER BY StartDate ASC';
	$parameteres = [];
	$options = [];
	$options['Scrollable'] = 'buffered';

	$statement = sqlsrv_query($connection, $sql, $parameteres, $options);

	if (!$statement) {
		var_dump(sqlsrv_errors(SQLSRV_ERR_ALL));
		return [];
	}

	if (!sqlsrv_has_rows($statement)) {
		return [];
	}
	
	$rows = [];

	while ($row = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_NEXT)) { //get row by row

		$rows[] = [$row['AlarmFlag'], $row['AlarmNo'], $row['BackColor'], $row['ForeColor'], $row['AlarmMess'], $row['ys'], $row['ms'], $row['ds'], $row['hs'], $row['ns'], $row['ss'], $row['ye'], $row['me'], $row['de'], $row['he'], $row['ne'], $row['se']];
	}
	
	sqlsrv_free_stmt($statement);

	$result = [];
	
	foreach ($rows as $k => $v) {

		$row = [$v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6], $v[7], $v[8], $v[9], $v[10], $v[11], $v[12], $v[13], $v[14], $v[15], $v[16] ];	
	
		$x = $v[5].'-'.$v[6].'-'.$v[7];
		
		if (!isset($result[$x])) {
			$result[$x] = [];
		}
	
		$result[$x][] = $row;
	}
	
	return $result;
}

// Spindle load/speed chart

function getChartingData4($connection, $machine, $startDate, $endDate, $startTime, $endTime): array {
	
	$sql = 'SELECT SpindleLoad1, SpindleSpeed1, YEAR(LogDate) AS y, MONTH(LogDate) AS m, DAY(LogDate) AS d, DATEPART(hour, LogDate) AS h, DATEPART(minute, LogDate) AS n, DATEPART(second, LogDate) AS s, MachineName';
	$sql .= ' FROM CNCChart_RunningData';
	$sql .= ' WHERE MachineName IN (';
	$sql .= '\'\', ';
	if (isset($machine) && is_array($machine)) {
		foreach ($machine as $mcname_group){
			$sql .= '\''.$mcname_group.'\', ';
		}
	}
	$sql .= '\'\'';
	$sql .= ')';
	$sql .= ' and LogDate >= \''.$startDate.' 00:00:00\'';
	$sql .= ' and LogDate <= \''.$endDate.' 23:59:59\'';
	$sql .= ' ORDER BY LogDate ASC';
	$parameteres = [];
	$options = [];
	$options['Scrollable'] = 'buffered';

	$statement = sqlsrv_query($connection, $sql, $parameteres, $options);

	if (!$statement) {
		var_dump(sqlsrv_errors(SQLSRV_ERR_ALL));
		return [];
	}
	if (!sqlsrv_has_rows($statement)) {
		return [];
	}

	while ($row = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_NEXT)) { //get row by row
		if (!isset($row['SpindleLoad1'])) {
			$row['SpindleLoad1'] = 0;
		}
		if (!isset($row['SpindleSpeed1'])) {
			$row['SpindleSpeed1'] = 0;
		}
		$rows[] = [$row['SpindleLoad1'], $row['SpindleSpeed1'], $row['y'], $row['m'], $row['d'], $row['h'], $row['n'], $row['s'], $row['MachineName']]; //status, start date
	}
	
	sqlsrv_free_stmt($statement);
	
	$result = [];
	
	foreach ($rows as $k => $v) {
		$row = [$v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6], $v[7]];
		$y = $v[8];
		$x = $v[2].'-'.$v[3].'-'.$v[4];
		
		if (!isset($result[$y][$x])) {
			$result[$y][$x] = [];
		}
	
		$result[$y][$x][] = $row;
	}
	
	return $result;
}

// Machine images based on name and model

function mcImageSrc($mctitle) {
	$match = preg_match('/STUDER-S31/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/studer-s31.png';
	}
	
	$match = preg_match('/VRX500-II/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/vrx500II.png';
	}
	
	$match = preg_match('/VTC560/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/VTC_560.png';
	}
	
	$match = preg_match('/HCN5000III/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/PALETECH.png';
	}
	
	$match = preg_match('/HCN-4000-SMOOTH/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/HCN4000SMOOTH.png';
	}
	
	$match = preg_match('/HCN4000-SMOOTH/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/HCN4000SMOOTH.png';
	}
	
	$match = preg_match('/NHP4000/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/NHP4000.png';
	}
	
	$match = preg_match('/TT1800MS/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/TT1800MS.png';
	}
	
	$match = preg_match('/GOODWAY-SW32/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/GOODWAYSW32.png';
	}
	
	$match = preg_match('/STUDER-S33/i', $mctitle);
	
	if (is_int($match) && $match > 0) {
		return 'images/STUDERS33.png';
	}
	
	return 'images/noimage.png';
}

// Convert NC Statuses from INT to STRING

function convertNCStatusValue($input) {
	if (!isset($input)) { //null check
		$input = -1;
	}

	if (!is_int($input)) { //convert to int if it's not
		$input = (int) $input;
	}

	$result = '';

	switch ($input) {
		case 00:
			$result = 'Offline';
		break;
		case 10:
			$result = 'Auto';
		break;
		case 20:
			$result = 'Idling';
		break;
		case 30:
			$result = 'Idling';
		break;
		case 40:
			$result = 'Setup';
		break;
		case 50:
			$result = 'Setup';
		break;
		case 60:
			$result = 'Setup';
		break;
		case 70:
			$result = 'Setup';
		break;
		case 01:
			$result = 'Offline';
		break;
		case 11:
			$result = 'Auto';
		break;
		case 21:
			$result = 'Alarm';
		break;
		case 31:
			$result = 'Alarm';
		break;
		case 41:
			$result = 'Setup';
		break;
		case 51:
			$result = 'Setup';
		break;
		case 61:
			$result = 'Setup';
		break;
		case 71:
			$result = 'Setup';
		break;
		default:
			$result = 'Invalid';
	}
	
	return $result;
}

// Convert NC Statuses to GRID color schematics needed
// for CSS Markup on Slideshow page

function NCStatusToGridColor($input) {
	if (!isset($input) && !is_string($input)) { //null check
		$input = -1;
	}

	$result = '';

	switch ($input) {
		case 'Offline':
		$result = 'poweroff';
		break;
		case 'Auto':
		$result = 'moperation';
		break;
		case 'Idling':
		$result = 'fhold';
		break;
		case 'Setup':
		$result = 'hjog';
		break;
		case 'Alarm':
		$result = 'alarm';
		break;
		default:
		$result = 'INVALID';
		}
	
	return $result;
}

// Convert NC Status INT to HEX values for charts

function convertNCStatusToHEX($input) {
	if (!isset($input) && !is_string($input)) { //null check
		$input = -1;
	}

	$result = '';
	
	switch ($input) {
		case 'Offline':
		$result = '#dddddd';
		break;
		case 'Auto':
		$result = '#4daf4a';
		break;
		case 'Idling':
		$result = '#ebc934';
		break;
		case 'Setup':
		$result = '#3399ff';
		break;
		case 'Alarm':
		$result = '#ca3433';
		break;
		default:
		$result = 'INVALID';
		}
	
	return $result;
}

// Getting slideshow data for visualization

function GetSlideshowData($data){
		
	$workshop = $_POST['workshop'];
		
	$workshops = []; //name, query
	$workshops[0] = ['110-1', '1101'];
	$workshops[1] = ['110-3', '1103'];
	$workshops[2] = ['200-1', '2001'];
	$workshops[3] = ['200-2', '2002'];
	$workshops[4] = ['300-1', '3001'];
	$workshops[5] = ['300-2', '3002'];
	$workshops[6] = ['300-3', '3003'];

	//check if the requested workshop exists

	if (isset($_POST['workshop'])) {
		if (!is_string($_POST['workshop']) || !isset($workshops[$_POST['workshop']])) {
			$_POST['workshop'] = null;
		}
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

	$columns = [];
	$columns[] = 'LogDate';
	$columns[] = 'MachineName';
	$columns[] = 'AlarmFlag';
	$columns[] = 'AlarmNo';
	$columns[] = 'MachineMode';
	$columns[] = 'NCStatus';
	$columns[] = 'AlarmMess';
	$columns[] = 'SpindleLoad1';
	$columns[] = 'SpindleSpeed1';
	$columns[] = 'ProgName';

	$sql = 'SELECT ';

	if (isset($columns) && is_array($columns) && count($columns) > 0) {
		foreach ($columns as $k => $v) {
			if ($k > 0) {
				$sql .= ', ';
			}

			$sql .= $v;
		}
	} else {
		$sql .= '*';
	}

	$sql .= ' FROM CNCStatusData';
	$sql .= ' WHERE MachineName LIKE \''.$workshops[$_POST['workshop']][1].'%\'';
	$sql .= ' ORDER BY MachineName ASC';

	$statement = sqlsrv_query($connection, $sql, [], ['Scrollable' => 'buffered']);

	if (!$statement) {
		echo sqlsrv_errors(SQLSRV_ERR_ALL);
		exit;
	}

	if (!sqlsrv_has_rows($statement)) {
		echo '<p>Липсва съдържание.</p>';
		exit;
	}

	//echo 'Total machines: '.sqlsrv_num_rows($statement).'<br />';

	$i = 0;
	$shopfloor = (isset($_POST['workshop'])) ? $workshops[$_POST['workshop']][1]  : 'all';

	//echo '<h2>ПРОИЗВОДСТВЕН ЦЕХ - '.$shopfloor.'</h2>';
	
	echo '<div id="row-'.$shopfloor.'">';
	
	while ($row = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_NEXT)) { //get row by row
		// Include alarm in NC status
		$row['NCStatus'] .= $row['AlarmFlag'];
		
		// Check for empty machine name
		if (empty(substr($row['MachineName'], 9))) {
			$row['MachineName'] .= '-Empty';
		}
				
		echo '<div class="row-'.substr($row['MachineName'], 5, 1).'">';
		//echo '<div id="w-'.$shopfloor.'-'.($i + 1).'" class="mc-box '.NCStatusToGridColor(convertNCStatusValue($row['NCStatus'])).'">';
		echo '<div onclick="showChart(\''.$row['MachineName'].'\', 1)" style="background:#ddd url(\''.mcImageSrc($row['MachineName']).'\') no-repeat center;background-size:contain;display:block;max-width:48px;width:48px;height:48px;border-radius:48px;margin:0 auto 0px auto;"></div>';
		echo ''.substr($row['MachineName'], 9).'';
		//echo '<div class="mc-status">'.convertNCStatusValue($row['NCStatus']).'</div>';
		echo '</div>';
		
		$i++;
	}
	echo '</div>';
	
	sqlsrv_free_stmt($statement);
	sqlsrv_close($connection); //close the connection
}

// Get momentary data for machines

function GetLiveData($data){
	
	if (isset($_POST['mcname']) && !empty($_POST['mcname'])) {
		
	$mcname = $_POST['mcname'];

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

	$columns = [];
	$columns[] = 'LogDate';
	$columns[] = 'MachineName';
	$columns[] = 'AlarmFlag';
	$columns[] = 'AlarmNo';
	$columns[] = 'MachineMode';
	$columns[] = 'NCStatus';
	$columns[] = 'AlarmMess';
	$columns[] = 'SpindleLoad1';
	$columns[] = 'SpindleSpeed1';
	$columns[] = 'ProgNo';

	$sql = 'SELECT ';

	if (isset($columns) && is_array($columns) && count($columns) > 0) {
		foreach ($columns as $k => $v) {
			if ($k > 0) {
				$sql .= ', ';
			}

			$sql .= $v;
		}
	} else {
		$sql .= '*';
	}

	$sql .= ' FROM CNCStatusData';
	$sql .= ' WHERE MachineName LIKE \''.$mcname.'%\'';
	$sql .= ' ORDER BY MachineName ASC';

	$statement = sqlsrv_query($connection, $sql, [], ['Scrollable' => 'buffered']);

	if (!$statement) {
		echo sqlsrv_errors(SQLSRV_ERR_ALL);
		exit;
	}

	if (!sqlsrv_has_rows($statement)) {
		echo '<p>Липсва съдържание.</p>';
		exit;
	}

	while ($row = sqlsrv_fetch_array($statement, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_NEXT)) {

		$row['NCStatus'] .= $row['AlarmFlag'];

		if ($row['MachineName'] == $mcname) {
		echo '<div style="background:transparent;padding:30px;border-radius:10px;">';
		echo '<p style="color:'.convertNCStatusToHEX(convertNCStatusValue($row['NCStatus'])).';font-size:14px;font-weight:bold;margin:0 0 5px 0;padding:0;">'.$row['MachineName'].'</p>';
		//echo 'ALARM_FLAG '.$row['AlarmFlag'].'<br />';
		//echo 'ALARM_NO '.$row['AlarmNo'].'<br />';
		echo '<p style="font-size:14px;margin:0;padding:0;">'.convertNCStatusValue($row['NCStatus']).'</p>';
		//echo 'ALARM_MESS '.$row['AlarmMess'].'<br />';
		if ($row['SpindleLoad1'] > 1 && $row['SpindleSpeed1'] > 1) {
		echo '<div style="display:block;width:100%;float:left;">';
		echo '<p style="display:inline-block;float:left;font-size:12px;margin:10px 25px 0 0;padding:0;">'.$row['SpindleLoad1'].'</p>';
		echo '<p style="display:inline-block;float:left;font-size:12px;margin:10px 0;padding:0;">'.$row['SpindleSpeed1'].'</p>';
		echo '</div>';
		}
		if (!empty($row['ProgNo'])) {
		echo '<p style="font-size:12px;margin:10px 0 0 0;padding:0;"><span style="display:block;font-weight:bold;">Избрана програма</span> '.$row['ProgNo'].'</p>';
		}
		echo '</div>';
		}
	}
	
	sqlsrv_free_stmt($statement);
	sqlsrv_close($connection);
	
	} else {
	
	return 'No machine specified!';
	
	}
}

function GetChartData($data){
	
}

if (isset($_POST['funcSelector']) && !empty($_POST['funcSelector'])) {
	
	switch ($_POST['funcSelector']) {
		case 0: echo 'No functions found.';
		break;
		case 1: echo GetLiveData($_POST['funcSelector']);
		break;
		case 2: echo GetSlideshowData($_POST['funcSelector']);
		break;
		case 3: echo GetChartData($_POST['funcSelector']);
		break;
		default: echo 'No functions found.';
	}
}

?>