<?php
require_once '../dbmanager.php';

ini_set('session.gc_maxlifetime', 12*60*60);
session_set_cookie_params(12*60*60);
session_start();

// define('START', 'index.php', true);

if (DEBUG) {
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':$_REQUEST: ', $_REQUEST);
	var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':$_SESSION: ', $_SESSION);
}

if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':checking cgi location...');
$location = get_request_location();
if (!$location) {
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.':no cgi location. rerouting to .'.START);
	header('location: '.START);
	exit(0);
}
?>

<html lang="de" dir="ltr" class="redesign no-js" data-ffo-opensans="false" data-ffo-opensanslight="false">
<head>
<meta charset="ISO-8859-1">
<link rel="stylesheet" type="text/css" href="../css3clock.css" />
<link rel="stylesheet" type="text/css" href="trainer.css" />
<script src="trainer.js"></script>
<script src="../jquery.js"></script>
<script type="text/javascript">
	function check_status() {
		var room	 	  = document.getElementById("classroom").dataset.location;
		var supremum      = document.getElementById("classroom").dataset.supremum;
		var default_bg    = 0x000080;
		var busy 	  	  = 0x990000;
		var busy_help  	  = 0xFF6600;
		var finished_help = 0x66E600;
		var finished  	  = 0x008000;
		
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "get_status.php?location="+room, true);
		xhr.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				var states = JSON.parse(this.responseText);

				console.log(states);
				
				for (var i = 1; i < supremum; i++) {
					document.getElementById("kreis"+i).style.background = default_bg;
				}
				
				if (states[0]) {
					for (var i = 1; i < states.length; i++) {
						if (states[i][1] != 0) {
							if (states[i][2] != 0) color = finished_help;	// finished + help
							else color = "008000";							// finished	
						}
						else {
							if (states[i][2] != 0) color = busy_help;		// busy + help
							else color = busy;								// busy
						}
						// 2do: check seat for validity !!!
						document.getElementById("kreis" + states[i][0]).style.background = "#" + color.toString(16);
					}
				} else {	// no active exercise. 
					for (var i = 1; i < states.length; i++) {
						// 2do: check seat for validity !!!
						document.getElementById("kreis" + states[i]).style.background = "#676767";
					}
				}
				var count = states.length-1;
				var article = document.getElementById("article");
				var student_count = document.getElementById("student_count");
				switch (count) {
					case 0:
						article.innerHTML = 'sind ';
						student_count.innerHTML = 'keine';
						break;
					case 1:
						article.innerHTML = 'ist ';
						student_count.innerHTML = 'ein';
						break;
					default:
						article.innerHTML = 'sind ';
						student_count.innerHTML = count;
				} 
				
				setTimeout(check_status, 10000);
			}
		};
		xhr.send();
	}
// 	document.onload = check_status;
// 	window.document.onload = check_status;
	window.onload = check_status;
</script>
</head>
<!--	
<body bgcolor=grey onload="timer = setTimeout(refresh, 30000);">
-->
<body bgcolor=grey>
<title>status get</title>
<font face=arial color=white>
<basefont face=arial color=white>

<h1>Trainer-Seite</h1>
<?php
$sth = $dbh->prepare('SELECT `name` FROM `rooms` WHERE `number` = :location');
$sth->execute(array('location' => $location));
echo '<h2>'.$sth->fetchColumn().'</h2>';
// alles klar, jetzt kommt Datebankabfrage, wer im Klassenzimmer sitzt

