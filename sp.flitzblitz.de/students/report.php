<?php
// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 12*60*60);
// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(12*60*60);
session_start();
// unset($_SESSION['is_new']);
// unset($_SESSION['supremum']);
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
if (!$location) { header('location: '.START); exit(0); }
// make sure, there are no stale sessions in db.
check_logins($location);

// validate seat.
$place = null;
if (isset($_REQUEST['seat'])) {
	if (validate_seat($_REQUEST['seat'], $location)) {
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':seat via cgi: '.$_REQUEST['seat'].'. registering with session.');
		$place = $_REQUEST['seat'];
		$_SESSION['seat'] = $_REQUEST['seat'];
	}
	// should we double check against session / db? - leaning towards no. can correct for wrongly chosen seats this way.
}
if (!isset($place)) {
	// seat was not in cgi request. --> check session and db.
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':no seat via cgi. checking session data and db...');
	if (isset($_SESSION['seat'])) {		// cgi seat invalid. check session.
		$place = $_SESSION['seat'];
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':got seat from session: '.$place);
	} else {									// seat not in session. check db.
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':no seat in cgi and session. checking db...');
		$sth = $dbh->prepare('SELECT `seat` FROM `logins` WHERE `student` = :student');
		$sth->execute(array('student' => $id));
		$place = $sth->fetchColumn();
		if ($place) {
			$_SESSION['seat'] = $place;
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':got seat from db: '.$place);
		}
	}
}
 if (!isset($place)) {
 	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':no seat in session or db. go get new seat...');
 	header('Location: '.GET_SEAT);
 	exit(0);
 }

// double check db entries to see if someone is already sitting on our seat...
if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking db if seat \''.$place.'\' is free...');
$sth = $dbh->prepare('SELECT `student` FROM `logins` WHERE (`room` = :location AND `seat`= :seat)');
$sth->execute(array('location' => $location, 'seat' => $place));
$rosa = $sth->fetchAll(PDO::FETCH_COLUMN);
if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':rosa: ', $rosa);
if (!is_null($rosa)) {
	if (sizeof($rosa) > 0) {
		if (sizeof($rosa) > 1) {
			ob_start();
			var_dump($rosa);
			$rosa_dump = ob_get_clean();
			throw new Exception('ATTENTION!!! SEAT '.$seat.' IN ROOM '.$room.' WAS OCCUPIED MORE THAN ONCE!!! BY: '.$rosa_dump);
		} else if ($rosa[0] != $id) {
// 			0jlrm30rg4d96ttb5koojh0at2
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':rosa: \''.$rosa[0].'\' me: \''.$id.'\'');
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':rosa: \''.$rosa[0].'\' != \''.$id.'\': '. ($rosa[0] != $id));
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':rosa took my seat. diverting to choose a new seat...');
			unset($_SESSION['seat']);
			header('Location: '.GET_SEAT);
			exit(0);
		}
	}
}

if (!$rosa) {	// not in db. --> insert. nota bene: `logins` is child to `students`. insert into `students` first.
	// see, if we have an active exercise.
	$sth = $dbh->prepare('SELECT `active_exercise` FROM `rooms` where `number` = :location');
	$sth->execute(array('location' => $location));
	$active_exercise = $sth->fetchColumn();

	try {
		$dbh->beginTransaction();
// 		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':inserting into `students`: '.session_id().', '.date('Y-m-d H:i:s').', '.$place);
// 		$sth = $dbh->prepare('INSERT INTO `students` (`session_id`,`login`,`seat`) VALUES (:session_id, NOW(), :seat)');
// 		$sth->execute(array('session_id' => session_id(), 'seat' => $place));
		
		// add session to the `rooms` `login` table.
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':adding \''.session_id()."' to `logins` for room '$location'.");
		$sth = $dbh->prepare('INSERT `logins` VALUES (:room, :student, NOW(), :seat)');
		$sth->execute(array('student' => session_id(), 'room' => $location, 'seat' => $place));
		
		if ($active_exercise) {					// if so, add session to `states`
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':exercise '.$active_exercise.' active. adding '.session_id().' to `states`.');
			$sth = $dbh->prepare('INSERT INTO `states` (`exercise_id`, `student_id`) VALUES (:exercise, :student_id)');
			$sth->execute(array('exercise' => $active_exercise, 'student_id' => session_id()));
		}
		$dbh->commit();
	} catch (Exception $e) {
		$dbh->rollBack();
		// 2do: check for duplicates and make sure our session is registered in the database.
		throw $e;
	}
}
?>
<!DOCTYPE html>
<html lang="de" dir="ltr" class="redesign no-js" data-ffo-opensans="false" data-ffo-opensanslight="false">
<head><title>status send</title>
<meta charset="ISO-8859-1">
<link rel="stylesheet" type="text/css" href="../css3clock.css" />
<link rel="stylesheet" type="text/css" href="students.css" />
<script src="students.js"></script>
<script src="../jquery.js"></script>

