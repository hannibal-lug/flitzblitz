/**
 * 
 */

//		var state = <?php // echo $_SESSION['state'] ?>;
		var stud     = document.getElementById("student");
		var student  = stud.value;
		var room     = stud.dataset.location;
		
//		document.onreadystatechange = function() {
//		     if (document.readyState === 'complete') {
//		        check_status();
//		     }
//		}
//		
//		window.onload = check_status();
		
//		<?php // echo session_get_next_state()?>

		function toggle_switch() {
			var c = document.getElementById("switch1");
			if (c.checked) {
				document.bgColor = "#288643";
			} else {
				document.bgColor = "#A61A1A";
			}
//			alert("update_state");
			xhr = new XMLHttpRequest();
			xhr.open("GET", "update_status.php?student="+student+"&state="+c.checked+"&location="+room, true);
			xhr.send();
		}

		function check_status() {
			var xhr = new XMLHttpRequest();
			var c = document.getElementById("switch1");
			xhr.open("POST", "get_status.php?location="+room+"&student="+student, true);
			xhr.onreadystatechange = function () {
				if (this.readyState == 4 && this.status == 200) {
					switch (this.responseText) {
						case 'finished':
							set_switch_status('enabled');
							c.checked = true;
							document.bgColor = "#288643";
							break;
						case 'busy':
							set_switch_status('enabled');
							c.checked = false;
							document.bgColor = "#A61A1A";
							break;
						case 'inactive':
							set_switch_status('disabled');
							break; 
					}
// 					alert(JSON.parse(this.responseText) ? 'yay' : 'ney');
// 					alert((this.responseText == "1") ? 'yay' : 'ney');
// 					alert(typeof this.responseText);
// 					if (!this.responseText) set_switch_status('disabled');
// 					if (JSON.parse(this.responseText)) set_switch_status('enabled');
// 					else set_switch_status('disabled');
// 					if (status != this.responseText) {
// 						update_state();
// 					}
					setTimeout(check_status, 10000);
				}
			};
			xhr.send();
		}

		function set_switch_status(state) {
			var sw = document.getElementById('switch1');

			switch (state) {
				case 'enabled':
					if (!sw.disabled) break;
					sw.disabled = false;
					document.bgColor = "#288643";
					break;
				case 'disabled':
					if (sw.disabled) break;
					sw.disabled = true;
					document.bgColor = "#606060";
					break;
			}
			
// 			if (state == 'enabled) {
// 				sw.disabled = false;
// 				if (sw.checked) {
// 					document.bgColor = "#288643";
// 				} else {
// 					document.bgColor = "#A61A1A";
// 				}
// 			} else if (state == 'disabled') {
// 				sw.disabled = true;
// 				document.bgColor = "#606060";
// 			}
		}

		function switch_switch(status) {
			var sw = document.getElementById('switch1');
// 			document.getElementsByClassName('switch-label')[0].style.backgroundColor = '#7b7b7b';
			if (status == 'enabled') {
				sw.disabled = false;
				if (sw.checked) {
					document.bgColor = "#288643";
				} else {
					document.bgColor = "#A61A1A";
				}
			}
			else {
				sw.disabled = true;
				document.bgColor = "#606060";
			}
// 			if (sw.disabled) {
// 				sw.disabled = false;
// 				if (sw.checked) {
// 					document.bgColor = "#288643";
// 				} else {
// 					document.bgColor = "#A61A1A";
// 				}
// 			} else {
// 				sw.disabled = true;
// 				document.bgColor = "#606060";
// 			}
		}
		
		function update_state() {
// 			alert(state);
//			if (<?php //echo $_SESSION['state']?>) // alert('yay'); else alert('ney');
//			alert('<?php // echo $_SESSION['state']?> //');
// 			alert("update_state");
// 			alert(document.form1.elements['student'].value);
// 			alert(document.getElementById("student").value);
			var c = document.getElementById("switch1");
			var xhr = new XMLHttpRequest();

			switch (state) {
				case 0:
					state = 1; break;
				case 1:
					state = 0; break;
			}
// 			alert(state);
			xhr.open("POST", "update_status.php?student="+student+"&state="+state, true);
			xhr.onreadystatechange = function () {
				if (this.readyState == 4 && this.status != 200) {
					alert('update_state_failed');
				} 
			};
			xhr.send();
			
			if (c.checked) {
				document.bgColor = "#288643";
			} else {
				document.bgColor = "#A61A1A";
			}
		}
		function frigging_ajax() {
			document.getElementById("frigging_ajax").innerHTML = "frigging ajax";
			alert("ajax!");
			var xmlhttp = new XMLHttpRequest();
	        xmlhttp.onreadystatechange = function() {
	            if (this.readyState == 4 && this.status == 200) {
	                document.getElementById("frigging_ajax").innerHTML = this.responseText;
	            }
	        };
	        xmlhttp.open("POST", "frigging_ajax.php?bla=blub", true);
	        xmlhttp.send();
		}
		function flip_switch(status) {
			var c = document.getElementById("switch1");
			switch (status) {
				case 'done':
					c.checked = true;
					document.bgColor = "#288643";
					alert(document.getElementById("student").value);
					update_state(document.getElementById("student").value, 1);
					alert("update_state("+document.getElementById("student").value+", "+1+")");
					break;
				case 'working':
					c.checked = false;
					document.bgColor = "#A61A1A";
					update_state(document.getElementById("student").value, 0);
					alert("update_state("+document.getElementById("student").value+", "+0+")")
					break;
			}
		}
		
		function set_button_state(state) {
			var c = document.getElementById("switch1");
			switch (state) {
				case 'finished':
					c.checked = true;
					document.bgColor = "#288643";
					break;
				case 'working':
					c.checked = false;
					document.bgColor = "#A61A1A";
					break;							
			}
		}
		// debug code
		function myaction() {
				document.form1.action="update_status.php?student=190&state=1";
				document.form1.submit();
		}
