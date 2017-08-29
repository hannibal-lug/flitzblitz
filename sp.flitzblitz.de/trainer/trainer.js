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
