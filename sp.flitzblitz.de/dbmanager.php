<?php
// 	ini_set('error_log', getcwd().'/h/logs/php_error.log');
// 	ini_set('error_log', realpath(dirname(__FILE__)).'/h/logs/php_error.log');
// 	ini_set('error_log', dirname(__FILE__).'/h/logs/php_error.log');
	ini_set('error_log', '/var/www/vhosts/flitzblitz.de/sp/logs/php_error.log');
	ini_set('display_startup_errors', 1);
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	error_reporting(E_ALL);
	
	define('DEBUG', false, true);
	define('DEBUG_DBM', false, true);
// 	$DEBUG_DBM = true;
	define('START', 'index.php', true);
	define('LOGIN_TIMEOUT', date('Y-m-d H:m:s', time()-12*3600), true);
	
	// Datenbank verbinden
	$servername = "localhost";
	$username = "sphh";
	$password = "StatusPanel";
	$dbname   = "lug-finished-h";
	
	$host = 'localhost';
	$db = 'lug-finished-h';
	$charset = 'utf8';
	$user = '***';
	$pass = '***';
	
	$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
	$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => true,
	);
	$dbh = new PDO($dsn, $user, $pass, $opt);
	
	function var_dump_msg_error_log($msg, $object=null) {
		ob_start();                    // start buffer capture
		echo $msg;
		var_dump($object);             // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log($contents);          // log contents of the result of var_dump($object)
	}
	
	function validate_seat($seat, $location) {
		if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.':verifying seat: \''.$seat.'\' for room '.$location);
		global $dbh;
		if (is_numeric($seat) and $seat > 0) {
			// find max seat.
			$sth = $dbh->prepare('SELECT `layout` FROM `rooms` WHERE `number` = :location');
			$sth->execute(array('location' => $location));
			$max_seat = array_sum(explode(' ', $sth->fetchColumn()));
			if ($seat <= $max_seat) {
				if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.':valid seat: '.$seat);
				return true;
			}
		}
		if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.':invalid seat: '.$seat);
		return false;
	}
	
	// checks logged in sessions for timeouts and removes stale sessions
	function check_logins($location) {
		global $dbh;
		// get all students in a room
		if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.':check_logins:checking for expired sessions in: '.$location.'...');
		$sth = $dbh->prepare('SELECT `student`, `login` FROM `logins` WHERE `room` = :location');
// 		$sth = $dbh->prepare('SELECT `student` FROM `logins` WHERE `room` = :location');
		$sth->execute(array('location' => $location));
		$rows = $sth->fetchAll();
		if (DEBUG_DBM) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':check_logins:logged in students: ', $rows);
		if ($rows) {
			foreach ($rows as $row) {
				if ($row['login'] < LOGIN_TIMEOUT) {
					if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.':check_logins:expired: '.$row['student'].'. removing...');
// 					try {
						// remove from `logins` and `students`
// 						$dbh->beginTransaction();
// 						$sth = $dbh->prepare('DELETE FROM `logins` WHERE `student` = :student');
// 						$sth->execute(array('student' => $student));

						// foreign key constraint on logins.student. should remove automatically if parent is removed.
						// 2do: MAKE SURE TO CHECK !!!
						$sth = $dbh->prepare('DELETE FROM `logins` WHERE `student` = :student');
						$sth->execute(array('student' => $row['student']));
// 						$dbh->commit();
// 					} catch (Exception $e) {
// 						$dbh->rollBack();
// 						throw $e;
// 					}
				}
			}
		}
	}
	
	// check cgi variable 'location' for validity
	function get_request_location() {
		global $dbh;
		$location = null;
		if (isset($_REQUEST['location'])) {
			// check cgi variable 'location' for validity
			$rooms      = $dbh->query('SELECT `number` FROM `rooms`')->fetchAll(PDO::FETCH_COLUMN); 	// get all valid room names
			$key        = array_search($_REQUEST['location'], $rooms);		// see if we have such a name
			if ($key !== false) {											// invalid location given --> check session.
				$location   = $rooms[$key];
				$_SESSION['location'] = $location;								// register location with session
				if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.':got location from cgi: '.$location);
			}
		}
		if (!isset($location))
			if (isset($_SESSION['location'])) {
				if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.':no cgi. got location from session: '.$_SESSION['location']);
				$location = $_SESSION['location'];
			} else {
				if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.':no location from cgi and session.');
				return false;
			}
