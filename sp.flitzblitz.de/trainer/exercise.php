<?php
	ini_set('session.gc_maxlifetime', 12*60*60);
	session_set_cookie_params(12*60*60);
	session_start();
	
	require_once '../dbmanager.php';
	
	if (DEBUG) {
		var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':$_REQUEST: ', $_REQUEST);
		var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':$_SESSION: ', $_SESSION);
	}
	
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking cgi location...');
	
	$location = get_request_location();
	if (!$location) {
		header('location: '.START);
		exit(0);
	}
	check_logins($location);

	$action = (isset($_REQUEST['do']) ? $_REQUEST['do'] : '');
	switch ($action) {
		case 'add_exercise':
			try {
				$dbh->beginTransaction();
				$now = date('Y-m-d H:i:s');
				$titel = isset($_REQUEST['titel']) ? $_REQUEST['titel'] : '';
				$description = isset($_REQUEST['description']) ? $_REQUEST['description'] : '';
				
				// add new exercise to `exercises`.
				if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.":adding exercise: '$now', name: '$titel', status: '$now', description: '$description'");
				$sql = 'INSERT INTO `exercises` (`start`, `name`, `status`, `description`) VALUES (:exercise, :name, :status, :description)';
				$sth = $dbh->prepare($sql);
				$sth->execute(array('exercise' => $now, 'name' => $titel, 'status' => $now, 'description' => $description));
				
				// note `active_exercise` in `rooms`
				// `rooms`, CONSTRAINT FOREIGN KEY (`active_exercise`) REFERENCES `exercises` (`start`) ON DELETE SET NULL ON UPDATE CASCADE
				if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.":updating `rooms`.`active_exercise`, location: '$location'");
				$sql = 'UPDATE `rooms` SET `active_exercise` = :exercise WHERE `number` = :location';
				$sth = $dbh->prepare($sql);
				$sth->execute(array('exercise' => $now, 'location' => $location));
				
				// add all logged in students to `states`
				$sql = 'SELECT `student` FROM `logins` WHERE `room` = :location';
				$sth = $dbh->prepare($sql);
				$sth->execute(array('location' => $location));
				$students = $sth->fetchAll(PDO::FETCH_COLUMN);
				if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':logged in students: ', $students);
				foreach ($students as $student) {
					// add logged in students to `states`
					if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':adding '.$student.' to `states`');
					/*
					`lug_finished`.`states`, 
						CONSTRAINT `fk_students_states` FOREIGN KEY (`student_id`) REFERENCES `students` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE
					*/
					$sth = $dbh->prepare('INSERT INTO `states` (`exercise_id`, `student_id`) VALUES (:exercise, :student_id)');
					$sth->execute(array('exercise' => $now, 'student_id' => $student));
				}
				$dbh->commit();
			} catch (Exception $e) {
				$dbh->rollback();
				throw $e;
			}
// 				header('Location: formaction.php');
// 				exit(0);
			break;
		case 'stop_exercise':
// 			get active exercise
			$sth = $dbh->prepare('SELECT `active_exercise` FROM `rooms` WHERE `number` = :location');
			$sth->execute(array('location' => $location));
			$active_exercise = $sth->fetchColumn();
			if ($active_exercise) {
// 			try{
// 				$dbh->beginTransaction();
				if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':removing exercise \''.$active_exercise.'\' from `exercises`');
				// drop active exercise
				$sth = $dbh->prepare('DELETE FROM `exercises` WHERE `start`= :active_exercise'); // 2do: disable student buttons.
				$delete_exercise = $sth->execute(array('active_exercise' => $active_exercise));
				if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':delete from exercises: '.$delete_exercise);
				
				// should be set to null by foreign key constraint.
				// update `rooms`
// 				$sth = $dbh->prepare('UPDATE `rooms` SET `active_exercise`= NULL WHERE `number` = :location');
// 				$update_rooms = $sth->execute(array('location' => $location));
// 				if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':update rooms: '.$update_rooms);
// 				$dbh->commit();
// 					header('Location: formaction.php'); exit(0);
// 			} catch (Exception $e) {
// 				$dbh->rollback();
// 				throw $e;
// 			}
			}
			break;
	}
	// get active exercise
	$sql = 'SELECT ex.`start`, ex.`name`, ex.`description` FROM `exercises` ex '.
			'INNER JOIN `rooms` r ON r.`active_exercise` = ex.`start` WHERE r.`number` = :location';
	$sth = $dbh->prepare($sql);
	$sth->execute(array('location' => $location));
	$active_exercise = $sth->fetch();
	if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':active exercise: ', $active_exercise);
	echo '<p>exercise.php</p>';
	if ($active_exercise) {
		echo '<h3>Aktive &Uuml;bung:</h3>'.PHP_EOL;
// 		echo '<form id="exercise" action="exercise.php" method="post" enctype="multipart/form-data" autocomplete="on">';
		echo '<h4>Gestartet: '.$active_exercise['start'].'</h4>'.PHP_EOL;
		echo '<p>Name: '.$active_exercise['name'].'</p>'.PHP_EOL;
		echo '<p>Beschreibung: '.$active_exercise['description'].'</p>'.PHP_EOL;
		echo '<button type="submit" form="exercise" name="do" value="stop_exercise">&Uuml;bung beenden</button><br>'.PHP_EOL;
// 		echo '</form>';
	} else {
		echo <<<ADDEX
<h3>Neue &Uuml;bung anlegen</h3>
		&Uuml;bungstitel
		<input class="inputbox" type="text" name="titel">
		Beschreibung
		<textarea class="inputbox" name="description"></textarea>
		<button type="submit" form="exercise" name="do" value="add_exercise">&Uuml;bung anlegen</button><br>
ADDEX;
	}
?>
