<!DOCTYPE html>
<html lang="de" dir="ltr" class="redesign no-js" data-ffo-opensans="false" data-ffo-opensanslight="false">
<head>
	<meta charset="utf-8"/>
	<!-- <meta http-equiv="refresh" content="10" > -->
	<link rel="stylesheet" type="text/css" href="button.css" />
	<script type="text/javascript" defer="defer" src="button.js">
// 		window.document.onload = check_status;
	</script>
	<!-- 
	<script type="text/javascript" defer="defer" src="button.js">
		window.document.onload = check_status;
	</script>
	 --> 
	<!-- 
	<script>
		// window.onload = check_status();
	</script>
	<script type="text/javascript">
// 		window.document.onload = check_status;
	</script>
	-->
</head>
<body bgcolor=#A61A1A onload="check_status();">
	<div class="container">
		<?php
		// server should keep session data for AT LEAST 12 hour
		ini_set('session.gc_maxlifetime', 12*60*60);
		// each client should remember their session id for EXACTLY 12 hour
		session_set_cookie_params(12*60*60);
		session_start();
		
		include '../dbmanager.php';
// 		define('START', 'index.php', true);
		
		if (isset($_REQUEST['location'])) {
			// check cgi variable 'location' for validity
			$rooms      = $dbh->query('SELECT `number` FROM `rooms`')->fetchAll(PDO::FETCH_COLUMN); 	// get all valid room names
			$key        = array_search($_REQUEST['location'], $rooms); 				// see if location matches
// 			if ($key === false) {													// invalid location given --> go back and choose new location.
// 				header('Location: '.START);
// 				exit(0);
// 			}																		// would have to access iframe.parent.location
			$location   = $rooms[$key]; 											// if not, first one will be set automatically. smart enuf :)
		} else if (isset($_SESSION['location'])) $location = $_SESSION['location'];
		
		if (DEBUG) {
			var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':$_SESSION: ', $_SESSION);
			var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.'$_REQUEST: ', $_REQUEST);
			error_log(basename(__FILE__).':'.__LINE__.'location: '. $location);
		}
		
		$ip = getenv('REMOTE_ADDR');
		$id = session_id();
		
		// jetzt den ready status in DB eintragen
		// Create connection
		$conn = new mysqli($servername, $username, $password,$dbname);
		
		// Check connection
		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
		}
		//echo "Connected successfully";
		
/*
		// In Datebank schauen ob Session schon angelegt ist
		$sql = "SELECT id FROM students where session_id =\"$id\"";
		$exercise_id = $conn->query($sql);
		if ( $exercise_id->num_rows > 0) {
			// User existiert -> update auf ready
			$row = mysqli_fetch_assoc($exercise_id);
			$student_id= $row["id"];
// 		    $sql = "update states set finished='0' where student=$student_id";
			$sql = "SELECT `student` FROM `states` WHERE `student` = $student_id";
// 			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.": \$student_id: ". $student_id);
// 			echo '<input type="hidden" name="student" value="'.$student_id.'">';
			$_SESSION['student_id'] = $student_id;
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.":\$_SESSION['student_id']: ". $_SESSION['student_id']);
			$query_result = $conn->query($sql);
// 			if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__."SELECT `student` FROM `states` WHERE `student`: ", $$query_result);
			if ($query_result) {
		    //echo "New record created successfully";
				$student_id= mysqli_insert_id($conn);
				if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.":mysql_insert_id: ". $student_id);
			} else {
		    echo "Error: " . $sql . "<br>" . $conn->error;
			}
// 			if (DEBUG) error_log(__FILE__.':'.__LINE__.":\$student_id: $student_id");
		}
*/
		?>
	<?php 
		/* get button state:
		 * 	1. no exercise --> disabled
		 * 	2. new exercise --> busy
		 * 	3. old exercise --> last state.
		 */
	?>
	<div id="button">
<!--	
		<form name="form1" action="update_status.php" method="post">
-->
		<form name="form1" action="" method="post">
			<input type=hidden name=id value="<?php echo session_id();?>">
			<input type=hidden name=status value="ready">
			<input type="hidden" name="student" id="student" value="<?php
				echo session_id();
			?>" data-location=<?php echo $location; ?>>
			<input type="hidden" id="state" value="<?php // echo $_SESSION['state']?>">
			
			<label class="switch switch-green switch-grey">
<!--			
				<input id=switch1 type="checkbox" class="switch-input" onchange="toggle_switch();" onclick="update_state();">
				<input id=switch1 type="checkbox" class="switch-input" onchange="update_state();">
-->
				<input id=switch1 type="checkbox" class="switch-input" onchange="toggle_switch();">
				<span class="switch-label" data-on="fertig" data-off="in arbeit"></span>
				<span class="switch-handle"></span>
			</label><br/><br/>
			
			<div id="frigging_ajax">
			</div>
<!-- 			
			Bitte schalten sie auf gr&uuml;n, <br/>wenn ihre Aufgabe erledigt ist.
-->
		</form>
		<!-- 
			<button type="submit" onclick="switch_switch();">disable switch</button>
			<button type="submit" onclick="check_status();">check status</button>
		 -->
	</div>
<?php
	// returns the next state and registers it with the session.
	function session_get_next_state() {
		if (isset($_SESSION['state'])) {
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':session_get_next_state:$_SESSION[\'state\']: '. $_SESSION['state']);
			$state = $_SESSION['state'];
			switch ($state) {
				case 0:
					$state = $_SESSION['state'] = 1; 
					if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':switch($state):case 0:next $_SESSION[\'state\']: '. $_SESSION['state']);
					break;
				case 1:
					$state = $_SESSION['state'] = 0;
					if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':switch($state):case 1:next $_SESSION[\'state\']: '. $_SESSION['state']);
					break;
			}
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':session_get_next_state():return $state: '. $state);
			return $state;
		}
	}
?>
	</div>
<?php
	if (isset($_REQUEST['state'])) {
		$state = $_REQUEST['state'];
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.":\$_REQUEST['state']: $state");
		echo <<<SCRIPT
<script type="text/javascript">
	set_button_state('$state');
</script>
SCRIPT;
	}
?>
</body>
</html>
