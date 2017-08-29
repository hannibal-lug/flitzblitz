<?php
	require_once '../dbmanager.php';
	// server should keep session data for AT LEAST 12 hour
	ini_set('session.gc_maxlifetime', 12*60*60);
	// each client should remember their session id for EXACTLY 12 hour
	session_set_cookie_params(12*60*60);
	session_start();

// 	define('START', 'index.php', true); // moved to dbmanager
	
	if (DEBUG) {
		var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':$_REQUEST: ', $_REQUEST);
		var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':$_SESSION: ', $_SESSION);
	}
	
	$id = session_id();
	
	// see, if we got a valid location, i.e. room.
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking cgi location...');
	$location = get_request_location();
	if (!$location) {
		header('location: '.START);
		exit(0);
	}
	// remove stale sessions. 
	check_logins($location);
	
	// check with session and db, if the user has already logged in. (student is not yet able to change / correct a wrongly chosen seat) 
	if (isset($_SESSION['seat'])) {
		$place = $_SESSION['seat'];		// we assume the seat remains valid for the entire lifetime of a session.
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':got seat from session: '.$place);
	} else {	// not in session --> check db.
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking, if \''.$id.'\' is already logged in to \''.$location.'\'...');
		$sth = $dbh->prepare('SELECT `seat` FROM `logins` WHERE `room` = :location AND `student` = :student');
		$sth_execute = $sth->execute(array('location' => $location, 'student' => $id));
		$place = $sth->fetchColumn();
		if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':seat from db: ', $place);
		
		// frigging db !!! 
		// so there seem to be cases, where an invalid seat got stored to the db, so... validate...
		if (validate_seat($place, $location)) {
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':storing seat '.$place.' in session.');
			$_SESSION['seat'] = $place;
		} else {
			unset($place);
		}
	}
	// got seat --> reroute to status button.
	if (isset($place)) {
		if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':student \''.$id.'\' got seat: \''.$place.
				'\' in \''.$location.'\'. rerouting to report.php...');
		header('Location: report.php?location='.$location.'&seat='.$place);
		exit(0);
	}
?>
<!DOCTYPE html>
<html lang="de" dir="ltr" class="redesign no-js" data-ffo-opensans="false" data-ffo-opensanslight="false">
<head><title>choose place</title>
<meta charset="iso-8859-1"/>
</style>
<link rel="stylesheet" type="text/css" href="../css3clock.css" />
<style type="text/css">
body {
	       font-family : arial;
		   /*font-weight:bold;*/
		   color:white;
           counter-reset: number
}

table{
		border-spacing: 0px;
	    padding: 0px;
		margin: 0 0 0 30px;
		border: 1px solid white
}

div.kreisg {
  width: 100px;
  height: 100px;
  border-radius: 50px;
  background-color: grey;
  margin: 10px;
  counter-decrement: number -1;
}

div.kreis {
  width: 100px;
  height: 100px;
  border-radius: 50px;
  background-color: #000080;
  margin: 10px;
  counter-increment: number;
}

div.kreis::after{
    content: counter(number);
    position: relative;
    color: white;
    width: 100%;
    text-align: center;
    top: 43px;
    left:43px;
}


.invis{
	  display:inline;
	  color:grey;
}
      .red{
		  display:inline;
		  color:red;
	  }

      .ms { 
			position:absolute;
			!margin: 1cm;
			!background-color:#99FF99; 
			width: 200px;
			border: 0px solid;
			!margin: 0px 0px 0px 0px;
			top:20px;
			left:700px;
	  }
</style>
</head>
<body bgcolor=grey>
<?php
/* h
// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 12*60*60);
// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(12*60*60);
session_start();
*/
$id = session_id();

// Check connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

/* h: replaced by pdo.
// In Datebank prüfen ob bereits eine Workstation ausgewählt wurde
$sql = "SELECT seat FROM logins WHERE student =\"$id\"";
$resWs = $conn->query($sql);

if ( $resWs->num_rows > 0 and $resWs != 0 ){
	// Der User hat in seiner aktuellen Session bereits eine Workstation ausgewählt und wird auf die Statusseite weitergeleitet
	header("Location: report.php?seat=0");
}
*/
?>

<?php
/* h: replaced by pdo.
// In Datebank schauen ob Session schon angelegt ist
// falls ja, gleich weiter reichen
$sql = "SELECT id FROM students where session_id =\"$id\"";
$result = $conn->query($sql);

if ( $result->num_rows > 0){
	// User existiert, dann abfragen ob schon fertig
	$row = mysqli_fetch_assoc($result);
	$student_id= $row["id"];
	$sql = "SELECT seat FROM states where student =$student_id";
	$result = $conn->query($sql);
	
	if ( $result->num_rows > 0){
		$row = mysqli_fetch_assoc($result);
		$seat=$row["seat"];
		echo "<script> document.location.replace(\"report.php?seat=$seat\n\")</script>";
	}
}
*/
?>

<h1>Bitte w&auml;hlen Sie ihren Sitz</h1>

bitte klicken Sie auf den Platz auf dem Sie sitzen


<br>
zur&uuml;ck zur <a href="/">Startseite <img src=../img/icons/home.gif width=100 align=middle></a>
<br>
<br>

<!-- </div> erhalten! -->
<div class=ms> 
<div id="liveclock" class="outer_face">

<div class="marker oneseven"></div>
<div class="marker twoeight"></div>
<div class="marker fourten"></div>
<div class="marker fiveeleven"></div>

<div class="inner_face">
<div class="hand hour"></div>
<div class="hand minute"></div>
<div class="hand second"></div>
</div>

</div>

<script src="../jquery.js"></script>

<script type="text/javascript">

