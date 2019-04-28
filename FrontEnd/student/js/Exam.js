//This function is used to get load the exam onto the page
function getExam(){
	var examID = sessionStorage.getItem("examID");
	var examHeader = document.createTextNode("Exam: " + examID);
	document.getElementById("examTitle").appendChild(examHeader);

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var data = this.responseText;
			 var jsonResponse = JSON.parse(data);
			 //Uncomment this line to see responses from the database
			 //document.getElementById("Database").innerHTML = data;

			var questionNum = 1;
			for(var i = 0; i < jsonResponse.length; i++){
				if( i % 2 == 0 ){
					addExamQuestion(jsonResponse[i], questionNum, jsonResponse[+i+1]);
					questionNum++;
				}
			}
			finalizeForm();
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xhttp.send(`examID=${examID}`);
}

//This function creates a button for specifically for the Exam form and some hidden fields
function finalizeForm(){
	var mybr = document.createElement('br');
	
	var submitExamBtn = document.createElement("INPUT"); 
	submitExamBtn.setAttribute("id", "submit");
	submitExamBtn.setAttribute("type", "submit");
	submitExamBtn.setAttribute("name", "submit");
	submitExamBtn.setAttribute("value", "Submit Exam");
	
	var currentUser = sessionStorage.getItem("currentUser");
	var studentName = document.createElement("INPUT");
	studentName.setAttribute("type", "hidden");
	studentName.setAttribute("id", "student");
	studentName.setAttribute("name", "studentAnswers[]");
	studentName.setAttribute("value", currentUser);

	var examID = sessionStorage.getItem("examID");
	var examIDToServer = document.createElement("INPUT");
	examIDToServer.setAttribute("type", "hidden");
	examIDToServer.setAttribute("id", "IDNum");
	examIDToServer.setAttribute("name", "studentAnswers[]");
	examIDToServer.setAttribute("value", examID);

	document.getElementById("Exam").appendChild(submitExamBtn);
	document.getElementById("Exam").appendChild(mybr);

	document.getElementById("Exam").appendChild(studentName);
	document.getElementById("student").readOnly = true;
	document.getElementById("Exam").appendChild(mybr);

	document.getElementById("Exam").appendChild(examIDToServer);
	document.getElementById("IDNum").readOnly = true;
	document.getElementById("Exam").appendChild(mybr);
}

//This function is used to add text areas for each exam question
function addExamQuestion(question, num, points) {
	var mybr = document.createElement('br');
	 
	var newTextArea = document.createElement("TEXTAREA");
	newTextArea.setAttribute("id", num);
	newTextArea.setAttribute("name", "studentAnswers[]");
	//This attribute forces the tab key to indent instead of going to the next element
	newTextArea.setAttribute("onkeydown", "if(event.keyCode===9){var v=this.value,s=this.selectionStart,e=this.selectionEnd;this.value=v.substring(0, s)+'\t'+v.substring(e);this.selectionStart=this.selectionEnd=s+1;return false;}");
	newTextArea.required = true;
	
	var Question = document.createElement("INPUT");
	Question.setAttribute("type", "hidden");
	Question.setAttribute("value", question);
	Question.setAttribute("name", "studentAnswers[]");
	
	var examQuestion = document.createTextNode(num + ". " + question + " (" + points + "pts)"); 
	var questionPara = document.createElement("p");
	questionPara.setAttribute("id", question);
	
	document.getElementById("Exam").appendChild(questionPara);
	document.getElementById(question).appendChild(examQuestion);
	document.getElementById("Exam").appendChild(mybr);
	document.getElementById("Exam").appendChild(newTextArea);
	document.getElementById("Exam").appendChild(Question);
	document.getElementById("Exam").appendChild(mybr);
}

//This function submits the completed exam to the server to be saved and graded
function finishExam(){
	var examForm = document.getElementById("Exam");
	var formData = new FormData(examForm);

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var data = this.responseText;
			 //var jsonResponse = JSON.parse(data);
			 document.body.removeChild(examForm);
			 document.getElementById("Database").innerHTML = data;
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/grading.php",true);
	xhttp.send(formData);
}