// Create connection
$conn = new mysqli($servername, $username, $password,$dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";

// zuerst gesendete Nachrichten speichern
$action = isset($_POST["action"]) ? $_POST["action"] : '';
// $action = $_POST["action"];
if ($action=="message") {
	// save message in DB
	$now = getdate();
	$timestamp="$now[year]-$now[mon]-$now[mday] $now[hours]:$now[minutes]:$now[seconds]";
	$sender= $_POST["sender"];
	$receiver =  $_POST["receiver"];
	$message =   $_POST["TMessage"];
	$message = 	nl2br($message);

	// Sender_ID ist Trainer
	$sender=-1;

	$sql = "insert into messages (id, from_seat, to_seat, message) value ('$timestamp', '$sender', '$receiver', '$message')";
	$result = $conn->query($sql);
}
/*
// L�schabfrage zuerst abfangen, z.B. bei neuer Aufgabe
// $action = $_POST["action"];
if ($action=="delete") {
	// del all states, students and hosts
	$sql = "DELETE FROM states WHERE 1";
	$result = $conn->query($sql);

	$sql = "DELETE FROM students WHERE 1";
	$result = $conn->query($sql);
	$sql = "DELETE FROM workstations WHERE 1";
	$result = $conn->query($sql);
	
}
*/
/*
// In Datebank schauen wieviele Sch�ler im Klassenzimmer
$now=time();
// jetzt - 12h
$from=$now-(12*60*60);
$from_ts=  date('Y-m-d H:m:s', $from);

// find all sessions newer than 8 hours
$sql = "SELECT finished, seat from states inner join students on students.id=student where session_created>'$from_ts'";
$result = $conn->query($sql);
//echo "gefunden: $result->num_rows";
$found_students = $result ? $result->num_rows :'';
*/
?>

&Uuml;bersicht-Seite Trainer, zum aktualisieren bitte hier 
<a href=status.php !onclick="document.location.reload(); return false">klicken 
<img src=../img/icons/reload.gif width=100 align=middle style="position:absolute; top:45px"></a><br style="clear:both">

(Seite wird alle 30 automatisch aktualisiert),
<br>zur&uuml;ck zur <a href=/>Startseite <img src=../img/icons/home.gif width=100 align=middle></a>
<br>
<br>
Zur Zeit 
<span id=article><?php
	$count = get_count($location); 
	echo $count == 1 ? 'ist ' : 'sind ';
?></span> <div class=red id="student_count"><b><?php echo $count; ?></b></div> Teilnehmer angemeldet:<br>


<!--  Klassenzimmer abbilden    -->
<?php 
	$sth = $dbh->prepare('SELECT `layout` FROM `rooms` where `number` = :location');
	$sth->execute(array('location' => $location));
	$layout = explode(' ', $sth->fetchColumn());
	$layout = array_reverse($layout);
	if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.'.layout: ', $layout);
	$counter_reset = array_sum($layout)+1;
	if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.'.supremum: '.$counter_reset);
	$seat = $counter_reset-1;
	echo <<<COUNTER_RESET
<style> body { counter-reset: number $counter_reset; } </style>
<table width="400" height="300" border="1" id="classroom"
	data-location="$location" data-supremum="$counter_reset">
COUNTER_RESET;
	foreach ($layout as $row) {
		echo '<tr>';
		for ($i = 1; $i <= $row; $i++) {
// 			echo "<td><a id=\"href$seat\" href=report.php?seat=$seat><div class=\"kreis\" id=\"kreis$seat\"></div></a></td>";
			if (DEBUG) error_log(basename(__FILE__).':'.__LINE__.'.foreach($seat): '.$i);
			echo "<td><div class=\"kreis\" id=\"kreis$seat\"></div></td>";
			$seat--;
		}
		echo "</tr>\n";
	}
	echo "</table>\n";
?>
<form id="exercise" action="exercise.php" method="post" enctype="multipart/form-data" autocomplete="on">
	<div id="exercise_status">
	<p>status.php</p>
		<?php
			// get active exercise
			$sql = 'SELECT ex.`start`, ex.`name`, ex.`description` FROM `exercises` ex '.
					'INNER JOIN `rooms` r ON r.`active_exercise` = ex.`start` WHERE r.`number` = :location';
			$sth = $dbh->prepare($sql);
			$sth->execute(array('location' => $location));
			$active_exercise = $sth->fetch();
			if (DEBUG) var_dump_msg_error_log(basename(__FILE__).':'.__LINE__.':active exercise: ', $active_exercise);
			if ($active_exercise) {
				echo '<h3>Aktive &Uuml;bung:</h3>'.PHP_EOL;
				
				echo '<h4>Gestartet: '.$active_exercise['start'].'</h4>'.PHP_EOL;
				echo '<p>Titel: '.$active_exercise['name'].'</p>'.PHP_EOL;
				echo '<p>Beschreibung: '.$active_exercise['description'].'</p>'.PHP_EOL;
				echo '<input type="hidden" name="location" value="'.$location.'"><br>'.PHP_EOL;
				echo '<button type="submit" form="exercise" name="do" value="stop_exercise">&Uuml;bung beenden</button><br>'.PHP_EOL;
			} else {
				echo <<<ADDEX
			<h3>Keine &Uuml;bung aktiv --> Neue &Uuml;bung anlegen:</h3>
				&Uuml;bungstitel <input type="text" name="titel" class="inputbox">
				Beschreibung
				<textarea name="description" class="inputbox"></textarea>
				<input type="hidden" name="location" value="$location">
				<button type="submit" form="exercise" name="do" value="add_exercise">&Uuml;bung anlegen</button><br>
ADDEX;
			}
		?>
	</div>
	<input type="hidden" name="location" value="<?php echo $location; ?>">
</form>

<script type="text/javascript">
var exercise_form = document.forms["exercise"];

