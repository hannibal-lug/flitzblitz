<?php
// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 12*60*60);
// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(12*60*60);
session_start();

require_once '../dbmanager.php';

define('DEBUG_AJAX', true, true);

if (DEBUG_AJAX) {
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':trainer:$_SESSION: ', $_SESSION);
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':trainer:$_REQUEST: ', $_REQUEST);
}

$location = get_request_location();
if (!$location) { 
	error_log('no location in ajax get_status.php');
	exit(-1);
}
check_logins($location);
/*
 * 3 student states:
 *   1. logged in: no exercise active --> grey
 *   2. exercise active, working 	  --> red
 *   3. exercise active, finished	  --> green
 */
$sth = $dbh->prepare('SELECT `active_exercise` FROM `rooms` WHERE `number` = :location');
$sth->execute(array('location' => $location));
$active_exercise = $sth->fetchColumn();

if ($active_exercise) {
	// get seat and status from all students logged in current room (via logins), attending active exercise (via rooms.active_exercise).
	// stu.`seat`, sta.`finished` FROM `students` stu, `logins` l, `states` sta
	$sql = 'SELECT l.`seat`, s.`finished`, s.`help` FROM `logins` l '.
				'INNER JOIN `states` s ON (s.`student_id` = l.`student`) '.
				'WHERE s.`exercise_id` = :active_exercise';
// 	if (DEBUG_AJAX) error_log(basename(__FILE__).':'.__LINE__.':trainer/get_status sql: '.$sql);
// 	$sql = 'SELECT stu.`seat`, sta.`finished` FROM `students` stu RIGHT JOIN `logins` l ON (l.`student` = stu.`session_id`) LEFT JOIN `states` sta ON (sta.`student` = l.`student`)';
	$sth = $dbh->prepare($sql);
// 	if (DEBUG_AJAX) {
// 		var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':trainer statement handle: ', $sth);
// 		var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':debugDumpParams: ', $sth->debugDumpParams());	// gets dumped onto stdout / xhr response!
// 	}
	$sth->execute(array('active_exercise' => $active_exercise));
	$have_exercise = array(true);
	$rows = $sth->fetchAll(PDO::FETCH_NUM);
	$reply = array_merge($have_exercise, $rows);
	if (DEBUG_AJAX) {
		var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':trainer:active exercise status: ', $reply);
		var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':trainer:json_encode: ', json_encode($reply));
	}
// 	echo json_encode($rows);
// 	echo json_encode('blah');
} else { // inactive
	if (DEBUG_AJAX) error_log(basename(__FILE__).':'.__LINE__.':trainer:no exercise active.');
	// find logged in students.
	$sth = $dbh->prepare('SELECT `seat` FROM `logins` WHERE `room` = :location');
	$sth->execute(array('location' => $location));
	$logged_in = $sth->fetchAll(PDO::FETCH_COLUMN);
	$have_exercise = array(false);
	$reply = array_merge($have_exercise, $logged_in);
	if (DEBUG_AJAX) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':trainer:logged in students: ', $reply);
	if (DEBUG_AJAX) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':trainer:json_encode students: ', json_encode($reply));
}
header('Content-Type: application/json');
echo json_encode($reply);

/*
$sth = $dbh->prepare('SELECT `active_exercise` FROM `rooms` WHERE `number` = :number');
$sth->execute(array('number' => $_SESSION['location']));
if (false) vdmel(basename(__FILE__).':'.__LINE__.":'SELECT `active_exercise` FROM `rooms`:\n", $sth->fetchColumn());
echo $sth->fetchColumn();
*/
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
