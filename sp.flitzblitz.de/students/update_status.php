<?php
// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 12*60*60);
// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(12*60*60);
session_start();
require_once '../dbmanager.php';

if (DEBUG) {
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.":\$_SESSION:\n", $_SESSION);
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.":\$_REQUEST:\n", $_REQUEST);
}

if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking location...');
if (isset($_REQUEST['location'])) {
	// check cgi variable 'location' for validity
	$rooms      = $dbh->query('SELECT `number` FROM `rooms`')->fetchAll(PDO::FETCH_COLUMN); 	// get all valid room names
	$key        = array_search($_REQUEST['location'], $rooms); 	// see if we have such a name
	if ($key === false) {
// 		header('Location: index.php');							// invalid location given --> go back and choose new location.
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':no location...');
		echo 'no location';
		exit(0);
	}
	$location   = $rooms[$key]; 								// if not, first one will be set automatically. smart enuf :)
	$_SESSION['location'] = $location;							// register location with session
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':got location from request: '.$location);
} else if (isset($_SESSION['location'])) {
	$location = $_SESSION['location'];
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':got location from session: '.$location);
} else {
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':no location...');
	echo 'no location';
	exit(0);
// 	throw new Exception('no location in ajax update_status.php');
	// 	header('Location: index.php');
	// 	exit(0);
}

if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking state...');
if (isset($_REQUEST['state'])) {
	$valid_states = array('false', 'true');
	$key = array_search($_REQUEST['state'], $valid_states);
	if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':key: ', $key);
	if ($key === false) {
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':no state.');
		echo 'invalid state';
		exit(0);
	} else {
		if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':valid_states[key]: ', $valid_states[$key]);
// 		$state = $valid_states[$key] ? 1 : 0;
		$state = $key;
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':got state: '.$state);
	}
}

if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking student...');
if (isset($_REQUEST['student'])) {
	$student = $_REQUEST['student'];
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':got student: '.$student);
} else {
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':no student.');
	echo 'no student';
	exit(0);
}

check_logins($location);

if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking for active exercise in room: '.$location);
$sth = $dbh->prepare('SELECT `active_exercise` FROM `rooms` WHERE `number` = :number');
$sth->execute(array('number' => $location));
$active_exercise = $sth->fetchColumn();
if ($active_exercise) {
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':got active exercise: '.$active_exercise);
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':setting state to ['.$state.'] for ['.$student.'] in exercise ['.$active_exercise.']');
	$sth = $dbh->prepare("UPDATE `states` SET `finished` = :status WHERE `student_id` = :student AND `exercise_id` = :active_exercise");
	$stmt_success = $sth->execute(array('student' => $student, 'status' => $state, 'active_exercise' => $active_exercise));
// 		if (DEBUG) {
// 			ob_start();
// 			$sth->debugDumpParams();
// 			$contents = ob_get_contents(); // put the buffer into a variable
// 			ob_end_clean();                // end capture
// 			error_log($contents);          // log contents of the result of var_dump($object)
// 		}
// 		$sth->bindParam(':student', $student, PDO::PARAM_INT);
// 		$sth->bindParam(':status', $state, PDO::PARAM_INT);
		
// 	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.": \$student: ". $student);
// 	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.": \$state: ". $state);
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.": \$stmt_success: $stmt_success");
}
echo 'döner';