// 		if (DEBUG_DBM) {
// 			if ($location) {
// 				error_log(basename(__FILE__).':'.__LINE__.':got location: '.$location);
// 			} else {
// 				error_log(basename(__FILE__).':'.__LINE__.':no location exception!');
// 			}
// 		}
		return $location;
	}
	
	// returns active session count in `logins`
	function get_count($location) {
		global $dbh;
		check_logins($location);
		$sth = $dbh->prepare('SELECT COUNT(*) FROM `logins` WHERE `room` = :location');
		$sth->execute(array('location' => $location));
		return $sth->fetchColumn();
	}
	
	// remove stale session from `logins` and `students`
	function remove_login($student) {
		global $dbh;
		if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.":remove_login:removing $student...");
// 		check_logins($location);							// 2do: TEST THIS CODE !!!
// 		try {
// 			$dbh->startTransaction();
			$sth = $dbh->prepare('DELETE FROM `logins` WHERE `student` = :student');
			$sth->execute(array('student' => $student));
// 			$sth = $dbh->prepare('DELETE FROM `students` WHERE `session_id` = :student');
// 			$sth->execute(array('student' => $student));
// 			$dbh->commit();
// 		} catch (Exception $e) {
// 			$dbh->rollBack();
// 			throw $e;
// 		}
	}
	
	function get_active_exercise($room) {
		global $dbh;
		$sth = $dbh->prepare('SELECT `active_exercise` FROM `rooms` WHERE `number`= :location');
		$sth->execute(array('location' => $room));
		return $sth->fetchColumn();
	}
	
/*	
	function create_exercise($room, $has_trainer) {
		
	}
	
	function update_status() {
		
	}

	function get_active_exercise($room, $dbh) {
		if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__."get_acive_exercise():room: $room");
		if (!isset($room)) {
			if (DEBUG_DBM)
				$room = 'duesseldorf';
				else error_log(basename(__FILE__).':'.__LINE__."get_acive_exercise(): no room given");
		}
		
		$sql = 'SELECT COUNT(*) FROM `rooms` WHERE name = ?';
		$sth = $dbh->prepare($sql);
		$statement_success = $sth->execute(array($room));
		if ($statement_success) {
			$row_count = $sth->fetchColumn();
			if ($row_count > 0) {
				if ($sth->fetchColumn() > 1) {
					if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.":get_active_exercise(): more than one active exercise.");
					return -1;
				} else {	// single active exercise
					$sql = 'SELECT active_exercise FROM rooms WHERE name = ?';
					$sth = $dbh->prepare($sql);
					$sth->execute(array($room));
					$result = $sth->fetchAll(PDO::FETCH_ASSOC);
					if (DEBUG_DBM) {
						error_log(basename(__FILE__).':'.__LINE__.":get_active_exercise(): single active exercise:");
						var_dump_error_log($result);
						error_log(basename(__FILE__).':'.__LINE__.":get_active_exercise():is_null(\$active_exercise): " . is_null($result[0]['active_exercise']));
					}
					if (is_null($result[0]['active_exercise'])) return 0;
					return $result;
				}
			} else if ($row_count == 0)
				if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__."get_active_exercise(): no active exercise");
				return 0;
		} else {
			if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.":get_active_exercise():SELECT FROM rooms failed.");
			return -1;
		}
		
		if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.":get_active_exercise()::stmt-execute: $statement_success ");
		if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.":get_active_exercise()::rowCount(): " . $sth->rowCount() . "");
		// http://localhost/Finish-IT.php/statuspanel.php?action=select_room&location=duesseldorf&user=student
		// 2do: check if exercise exists. create one if it doesn't.
	// 	if ($statement_success) {
	// 		if ($sth->rowCount() > 0) {
	// 			if ($sth->rowCount() > 1)
	// 				if (DEBUG_DBM) error_log(basename(__FILE__).':'.__LINE__.":get_active_exercise(): more than one active exercise.");
	// 			return $sth->fetchAll(PDO::FETCH_ASSOC);
	// 		} else if ($sth->rowCount() == 0) return 0;
	// 	}
	// 	else return -1;
	}
*/

/*
 * possible runtime operations on database:
 * 	log in students
 * 		map student to room -> workstation (`students_in_room`, [`workstations`])
 * 	start exercise
 * 		set exercise to active (`exercises`)
 *		set `active_exercise` in `rooms` (`rooms`)
 *		create exercise state (`states`)
 *		register all students in room with exercise state  (`states`)
 * 	stop exercise
 * 		update exercise status (`exercises`)
 * 		update `active_exercise` in `rooms` (`rooms`)
 * 	set/update student exercise status (`states`)
*/
