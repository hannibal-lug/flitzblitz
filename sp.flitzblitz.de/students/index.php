<?php
// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 12*60*60);
// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(12*60*60);
session_start();

require_once '../dbmanager.php';

define('GET_SEAT', 'chooseplace.php', true);
$id = session_id();

if (DEBUG) {
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.":\$_SESSION:\n", $_SESSION);
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.":\$_REQUEST:\n", $_REQUEST);
}

// validate given location
if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking cgi location...');
$location = get_request_location();
if ($location) {
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':got location. diverting to chooseplace.php');
	header('Location: chooseplace.php?location='.$location);
	exit(0);
}
?>

<html lang="de" dir="ltr" class="redesign no-js" data-ffo-opensans="false" data-ffo-opensanslight="false">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="../css3clock.css" />
		<link rel="stylesheet" type="text/css" href="students.css" />
	</head>

	<body>
		<h1> Wo du sitze?</h1>
		<?php
			if (DEBUG) {
				$sql = 'SELECT COUNT(*) FROM `rooms`';
				$rowcount = $dbh->query($sql)->fetchColumn();
				error_log(basename(__FILE__).':'.__LINE__.':`rooms` row count: ' . $rowcount);
			}
			// connect to db and get list of rooms.
			$sql = 'SELECT `name`, `number` FROM `rooms`';
			$stmt = $dbh->query($sql);							// no fetch?
			if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':`rooms`: ', $stmt);
			foreach ($stmt as $row) {
				if (DEBUG) { var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':room: ', $row); }
				echo '<a href="chooseplace.php?location=' . $row['number'] . '">' . $row['name'] . "</a><br>\n";
			}
		?>
	</body>
</html>