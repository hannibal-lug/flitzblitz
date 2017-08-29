<?php
// check if we have an active exercise.

// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 12*60*60);
// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(12*60*60);
session_start();
require_once '../dbmanager.php';

// $DEBUG = false;
define('DEBUG_AJAX', false, true);

if (DEBUG_AJAX) {
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':$_SESSION: ', $_SESSION);
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':$_REQUEST: ', $_REQUEST);
}

if (DEBUG_AJAX) error_log(basename(__FILE__).':'.__LINE__.':checking location...');
$location = get_request_location();
if (!$location) { header('location: '.START); exit(0); }
// make sure, there are no stale sessions in db.
check_logins($location);

if (!$location) {
	if (DEBUG_AJAX) error_log(basename(__FILE__).':'.__LINE__.':no location found.');
// 	header('Location: '.START); exit(0); 
}

if (DEBUG_AJAX) error_log(basename(__FILE__).':'.__LINE__.':checking student...');
if (isset($_REQUEST['student'])) {
	$student = $_REQUEST['student'];
	if (DEBUG_AJAX) error_log(basename(__FILE__).':'.__LINE__.':got student from cgi: '.$student);
} else {
	if (DEBUG_AJAX) error_log(basename(__FILE__).':'.__LINE__.':no student given');
}

// get active exercise for requesting students room
$sth = $dbh->prepare('SELECT r.`active_exercise` FROM `rooms` r INNER JOIN `logins` l ON l.`room` = r.`number`'.
							'WHERE r.`number` = :number AND l.`student` = :student');
// $sth = $dbh->prepare('SELECT `active_exercise` FROM `rooms` WHERE `number` = :number');
$sth->execute(array('number' => $location, 'student' => $student));
$active_exercise = $sth->fetchColumn();
if (DEBUG_AJAX) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':\''.$location.'\' active exercise: ', $active_exercise);
if (DEBUG_AJAX) error_log(basename(__FILE__).':'.__LINE__.':ajax reply: '. $active_exercise ? true : false);
if (DEBUG_AJAX) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':ajax reply var_dump`: ', $active_exercise ? true : false);
if ($active_exercise) {
	// get student status.
	$sth = $dbh->prepare('SELECT s.`finished` FROM `states` s INNER JOIN `rooms` r ON r.`active_exercise` = s.`exercise_id` '.
								'WHERE s.`student_id` = :student AND r.`number` = :location');
	$sth->execute(array('student' => $student, 'location' => $location));
	$status = $sth->fetchColumn();
	if (DEBUG_AJAX) error_log(basename(__FILE__).':'.__LINE__.':student:finished: '.$status);
	
	if ($status == 0) echo 'busy';
	else echo 'finished';
	
} else echo 'inactive';

// echo (json_encode($active_exercise? true : false));


// if (isset($_REQUEST['student'])) {
// 	$sql = 'SELECT COUNT(*) FROM `states` WHERE `student` = :student';
// 	$sth = $dbh->prepare($sql);
// 	$sth->bindParam(':student', $_REQUEST['student'], PDO::PARAM_INT);
// 	try {
// 		$statement_success = $sth->execute();
// 		if ($statement_success) {
// 			$row_count = $sth->fetchColumn();
// 			if ($row_count == 1) {
// 				$sql = 'SELECT `id` FROM `states` WHERE `student` = :student';
// 				$sth = $dbh->prepare($sql);
// 				$sth->bindParam(':student', $_REQUEST['student'], PDO::PARAM_INT);
// 				$statement_success = $sth->execute();
// 				if ($statement_success) {
// 					$exercise_id = $sth->fetch(PDO::FETCH_ASSOC);
// 					// var_dump($result);
// 					// var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':db result: ', $result);
// 					if (is_null($exercise_id['id'])) error_log("dbfail.<br>");
// 					echo $exercise_id['id'];
// 				} else { throw new PDOException("Database operation failed.") ; }
// 			}
// 		}
// 	} catch (PDOException $e) {
// 		error_log($e.getCode());
// 		error_log($sth->errorInfo());
// 	}
// }
?>