exercise_form.onsubmit = function(event) {
  event.preventDefault(); //Prevents page from Reloading
 
  var action = this.getAttribute("action"), //Getting Form Action URL
  	  method = this.getAttribute("method"); //Getting Form Submit Method (Post/Get)
 
  //Submitting Form Using Ajax
  var data = new FormData(exercise_form);
  data.append("do", exercise_form.elements.namedItem("do").value);
  var xhr = new XMLHttpRequest();
/*  
  xhr.onload = function() {
  	if (http.status == 200) {
*/
  xhr.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
//   	  	console.log("Form Submitted");
		document.getElementById("exercise_status").innerHTML = this.responseText;
  	 }
  };
  xhr.open(method,action,true);
  xhr.send(data); 
};
</script>
<!--
<table width=400 height=300 border=1 id=classroom>
<tr><?php // for ($i=11;$i>=9;$i--) echo "<td><div class=\"kreis\" id=\"kreis$i\"></div></td>"?><td><div class="kreisg"></div></td></tr>
<tr><?php // for ($i=8;$i>=5;$i--) echo "<td><div class=\"kreis\" id=\"kreis$i\"></div></td>"?></tr>
<tr><?php // for ($i=4;$i>=1;$i--) echo "<td><div class=\"kreis\" id=\"kreis$i\"></div></td>"?></tr>
</table>

<script type="text/javascript">
<?php
/*
// students logged in are shown
$result = $conn->query($sql);
while($row = mysqli_fetch_assoc($result)) {
	//echo "$i i: ".$students[0][0] . " val: ".$students[0][1]." val: ".$students[0][2]."<br>";
	if ($row["finished"])
		echo "document.getElementById(\"kreis". $row["seat"] . "\").style.background = \"green\";\n";
    else
		echo "document.getElementById(\"kreis" . $row["seat"] . "\").style.background = \"#990000\";\n";
}
*/
?>
</script>
-->
<br>
<form action=status.php name="messanger" method=post>
<input type=hidden name=action value=message />
<input type="submit" name="sendMessage" value="Neue Textnachricht schreiben" 
	id="SendMessage" onclick="SubmitMessage ();ShowReceiver();return false" />
<!-- css-Counter zur�cksetzen -->
<div class=cmod></div>
<!--  kleine Sitzordnung f�r Chatmessage einblenden -->
<table width=400 height=300 border=1 id="SendTo" class=tableTo>
<tr><?php for ($i=11;$i>=9;$i--) echo "<td><a id=\"Rhref$i\"><div class=\"kreisT\" id=\"Rkreis$i\"><div class=show></div></div></a></td>"?><td><div class=\"kreisg\"></div></td></tr>
<tr><?php for ($i=8;$i>=5;$i--) echo "<td><a id=\"Rhref$i\"><div class=\"kreisT\" id=\"Rkreis$i\"><div class=show></div></div></a></td>"?></tr>
<tr><?php for ($i=4;$i>=1;$i--) echo "<td><a id=\"Rhref$i\"><div class=\"kreisT\" id=\"Rkreis$i\"><div class=show></div></div></a></td>"?></tr>
<tr><td colspan=4 align=center><a href=report.php?seat=-1 onclick="ChooseReceiver('-1');return false;"><img src=../img/icons/trainer.gif width=100 align=middle></a></td></tr>
<tr><td colspan=4 align=center>send to all students<a href=status.php?seat=-2 onclick="ChooseReceiver('-2');return false;"><img src=../img/icons/student.gif width=100 align=middle></a></td></tr>
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
$sql = "SELECT seat FROM `logins` WHERE `room` = '$location'";
$result = $conn->query($sql);
if ($result) {
	while($row = mysqli_fetch_assoc($result)) {
		//echo "$i i: ".$students[0][0] . " val: ".$students[0][1]." val: ".$students[0][2]."<br>";
		/*
			echo "document.getElementById(\"Rkreis". $row["seat"] . "\").style.background = \"#000080\";\n";
		*/
			echo "document.getElementById(\"Rhref". $row["seat"] . "\").setAttribute(\"href\",\"status.php?seat=-1\" )\n";
			echo "document.getElementById(\"Rhref". $row["seat"] . "\").setAttribute(\"onclick\",\"ChooseReceiver(". $row["seat"] .");return false;\" )\n";
	}
}
?>
</script>

<br>
<table width=400 height=300 border=1 id="Message" class=tableTo><tr><td><textarea cols=46 rows=19 maxlength=65535 name="TMessage"></textarea></td></tr></table>
<input type=hidden value="-1" name=sender>
<input type=hidden value="" name=receiver>
</form>

