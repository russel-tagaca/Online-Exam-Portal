function getScores(){
	var uniqueID = sessionStorage.getItem("uniqueExam");
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var data = this.responseText;
			 var jsonResponse = JSON.parse(data);
			 //alert(data);
			 loadScores(jsonResponse);
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xhttp.send(`uniqueID=${uniqueID}`);
}

function loadScores(examScores){
	var scoresForm = document.getElementById("scores");
	var submitBtn = document.getElementById("submitButton");
	var studentName = examScores[0];
	var examID = examScores[1];
	var uniqueID = sessionStorage.getItem("uniqueExam");
	
	var examTable = document.getElementById("ExamTable");
	examTable.setAttribute("id", "examTable");
	for(var i = 2; i < examScores.length; i+=6){
			var question = examScores[i];
			var studentAnswer = examScores[i+1];
			var grade = examScores[i+2];
			var comments = examScores[i+3];
			var studentOutput = examScores[i+4] // i+4 is an array of students output
			var expectedAnswers = examScores[i+5] // i+5 is an array of expected answers
		
			var tableRow = document.createElement("tr");
			
            var tableElementQuestion = document.createElement("td");
			tableElementQuestion.setAttribute("id","tdQuestion");
			
            var tableElementAnswer = document.createElement("td");
            tableElementAnswer.setAttribute("id","tdAnswer");
			
            var tableElementDeductions = document.createElement("td");
            tableElementDeductions.setAttribute("id","tdDeductions");
			
            var tableElementGrade = document.createElement("td");
            tableElementGrade.setAttribute("id","tdGrade");
			
			var questionPara = document.createElement("p");
			questionPara.setAttribute("id", "question");
			var questionParaText = document.createTextNode(question);
			questionPara.appendChild(questionParaText);
			tableElementQuestion.appendChild(questionPara);
		
			var studentAnsTextArea = document.createElement("TEXTAREA");
			studentAnsTextArea.defaultValue = studentAnswer;
		    studentAnsTextArea.readOnly = true;
			studentAnsTextArea.setAttribute("name", "examRevision[]");
			studentAnsTextArea.setAttribute("class", "studentAnsClass");
			tableElementAnswer.appendChild(studentAnsTextArea);
			
			var gradeInput = document.createElement("INPUT");
			gradeInput.defaultValue = grade;
			gradeInput.setAttribute("type", "number");
			gradeInput.setAttribute("id", "gradeInput");
			gradeInput.setAttribute("min", "0");
			gradeInput.setAttribute("max", "100");
			gradeInput.setAttribute("name", "examRevision[]");
			tableElementGrade.appendChild(gradeInput);
			
			var cmtsTextArea = document.createElement("TEXTAREA");
			cmtsTextArea.defaultValue = comments;
			cmtsTextArea.setAttribute("name", "examRevision[]");
			cmtsTextArea.setAttribute("id", "comments");
			tableElementDeductions.appendChild(cmtsTextArea);
		
			tableRow.appendChild(tableElementQuestion);
			tableRow.appendChild(tableElementAnswer);
			tableRow.appendChild(tableElementGrade);
			tableRow.appendChild(tableElementDeductions);
			gradeReport(studentOutput,expectedAnswers,tableRow);
			examTable.appendChild(tableRow);
	}
	var studentNameInput = document.createElement("INPUT");
	studentNameInput.defaultValue = studentName;
	studentNameInput.setAttribute("type", "hidden");
	studentNameInput.setAttribute("name", "examRevision[]");
	
	var examIDInput = document.createElement("INPUT");
	examIDInput.defaultValue = examID;
	examIDInput.setAttribute("type", "hidden");
	examIDInput.setAttribute("name", "examRevision[]");
	
	var uniqueIDInput = document.createElement("INPUT");
	uniqueIDInput.defaultValue = uniqueID;
	uniqueIDInput.setAttribute("type", "hidden");
	uniqueIDInput.setAttribute("name", "examRevision[]");
	
	scoresForm.appendChild(studentNameInput);
	scoresForm.appendChild(examIDInput);
	scoresForm.appendChild(uniqueIDInput);
}
function gradeReport(studentOut, expectedAns, row){
	var outputTable = document.createElement("table");
	outputTable.setAttribute("id", "outputTable");
	row.appendChild(outputTable);
	
	var tableHeaderExpected = document.createElement("th");
	tableHeaderExpected.setAttribute("id", "Expected");
	tableHeaderExpected.appendChild(document.createTextNode("Expected"))
	
	var tableHeaderStudent = document.createElement("th");
	tableHeaderStudent.setAttribute("id", "Run");
	tableHeaderStudent.appendChild(document.createTextNode("Run"))
	
	outputTable.appendChild(tableHeaderExpected);
	outputTable.appendChild(tableHeaderStudent);
	
	for(var i = 0; i < studentOut.length; i++){
		var tableRowOutputs = document.createElement("tr");
		tableRowOutputs.setAttribute("id", "outputs");
		
		var expectedOutput = document.createElement("p");
		expectedOutput.setAttribute("class", "expectedOutput");
		var expectedOutputText = document.createTextNode(expectedAns[i]);
		expectedOutput.appendChild(expectedOutputText);
		
		var tableElementExpect = document.createElement("td");
		tableElementExpect.setAttribute("class", "expectedTableElmOutput");
		tableElementExpect.appendChild(expectedOutput);
		tableRowOutputs.appendChild(tableElementExpect);
		
		var stOutput = document.createElement("p");
		stOutput.setAttribute("class", "studentOutput");
		var stOutputText = document.createTextNode(studentOut[i]);
		stOutput.appendChild(stOutputText);
		
		var tableElementStud = document.createElement("td");
		tableElementStud.setAttribute("class", "studentTableElemOutput");
		tableElementStud.appendChild(stOutput);
		tableRowOutputs.appendChild(tableElementStud);
		
		var tableElementResult = document.createElement("td");
		var tableElementColor = document.createElement("td");
		if( studentOut[i] == expectedAns[i] ){
			tableElementResult.appendChild(document.createTextNode("CORRECT"));
			tableElementColor.setAttribute("class", "correctAns");
		}else{
			tableElementResult.appendChild(document.createTextNode("WRONG"));
			tableElementColor.setAttribute("class", "wrongAns");
		}
		tableRowOutputs.appendChild(tableElementResult);
		tableRowOutputs.appendChild(tableElementColor);
		outputTable.appendChild(tableRowOutputs);
	}
}

function submitExam(){
	var xhttp = new XMLHttpRequest();
	var examForm = document.getElementById("scores");
	var formData = new FormData(examForm);

	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var data = this.responseText;
			 
			 document.body.removeChild(examForm);
			 var para = document.getElementById("response");
			 para.appendChild(document.createTextNode("Exam grades posted"));
			 //sleep(3000); //milliseconds
			 //window.location = "https://web.njit.edu/~efc9/cs490/instructor/instructorRevise.html";
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.send(formData);
}



function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}