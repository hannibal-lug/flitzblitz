var timer = null;

function confirm_Click() {
	var strconfirm = confirm("Neue Übung starten?");
	if (strconfirm == true)
		return true;
	else
		return false;
}
function FensterOeffnen(Adresse) {
	MeinFenster = window
			.open(Adresse, "Zweitfenster",
					"width=300,height=100,left=10,top=10,menubar=no,location=no,resizeable=no");
	// MeinFenster.document.write("<p>ein neues Fenster!<br>(Besser wÃ¤re aber
	// eine dialog-Box!)</p>");
	MeinFenster.focus();
}
function refresh() {
	timer = null;
	document.location.replace("status.php");
}
function ShowReceiver() {
	if (document.forms["messanger"].sendMessage.value == "Neue Textnachricht schreiben") {
		if (timer) {
			clearTimeout(timer);
			timer = null;
		}
		document.getElementById("tools").style.visibility = "hidden";
		document.getElementById("classroom").style.visibility = "hidden";
		document.getElementById("MessageBlock").style.visibility = "hidden";
		document.getElementById("SendTo").style.visibility = "visible";
		document.forms["messanger"].sendMessage.value = "An wen senden?"
	}

}
function getValue() {
	// alert (document.getElementById("classs").value);
	// document.getElementById("demo").innerHTML = "You selected: " + x;
	classroom.classroomi.value = document.getElementById("classs").value;

}
function ChooseReceiver(receiver) {
	if (document.forms["messanger"].sendMessage.value == "An wen senden?") {
		document.getElementById("SendTo").style.visibility = "hidden";
		document.getElementById("Message").style.visibility = "visible";
		document.forms["messanger"].receiver.value = receiver;
		document.forms["messanger"].sendMessage.value = "Jetzt Nachricht schreiben und senden";
	}
}

function SubmitMessage() {
	textarea = document.forms["messanger"].TMessage.value;
	if (document.forms["messanger"].sendMessage.value == "Jetzt Nachricht schreiben und senden"
			&& textarea.length > 0) {
		document.forms["messanger"].submit();
	} else if (document.forms["messanger"].sendMessage.value == "Jetzt Nachricht schreiben und senden")
		alert("Bitte eine Nachricht eingeben!");
}

function check_status() {
	var room	 	  = document.getElementById("classroom").dataset.location;
	var supremum      = document.getElementById("classroom").dataset.supremum;
	var default_bg    = 0x000080; // dunkelblau
	var busy 	  	  = 0x990000; // dunkelrot 
	var help 	  	  = 0xffd700; // gelb 
	var slower		  = 0xfcc9b9; // slower
	var finished  	  = 0x008000; // dunkelgrün

	// zur Zeit nicht verwendet
	var busy_help  	  = 0xFF6600; // orange 
	var finished_help = 0x66E600; // hellgrün 
	
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

