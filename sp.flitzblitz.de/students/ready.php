<!DOCTYPE html>
<html lang="de" dir="ltr" class="redesign no-js" data-ffo-opensans="false" data-ffo-opensanslight="false">
<meta charset="utf-8"/>
<body bgcolor=#288741>
<script>
function myaction(){
		document.bgColor = "#288741";
		document.form1.action="open.php?status=open";
		document.form1.submit();
}

</script>


<style type="text/css"><!--
/* * Copyright (c) 2013 Thibaut Courouble * http://www.cssflow.com * * Licensed under the MIT License: * http://www.opensource.org/licenses/mit-license.php */

body {
    font: bold 0.8em Arial;
	color: white;
}

.container { margin: 30px auto; width: 280px; text-align: center;}

.container > .switch {  display: block;  margin: 12px auto;}

.switch { position: relative; display: inline-block;  vertical-align: top;  width: 156px;  height: 20px;padding: 3px;  background-color: white;  border-radius: 18px;  box-shadow: inset 0 -1px white, inset 0 1px 1px rgba(0, 0, 0, 0.05);cursor: pointer;  background-image: -webkit-linear-gradient(top, #eeeeee, white 25px); background-image: -moz-linear-gradient(top, #eeeeee, white 25px); background-image: -o-linear-gradient(top, #eeeeee, white 25px); background-image: linear-gradient(to bottom, #eeeeee, white 25px);}

.switch-input { position: absolute; top: 0;left: 0; opacity: 0;}

.switch-label { position: relative; display: block; height: inherit; font-size: 10px;text-transform: uppercase;  background: red;  border-radius: inherit;  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.12), inset 0 0 2px rgba(0, 0, 0, 0.15); -webkit-transition: 0.15s ease-out;-moz-transition: 0.15s ease-out; -o-transition: 0.15s ease-out;  transition: 0.15s ease-out;  -webkit-transition-property: opacity background; -moz-transition-property: opacity background;-o-transition-property: opacity background; transition-property: opacity background;}

.switch-label:before, .switch-label:after {position: absolute; top: 50%; margin-top: -.5em;line-height: 1; -webkit-transition: inherit; -moz-transition: inherit; -o-transition: inherit;transition: inherit;}

.switch-label:before { content: attr(data-off); right: 11px; color: #aaa;  text-shadow: 0 1px rgba(255, 255, 255, 0.5);}

.switch-label:after {content: attr(data-on); left: 11px; color: white; text-shadow: 0 1px rgba(0, 0, 0, 0.2); opacity: 0;}

.switch-input:checked ~ .switch-label {background: #47a8d8; box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.15), inset 0 0 3px rgba(0, 0, 0, 0.2);}

.switch-input:checked ~ .switch-label:before {opacity: 0;}

.switch-input:checked ~ .switch-label:after { opacity: 1;}
.switch-handle {position: absolute; top: 4px;left: 4px;  width: 18px; height: 18px; background: white; border-radius: 10px; box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2); background-image: -webkit-linear-gradient(top, white 40%, #f0f0f0); background-image: -moz-linear-gradient(top, white 40%, #f0f0f0);background-image: -o-linear-gradient(top, white 40%, #f0f0f0); background-image: linear-gradient(to bottom, white 40%, #f0f0f0); -webkit-transition: left 0.15s ease-out; -moz-transition: left 0.15s ease-out; -o-transition: left 0.15s ease-out; transition: left 0.15s ease-out;}

.switch-handle:before {content: ''; position: absolute; top: 50%; left: 50%; margin: -6px 0 0 -6px;
width: 12px; height: 12px; background: #f9f9f9; border-radius: 6px; box-shadow: inset 0 1px rgba(0, 0, 0, 0.02); background-image: -webkit-linear-gradient(top, #eeeeee, white); background-image: -moz-linear-gradient(top, #eeeeee, white); background-image: -o-linear-gradient(top, #eeeeee, white); background-image: linear-gradient(to bottom, #eeeeee, white);}

.switch-input:checked ~ .switch-handle { left: 140px; box-shadow: -1px 1px 5px rgba(0, 0, 0, 0.2);}
.switch-green > .switch-input:checked ~ .switch-label { background: #4fb845;}

--></style>
<div class="container">
<?php 
// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 12*60*60);
// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(12*60*60);
session_start();
$ip = getenv('REMOTE_ADDR');
$id = session_id();

// jetzt den ready status in DB eintragen
// Datenbank verbinden
$servername = "localhost";
$username = "sp";
$password = "ServicePanel";
$dbname   = "lug_finished_h";
// Create connection
$conn = new mysqli($servername, $username, $password,$dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";

// In Datebank schauen ob Session schon angelegt ist
$sql = "SELECT id FROM students where session_id =\"$id\"";
$result = $conn->query($sql);
if ( $result->num_rows > 0){
	// User existiert -> update auf ready
	$row = mysqli_fetch_assoc($result);
	$student_id= $row["id"];
    $sql = "update states set finished='1' where student=$student_id";
	if ($conn->query($sql) === TRUE) {
    //echo "New record created successfully";
		$student_id= mysqli_insert_id($conn);
	} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
	}
}
?>

<form name="form1" action="open.php" method="Get">
<input type=hidden name=id value="<?php echo session_id();?>">
<input type=hidden name=status value="open">

<label class="switch switch-green"><input id=switch1 type="checkbox" class="switch-input" checked onchange="myaction();"><span class="switch-label" data-on="ready" data-off="in progress"></span><span class="switch-handle"></span></label>
<br><br>Bitte schalten sie auf rot, <br>um weiter zu arbeiten.
</form>

</div>

</body>
</html>
