//This function gets all the available exams from the server and sutomatically fills up a form with choices
function getExams(){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var data = this.responseText;
			 var jsonResponse = JSON.parse(data);
			 //UnComment this line to see out put from server
			 //document.getElementById("Database").innerHTML = sessionStorage.getItem("currentUser");;
			 for( var i in jsonResponse ){
				addExamButtons(jsonResponse[i]);
			}
			addButton();
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xhttp.send(`exam=Choose Exam`);
}

//This function is called only when exam choices are loaded and is ment to add a submit button for the exam choice
function addButton(){
	 var mybr = document.createElement('br');
	 var newButton = document.createElement("INPUT");

	 newButton.setAttribute("id", "Select");
	 newButton.setAttribute("type", "submit");
	 newButton.setAttribute("name", "submit");
	 newButton.setAttribute("value", "Select");
	 newButton.setAttribute("class", "myButton");

	 document.getElementById("examChoices").appendChild(mybr);
	 document.getElementById("examChoices").appendChild(mybr);
	 document.getElementById("examChoices").appendChild(newButton);
}

//This function is called from getExams() and is used to create radio buttons for all the available exams
function addExamButtons (examNum) {
	var ran = Math.random();
	var mybr = document.createElement('br');
	
	var examOptions = document.createElement("INPUT"); 
	examOptions.setAttribute("type", "radio");
	examOptions.setAttribute("id", "examNum");
	examOptions.setAttribute("name", "examNumber");
	examOptions.setAttribute("value", examNum);


	var radioText = document.createTextNode("Exam: " + examNum);
	var radioLabel = document.createElement("label");
	radioLabel.appendChild(radioText);
	radioLabel.appendChild(examOptions);
	
	document.getElementById("examChoices").appendChild(radioLabel);
	document.getElementById("examChoices").appendChild(mybr);
}

//This function handles sending you to a new page to take the exam that you have choosen
function takeExam(){
		var exNum = document.querySelector('input[name="examNumber"]:checked').value; 
		sessionStorage.setItem("examID", exNum);
		window.location.href = "Exam.html";
}