<!--  Nachrichten ausgeben -->
<div class=messages id=MessageBlock>
<?php 
/*
// Empf�nger_ID aus DB auslesen
$sql = "SELECT `student_id` FROM `states` WHERE `seat` = $senderId ORDER BY `seat` DESC";
$result = $conn->query($sql);
if (isset($result) and $result->num_rows > 0) {
	$row = mysqli_fetch_assoc($result);
	$receiver = $row["student_id"];
}
*/
$now=time();
// jetzt - 12h
$from=$now-(12*60*60);
$from_ts=  date('Y-m-d H:m:s', $from);

// find all messages for trainer
$sql = "SELECT id, from_seat, to_seat, message from messages where (to_seat=-1 or to_seat=-3) and id>'$from_ts' order by id desc";

$result = $conn->query($sql);
if ($result) {
	$found_students=$result->num_rows;
	$students=array();
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
	} else {
		echo "noch keine Nachrichten";
	}
}
?>
</div> <!--  end Messagebock -->

<br>
<div id=tools>
<!--
<form name=classroom_list>
Bitte Klassenzimmer ausw&auml;hlen:<br>

<select id=classs name=classrooms !onchange="getValue();"> 
<optgroup label="Klassenzimmer EG">
   <option> Aula </option>
   <option> Kaffeeautomat</option>
   <option> Empfang </option>
   <option> Nebenraum</option>
</optgroup>
<optgroup label="Klassenzimmer 1. OG">
   <option> Stockholm</option>
   <option> </option>
   <option> </option>
   <option> </option>
</optgroup>
<optgroup label="Klassenzimmer 2. OG">
   <option selected>D&uuml;sseldorf</option>
   <option> </option>
   <option> </option>
   <option> </option>
</optgroup>
<optgroup label="Klassenzimmer 3. OG">
   <option> Technikraum</option>
   <option> </option>
   <option> </option>
   <option> </option>
</optgroup>
<optgroup label="extern 1">
   <option> Extern 1</option>
   <option> Extern 2</option>
   <option> Extern 3</option>
   <option> Extern 4</option>
</optgroup>
<optgroup label="extern 2">
   <option> Extern 11</option>
   <option> Extern 12</option>
   <option> Extern 13</option>
   <option> Extern 14</option>
</optgroup>
</select>   
<input name=classroomi value="D�sseldorf"> </input>
<input type=submit value="Klassenzimmer abfragen" name=send>
</form>
 -->
<!-- 
<form name=classroom_del action="formaction.php" method="post" onsubmit="return(confirm_Click())">
-->
<form name=classroom_del action="formaction.php" method="post">
<input type=hidden name=action value=delete>
<br>
<br>
<div class=darkblue>Verwaltung-Tools</div><br>
Bitte Klassenzimmer ausw&auml;hlen:<br>

<select id=classs name="location" !onchange="getValue();"> 
<optgroup label="Klassenzimmer EG">
   <option> Aula </option>
   <option> Kaffeeautomat</option>
   <option> Empfang </option>
   <option> Nebenraum</option>
</optgroup>
<optgroup label="Klassenzimmer 1. OG">
   <option> Stockholm</option>
   <option> </option>
   <option> </option>
   <option> </option>
</optgroup>
<optgroup label="Klassenzimmer 2. OG">
   <option selected value="2-24">D&uuml;sseldorf</option>
   <option value="2-21">Hannover</option>
   <option value="2-22">Frankfurt</option>
   <option value="2-23">Kiel</option>
   <option value="2-25">Stuttgart</option>
</optgroup>
<optgroup label="Klassenzimmer 3. OG">
   <option> Technikraum</option>
   <option> </option>
   <option> </option>
   <option> </option>
</optgroup>
<optgroup label="extern 1">
   <option> Extern 1</option>
   <option> Extern 2</option>
   <option> Extern 3</option>
   <option> Extern 4</option>
</optgroup>
<optgroup label="extern 2">
   <option> Extern 11</option>
   <option> Extern 12</option>
   <option> Extern 13</option>
   <option> Extern 14</option>
</optgroup>
</select>   
 <button type="submit" value="manage" name="action">Klassenzimmer verwalten</button>
 
 </form>
</div>
<script type="text/javascript">

var selects = document.getElementById('classs');
var room = document.getElementById("classroom").dataset.location;
var classrooms = selects.options;
for (var i = 0; i < classrooms.length; i++) {
	if (classrooms[i].value == room) {
		selects.selectedIndex = i;
		break;
	}
}

</script>
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
<script src="../students/clock.js"></script>
<!--  end Uhr einblenden  -->
</div>
</font>
</body>
</html>