</head>
<body bgcolor=grey>

<h1>Studenten-Seite</h1>
<?php 
$url="open.php";
$message ="";
$session_created="";

/* h
// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 12*60*60);
// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(12*60*60);
session_start();
*/

$ip = getenv('REMOTE_ADDR');
$id = session_id();
/*
try { $seat = $_GET["seat"]; }
catch (Exception $ex) { $seat = 0; }
*/
$seat=$place;
$senderId=$seat;


// Create connection
$conn = new mysqli($servername, $username, $password,$dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";

// zuerst gesendete Nachrichten speichern
$action = isset($_POST["action"]) ? $_POST["action"] : '';
if($action=="message"){
	// save message in DB
	$now = getdate();
	$timestamp="$now[year]-$now[mon]-$now[mday] $now[hours]:$now[minutes]:$now[seconds]";
	$sender= $_POST["sender"];
	$receiver =  $_POST["receiver"];
	$message =   $_POST["TMessage"];
	$message = 	nl2br($message);
	
	$sql = "insert into messages (id, from_seat, to_seat, message) value ('$timestamp', '$sender', '$receiver', '$message')";
	$result = $conn->query($sql);
}

/* h: replaced by h. we already did that.	
// In Datebank schauen ob Session schon angelegt ist
$sql = 'SELECT `id` FROM `logins` WHERE `student` =\'$id\'';
$result = $conn->query($sql);

if (isset($result) and $result->num_rows > 0) {
	// User existiert, dann abfragen ob schon fertig
	$row = mysqli_fetch_assoc($result);
	$student_id= $row["id"];
	$sql = "SELECT finished, seat FROM states where student =$student_id";

	$sql = "SELECT sta.finished, stu.seat FROM students stu INNER JOIN states ON sta.student = stu.session_id where stu.id =$student_id";
	$result = $conn->query($sql);
	if (DEBUG) var_dump_msg_error_log('frigging bernie!!!', $result);
	if ($result) {
		if ( $result->num_rows > 0){
			$row = mysqli_fetch_assoc($result);
			$message = "Sie haben den Platz Nr.: <div class=red><b>".$row["seat"]."</div> ausgesucht.\n";
			$seat=$row["seat"];
			$senderId=$row["seat"];
			$finished =$row["finished"];
			if ($finished)
				$url="ready.php";
			else 
		    	$url="open.php";
		}
		else {
	    echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}

}
else{
	// User wird eingetragen
	//echo "User wird eingetragen<br>";
	// wenn neue Übung gestartet, Student zur Anmeldeseite weiterleiten
	if ($seat==0){
		echo "<script> location.replace(\"/\")</script>";
		exit(0);
	}
	// jetzt in Tabelle workstations eintragen
	$sql = "insert into workstations (name,ipv4) value ('na','$ip')";
	if ($conn->query($sql) === TRUE) {
		//echo "New record created successfully";
		$workstation_id= mysqli_insert_id($conn);
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
	$now = getdate();
	$timestamp="$now[year]-$now[mon]-$now[mday] $now[hours]:$now[minutes]:$now[seconds]";
	$sql = "insert into students (session_id, session_created, workstation) value ('$id', '$timestamp', '$workstation_id')";
	$message = "Sie haben den Platz Nr.: <div class=red><b>$seat</div> ausgesucht.\n";
	//$sqlInsWs = "INSERT INTO students ";
	$student_id=0;
	if ($conn->query($sql) === TRUE) {
    //echo "New record created successfully";
		$student_id= mysqli_insert_id($conn);
	} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
	}

	// jetzt in Tabelle states eintragen
	$sql = "insert into states (id, workstation, student, finished,help, seat) value ('$timestamp', $workstation_id,'$student_id', '0','0','$seat')";
	//echo $sql;die(0);
	if ($conn->query($sql) === TRUE) {
    //echo "New record created successfully";
		
	} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
	}

}
*/
?>

Trainer feedback geben und roten Schalter drücken, wenn fertig
<br>
zurück zur <a href="/">Startseite <img src=../img/icons/home.gif width=100 align=middle></a>
<br>
<br>
<?php echo $message?>
<br>
<iframe id="reporter" src="button.php?location=<?php echo $location ?>"></iframe>
<br>
<form action=report.php name="messanger" method=post>
<input type=hidden name=action value=message>
<input type="submit" name="sendMessage" value="Neue Textnachricht schreiben" id="SendMessage" onclick="SubmitMessage();ShowReceiver();return false" />
<br><br>
<!--  Nachrichten ausgeben -->
<div class=messages id=MessageBlock>
<?php 

$now=time();
// jetzt - 12h
$from=$now-(12*60*60);
$from_ts=  date('Y-m-d H:m:s', $from);

// find all messages for the student
$sql = "SELECT id, from_seat, to_seat, message from messages where (to_seat=$senderId or to_seat=-2 or to_seat=-3)  and id>'$from_ts' order by id desc";
$result = $conn->query($sql);
$found_students=$result->num_rows;
if (mysqli_num_rows($result) > 0) {
	// output data of each row
	$i=1;
	while($row = mysqli_fetch_assoc($result)) {
		$fromID=$row["from_seat"];
		if ($fromID==-1)
			$fromID = "Trainer";
		else if($fromID==0 or $fromID <-1)
			$fromID = "Unknown";
		$toID=$row["to_seat"];
		if ($toID==-1)
			$toID = "Trainer";
		else if ($toID==-2)
			$toID = "all students";
		else if ($toID==-3)
			$toID = "all students and trainer";
		else if ($toID<-3)
			$toID = "Unknown";
		$date = new DateTime($row["id"]);
		$dateD =date_format($date, 'd.m.Y');
		$dateT =date_format($date, 'H:i:s');
		echo "<div class=yellow>Nachricht Nr.</div> <u>$i</u> <div class=yellow>vom:</div> <u>$dateD</u> <u>$dateT</u><div class=yellow> von Platz:</div> <u>$fromID</u> <div class=yellow>an:</div> <u>$toID</u><br>". $row["message"]."<br><br>";
		$i++;
	}
} else 
	echo "noch keine Nachrichten";
?>
</div>
zum aktualisieren bitte hier 
<a href=report.php>klicken 
<img src=../img/icons/reload.gif width=50 align=middle style="position:relative; top:0px"></a><br style="clear:both">
<br>

<!--  kleine Sitzordnung für Chatmessage einblenden -->
<table width=400 height=300 border=1 id="SendTo">
	<tr>
		<td colspan=4 align=center>
			<a href=report.php?seat=-1 onclick="ChooseReceiver('-1');return false;">
				<img src=../img/icons/trainer.gif width=100 align=middle>
			</a>
		</td>
	</tr>
<tr><?php for ($i=1;$i<=4;$i++) echo "<td><a id=\"href$i\"><div class=\"kreis\" id=\"kreis$i\"></div></a></td>"?></tr>
<tr><?php for ($i=5;$i<=8;$i++) echo "<td><a id=\"href$i\"><div class=\"kreis\" id=\"kreis$i\"></div></a></td>"?></tr>
<tr>
	<td><div class=\"kreisg\"></div></td>
	<?php for ($i=9;$i<=11;$i++) echo "<td><a id=\"href$i\"><div class=\"kreis\" id=\"kreis$i\"></div></a></td>"?>
</tr>
<tr><td colspan=4 align=center>send to all students
	<a href=report.php?seat=-2 onclick="ChooseReceiver('-2');return false;">
		<img src=../img/icons/student.gif width=100 align=middle>
	</a>
</td></tr>
<tr><td colspan=4 align=center>send to all students and trainer<a href=report.php?seat=-3 
       onclick="ChooseReceiver('-3');return false;">
       <img src=../img/icons/student.gif width=100 align=middle>
       <img src=../img/icons/trainer.gif width=100 align=middle></a></td></tr>
</table>

<script type="text/javascript">
<?php 
// Nur angemeldete Sitze anzeigen:
$now=time();
// jetzt - 12h
$from=$now-(12*60*60);
$from_ts=  date('Y-m-d H:m:s', $from);
// $sql = "SELECT finished, seat from states inner join students on students.id=student where session_created>'$from_ts'";
$sql = "SELECT `seat` FROM `logins` WHERE `room` = '$location'";
$result = $conn->query($sql);
if (isset($result)) {
	while($row = mysqli_fetch_assoc($result)) {
// 			echo "document.getElementById(\"kreis". $row["seat"] . "\").style.background = \"#000080\";\n";
			echo "document.getElementById(\"href". $row["seat"] . "\").setAttribute(\"href\",\"report.php?seat=". $row["seat"] ."\" )\n";
			echo "document.getElementById(\"href". $row["seat"] . "\").setAttribute(\"onclick\",\"ChooseReceiver(". $row["seat"] .");return false;\" )\n";
	}
}
?>
</script>
<br>
<table width=400 height=300 border=1 id="Message"><tr><td><textarea cols=46 rows=19 maxlength=65535 name="TMessage"></textarea></td></tr></table>
<input type=hidden value="<?php echo $senderId;?>" name=sender>
<input type=hidden value="" name=receiver>
</form>

<br>
<!-- <div class=invis>
ihre IP ist : <?php echo $ip;?><br>
ihre ID ist : <?php echo $id;?><br>
</div>
 -->
<!--   Uhr einblenden  -->
<div class=ms><div id="liveclock" class="outer_face">
<div class="marker oneseven"></div>
<div class="marker twoeight"></div>
<div class="marker fourten"></div>
<div class="marker fiveeleven"></div>
<div class="inner_face">
<div class="hand hour"></div>
<div class="hand minute"></div>
<div class="hand second"></div>
</div></div>
<script src="clock.js"></script>
<!--  end Uhr einblenden  -->

</body>
