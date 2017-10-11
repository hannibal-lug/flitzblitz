var timer=null;

function FensterOeffnen (Adresse) { 
MeinFenster = window.open(Adresse, "Zweitfenster", "width=300,height=100,left=10,top=10,menubar=no,location=no,resizeable=no" ); 
//MeinFenster.document.write("<p>ein neues Fenster!<br>(Besser wÃ¤re aber eine dialog-Box!)</p>"); 
MeinFenster.focus(); 
}
function refresh() {
	timer = null;
	document.location.replace("report.php");
}
function ShowReceiver (){
	if (document.forms["messanger"].sendMessage.value=="Neue Textnachricht schreiben"){
		if (timer) {
			clearTimeout(timer);
			timer = null;
		}
		document.getElementById("MessageBlock").style.visibility="hidden";
		document.getElementById("SendTo").style.visibility="visible";
		document.forms["messanger"].sendMessage.value="An wen senden?"
	}
	
}
		
function ChooseReceiver (receiver){
	if (document.forms["messanger"].sendMessage.value=="An wen senden?"){
		document.getElementById("SendTo").style.visibility="hidden";
		document.getElementById("Message").style.visibility="visible";
		document.forms["messanger"].receiver.value=receiver;
		document.forms["messanger"].sendMessage.value="Jetzt Nachricht schreiben und senden";
	}
}

function SubmitMessage (){
	textarea= document.forms["messanger"].TMessage.value;
	if (document.forms["messanger"].sendMessage.value=="Jetzt Nachricht schreiben und senden" && textarea.length>0){
		document.forms["messanger"].submit();
	}
	else if (document.forms["messanger"].sendMessage.value=="Jetzt Nachricht schreiben und senden") 
		alert ("Bitte eine Nachricht eingeben!");
}
function getValue(){
	var e = document.getElementById("classs");
	return e.options[e.selectedIndex].text;
}
