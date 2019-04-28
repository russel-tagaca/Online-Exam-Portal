function scores(){
	var student = sessionStorage.getItem("currentUser");
	var uniqueExam = sessionStorage.getItem("uniqueExam");
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var data = this.responseText;
			 //alert(data);
			 var jsonResponse = JSON.parse(data);
			 
			 loadScores(jsonResponse);
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xhttp.send(`student=${student}&uniqueExam=${uniqueExam}`);
}

function loadScores(examScores){
	var examTable = document.getElementById("ExamTable");
	var studentTable = document.getElementById("studentTable");
	var student = sessionStorage.getItem("currentUser");
	
	var tableRow = document.createElement("tr");
	var tableElementUser = document.createElement("td");
	tableElementUser.appendChild(document.createTextNode(student));
	var tableElementFinalGrade = document.createElement("td");
	tableElementFinalGrade.appendChild(document.createTextNode(examScores[examScores.length-1] + "%"));
	
	studentTable.appendChild(tableRow);
	tableRow.appendChild(tableElementUser);
	tableRow.appendChild(tableElementFinalGrade);
	for(var i = 0; i < examScores.length-1; i+=6){
			var question = examScores[i];
			var studentAnswer = examScores[i+1];
			var grade = examScores[i+2];
			var comments = examScores[i+3];
			var studentOutput = examScores[i+4] // something is wrong with student output
			var expectedAnswers = examScores[i+5]
		
			var tableRow = document.createElement("tr");
			var tableElementQuestion = document.createElement("td");
			tableElementQuestion.setAttribute("id","tdQuestion");
			var tableElementAnswer = document.createElement("td");
			var tableElementDeductions = document.createElement("td");
			var tableElementGrade = document.createElement("td");
		
			var questionTextPara = document.createElement("p");
			questionTextPara.setAttribute("class", "quesDedu");
			var questionText = document.createTextNode(question);
			questionTextPara.appendChild(questionText);
			tableElementQuestion.appendChild(questionTextPara);
		
			var studentAnswerPara = document.createElement("p");
			studentAnswerPara.setAttribute("class", "studentAnsClass");
			var studentAnswerText = document.createTextNode(studentAnswer);
			studentAnswerPara.appendChild(studentAnswerText);
			tableElementAnswer.appendChild(studentAnswerPara);
		
			var commentsTextPara = document.createElement("p");
			commentsTextPara.setAttribute("class", "quesDedu");
			var commentsText = document.createTextNode(comments);
			commentsTextPara.appendChild(commentsText);
			tableElementDeductions.appendChild(commentsTextPara);
			
			var gradeText = document.createTextNode(grade);
			tableElementGrade.appendChild(gradeText);
			tableElementGrade.setAttribute("id", "grade");
		
			tableRow.appendChild(tableElementQuestion);
			tableRow.appendChild(tableElementAnswer);
			tableRow.appendChild(tableElementDeductions);
			tableRow.appendChild(tableElementGrade);
			gradeReport(studentOutput,expectedAnswers,tableRow);
			examTable.appendChild(tableRow);
	}
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