/***********************************************
* CSS3 Analog Clock- by JavaScript Kit (www.javascriptkit.com)
* Visit JavaScript Kit at http://www.javascriptkit.com/ for this script and 100s more
***********************************************/

var $hands = $('#liveclock div.hand')

window.requestAnimationFrame = window.requestAnimationFrame
                               || window.mozRequestAnimationFrame
                               || window.webkitRequestAnimationFrame
                               || window.msRequestAnimationFrame
                               || function(f){setTimeout(f, 60)}


function updateclock(){
	var curdate = new Date()
	var hour_as_degree = ( curdate.getHours() + curdate.getMinutes()/60 ) / 12 * 360
	var minute_as_degree = curdate.getMinutes() / 60 * 360
	var second_as_degree = ( curdate.getSeconds() + curdate.getMilliseconds()/1000 ) /60 * 360
	$hands.filter('.hour').css({transform: 'rotate(' + hour_as_degree + 'deg)' })
	$hands.filter('.minute').css({transform: 'rotate(' + minute_as_degree + 'deg)' })
	$hands.filter('.second').css({transform: 'rotate(' + second_as_degree + 'deg)' })
	requestAnimationFrame(updateclock)
}

requestAnimationFrame(updateclock)


</script>
</div>

<!--  Klassenzimmer abbilden    -->
<table width=400 height=300 border=1 id="SendTo">
<!-- 
<tr><?php // for ($i=1;$i<=4;$i++) echo "<td><a id=\"href$i\" href=report.php?seat=$i><div class=\"kreis\" id=\"kreis$i\"></div></a></td>"?></tr>
<tr><?php // for ($i=5;$i<=8;$i++) echo "<td><a id=\"href$i\" href=report.php?seat=$i><div class=\"kreis\" id=\"kreis$i\"></div></a></td>"?></tr>
<tr><td><div class=\"kreisg\"></div></td>
	<?php // for ($i=9;$i<=11;$i++) echo "<td><a id=\"href$i\" href=report.php?seat=$i><div class=\"kreis\" id=\"kreis$i\"></div></a></td>"?></tr>
 -->
 
<?php
	// display seat placeholders according to room layout.
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking layout for room: '.$location);
	$sth = $dbh->prepare('SELECT `layout` FROM `rooms` where `number` = :location');
	$sth->execute(array('location' => $location));
	$layout = explode(' ', $sth->fetchColumn());
	$seet = 1;
	foreach ($layout as $pc_in_row) {
		echo '<tr>';
		for ($i = 1; $i <= $pc_in_row; $i++) {
			echo "<td><a id=\"href$seet\" href=report.php?seat=$seet><div class=\"kreis\" id=\"kreis$seet\"></div></a></td>";
			$seet++;
		}
		echo '</tr>';
	}
?>
</table>
<?php 
$sth = $dbh->prepare('SELECT `seat` FROM `logins` WHERE `room` = :location');
$sth->execute(array('location' => $location));
$reserved_seats = $sth->fetchAll(PDO::FETCH_COLUMN);
if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':reserved seats: ', $reserved_seats);

if ($reserved_seats) {
	echo '<script type="text/javascript">';
	foreach ($reserved_seats as $rs) {
		echo "document.getElementById(\"kreis".$rs."\").style.background = \"#c0c0c0\";\n";
		echo "document.getElementById(\"href".$rs."\").removeAttribute(\"href\")\n";
		echo "document.getElementById(\"href".$rs."\").removeAttribute(\"onclick\")\n";
	}
	echo '</script>';
}
?>

<!--
<script type="text/javascript">
-->
<?php 
/*
// Nur angemeldete Sitze anzeigen:
$now=time();
// jetzt - 12h
$from=$now-(12*60*60);
$from_ts=  date('Y-m-d H:m:s', $from);
$sql = "SELECT finished, seat from states inner join students on students.id=student where session_created>'$from_ts'";
$result = $conn->query($sql);
while($row = mysqli_fetch_assoc($result)) {
	//echo "$i i: ".$students[0][0] . " val: ".$students[0][1]." val: ".$students[0][2]."<br>";
		echo "document.getElementById(\"kreis". $row["seat"] . "\").style.background = \"#c0c0c0\";\n";
		echo "document.getElementById(\"href". $row["seat"] . "\").removeAttribute(\"href\")\n";
		echo "document.getElementById(\"href". $row["seat"] . "\").removeAttribute(\"onclick\")\n";
}
*/

// find reserved seats in this room and remove them from choice options.
?>
<!--
</script>
-->

<h1>Bitte wählen Sie ihr Klassenzimmer</h1>

<?php
	// Der User soll eine Workstation auswählen
	// Gibt die Namen der Schulungsräume in einer Dropdown Liste aus
	$sqlRooms = "SELECT name FROM rooms";
	$resRooms = $conn->query($sqlRooms);
	
	if($resRooms->num_rows > 0) {
		
		// Füllt die Dropdown Liste
		echo "<select id=clr name=classrooms>";
		echo "<option value=''>---Klassenzimmer---</option>";
		while ($room = mysqli_fetch_assoc($resRooms)) {
			$i=1;
			echo "<option value='{".$i."}'>".$room['name']."</option>";
			$i++;
		}
		echo "</select>";
		
		//
// 		$selRoom = $_POST['clr'];
		
// 		$sqlNrWs = "SELECT workstations FROM rooms WHERE classroom = \"$selRoom\"";
// 		$resNrWs = $conn->query($sqlNrWs);
		
// 		for( $i = 0; §i < $resNrWs; )	{
// 			if ($i>0 and $i%4==0) {
// 				echo "<div class=containerdivnewline></div>";
// 				echo "<div class=\"ovalr\"></div>";
// 			} else {
// 				echo "<div class=\"ovalr\"></div>";
// 			}
// 			$i++;
// 		}
	}
?>


</body>
</html>