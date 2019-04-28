function getExams(){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var data = this.responseText;
			 var jsonResponse = JSON.parse(data);
			if( jsonResponse.length == 0 ){
				var para = document.getElementById("response");
			 	para.appendChild(document.createTextNode("No Exams"));
			 }
			 else{
			 	addExamButtons(jsonResponse);
				addButton();
			 }
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xhttp.send(`getExams=Choose Exam&student=efc9`);
}
//This function is called only when exam choices are loaded and is ment to add a submit button for the exam choice
function addButton(){
	 var mybr = document.createElement('br');
	 var newButton = document.createElement("INPUT");

	 newButton.setAttribute("type", "submit");
	 newButton.setAttribute("class", "myButton");
	 newButton.setAttribute("name", "submit");
	 newButton.setAttribute("value", "Select");

	 document.getElementById("examChoices").appendChild(newButton);
	 document.getElementById("examChoices").appendChild(mybr);
}

//This function is called from getExams() and is used to create radio buttons for all the available exams
function addExamButtons (examNum) {
	  var examChoices = document.getElementById("examChoices");	
	  for(var i = 0; i < examNum.length; i+=3){
		  	var uniqueExam = examNum[i];
		  	var studentName = examNum[i+1];
		  	var examID = examNum[i+2];
		  
			var mybr = document.createElement('br');
		  
	  		var examOptions = document.createElement("INPUT");
	  		var radioLabel = document.createTextNode("Exam: " + examID);
		  	examOptions.setAttribute("type", "radio");
		  	examOptions.setAttribute("id", uniqueExam);
	  		examOptions.setAttribute("name", "examChoice");
	  		examOptions.setAttribute("value", examID);
		  
		  	var student = document.createElement("p");
		  	student.setAttribute("id", i);
		  
		  	var studentText = document.createTextNode(studentName);
		  
		  	document.getElementById("examChoices").appendChild(student);
		  	document.getElementById(i).appendChild(studentText);
		  	document.getElementById("examChoices").appendChild(examOptions);	
	  		document.getElementById("examChoices").appendChild(radioLabel);
	  		document.getElementById("examChoices").appendChild(mybr);
	  }
}

//This function handles sending you to a new page to take the exam that you have choosen
function chooseExam(){
		var checkedExam = document.querySelector('input[name="examChoice"]:checked'); 
		var checkedExamID = checkedExam.id;
		sessionStorage.setItem("uniqueExam", checkedExamID);
		window.location.href = "instructorScores.html";
}