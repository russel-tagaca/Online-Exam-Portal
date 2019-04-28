//This function gets questions from the question bank and populates a form with all the available questions
function getQuestions(){
	var currentUser = sessionStorage.getItem("currentUser");
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var data = this.responseText;
			 var jsonResponse = JSON.parse(data);
			 makeQBank();
			 for( var i in jsonResponse ){
				loadQuestionBank(jsonResponse[i], +i+1);
			}
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xhttp.send(`submit=submit&currentUser=${currentUser}`);
}

//Creates a form that handles selecting questions for an exam
function makeQBank(){
	var mybr = document.createElement('br');
	
	var qBankForm = document.createElement('form');
	qBankForm.setAttribute("id", "choice");
	qBankForm.setAttribute("action", "javascript:previewExam()");
	qBankForm.setAttribute("accept-charset", "utf-8");

	var previewExamBtn = document.createElement("INPUT");
	previewExamBtn.setAttribute("type", "submit");
	previewExamBtn.setAttribute("name", "submit");
	previewExamBtn.setAttribute("class", "myButton");
	previewExamBtn.setAttribute("value", "Preview Exam");
	previewExamBtn.setAttribute("id", "previewExamButton");
	
	var clearPreviewBtn = document.createElement("INPUT");
	clearPreviewBtn.setAttribute("type", "button");
	clearPreviewBtn.setAttribute("name", "submit");
	clearPreviewBtn.setAttribute("class", "myButton");
	clearPreviewBtn.setAttribute("value", "Clear Preview");
	clearPreviewBtn.setAttribute("id", "clearPreviewButton");
	clearPreviewBtn.setAttribute("onclick", "clearPreview()");

	document.getElementById("makeExamDiv").appendChild(qBankForm);
	document.getElementById("choice").appendChild(previewExamBtn);
	document.getElementById("choice").appendChild(clearPreviewBtn);
	document.getElementById("choice").appendChild(mybr);
}
function clearPreview(){
		var previewForm = document.getElementById("preview");
		var previewFormChildren = previewForm.children;
		
		while(previewForm.length > 1){
				 previewForm.removeChild(previewForm.firstChild);
		}
}

//This function loads all the questions that were submitted to the question bank
function loadQuestionBank (question, num) { 
	var mybr = document.createElement('br');
	var questionTextNode = document.createTextNode(question);
	var previewExamBtn = document.getElementById("previewExamButton");

	var exmQuestion = document.createElement("INPUT"); 
	exmQuestion.setAttribute("type", "checkbox");
	exmQuestion.setAttribute("name", "formCheck[]");
	exmQuestion.setAttribute("id", "question"+num);
	exmQuestion.setAttribute("value", question);
	
	var questionLabel = document.createElement("LABEL");
	questionLabel.setAttribute("for", "question"+num);
	questionLabel.setAttribute("id", num);

	document.getElementById("choice").insertBefore(exmQuestion, previewExamBtn);
	document.getElementById("choice").insertBefore(questionLabel, previewExamBtn);
	document.getElementById(num).appendChild(questionTextNode);
	document.getElementById("choice").insertBefore(mybr, previewExamBtn);
}

function loadExamPreview(question, num) {
	//ran is used to make random id numbers
	//var ran = Math.random() * num;
	var mybr = document.createElement('br');
	var questionTextNode = document.createTextNode(question);
	var makeExamBtn = document.getElementById("makeExamButton");
	
	var questionPoints = document.createElement("INPUT"); 
	questionPoints.setAttribute("type", "number");
	questionPoints.setAttribute("min", "1");
	questionPoints.setAttribute("max", "100");
	questionPoints.setAttribute("name", "examQuestions[]");
	//questionPoints.setAttribute("id", "question"+ran);
	questionPoints.required = true;
	
	/*var questionLabel = document.createElement("LABEL");
	questionLabel.setAttribute("for", "question"+ran);
	questionLabel.setAttribute("id", ran);*/
	
	var questionLabel = document.createElement("p");
	questionLabel.appendChild(questionPoints);
	questionLabel.appendChild(questionTextNode);
	
	
	var exmQuestion = document.createElement("INPUT");
	exmQuestion.setAttribute("type", "hidden");
	exmQuestion.setAttribute("value", question);
	exmQuestion.setAttribute("name", "examQuestions[]");
	
	//document.getElementById("preview").insertBefore(questionPoints, makeExamBtn);
	//document.getElementById("preview").insertBefore(questionLabel, makeExamBtn);
	document.getElementById("preview").insertBefore(questionLabel, makeExamBtn);
	document.getElementById("preview").insertBefore(exmQuestion, makeExamBtn);
	//document.getElementById(ran).appendChild(questionTextNode);
	document.getElementById("preview").insertBefore(mybr, makeExamBtn);
}

//This function takes the questions you chose and makes an exam for you with those questions
function makeExam(){
	var xhttp = new XMLHttpRequest();
	var examPreviewForm = document.getElementById("preview");
	var formData = new FormData(examPreviewForm);

	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var data = this.responseText;
			 //var jsonResponse = JSON.parse(data);
			 var previewExam = document.getElementById("preview")
			 var div = document.getElementById("examPreview");
			
			 var paraResp = document.createElement("p");
			 paraResp.setAttribute("id", "examSubmitted");
			 paraResp.appendChild(document.createTextNode(data));
			 //div.removeChild(previewExam);
			 while(previewExam.length > 1){
				 previewExam.removeChild(previewExam.firstChild);
			 }
			 div.appendChild(paraResp);
			 
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.send(formData);
}

//Submites questions to the question bank
//Questions are being duplicated in question bank
function submitQuestion(){
	var xhttp = new XMLHttpRequest();
	var makeQuestionsForm = document.getElementById("question");
	var formData = new FormData(makeQuestionsForm);
	
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			var data = this.responseText;
			var submitQuestionForm = document.getElementById("question");
			//alert(data);
			window.location.reload();
			submitQuestionForm.reset();
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.send(formData);
 }

//Makes a preview exam from the choosen questions
function previewExam(){
	var qBankForm = document.getElementById("choice");
	var div = document.getElementById("examPreview");
	var qBankChildren = qBankForm.children;
	
	/*if(div.childNodes.length != 1){
		//Comparing not equals 1 becuase an empty div still has a text node
		if(div.contains(document.getElementById("preview"))) {
			div.removeChild(document.getElementById("preview"));
		}
		if(div.contains(document.getElementById("examSubmitted"))) {
			div.removeChild(document.getElementById("examSubmitted"));
		}
	}
	makeExamPreview();*/
	
	//If an exam was submitted this removes the exam submitted text
	if( document.getElementById("examSubmitted") != null )
		div.removeChild(document.getElementById("examSubmitted"));
	for (var i = 0; i < qBankChildren.length; i++) {
		if( qBankChildren[i].checked ){
			loadExamPreview(qBankChildren[i].value, i);
		}
	}
	qBankForm.reset();
}

//Handles filtering question bank by difficulty and type
function filterQuestions(diff,type){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			var data = this.responseText;
			var jsonResponse = JSON.parse(data);
			
			var qBankForm = document.getElementById("choice");
			document.getElementById("makeExamDiv").removeChild(qBankForm);

			if( jsonResponse[0]=="Unavailable" ){
				var para = document.getElementById("response");
			 	para.appendChild(document.createTextNode("No questions"));	
			}
			else{
				makeQBank();
				for( var i in jsonResponse ){
					loadQuestionBank(jsonResponse[i], +i+1);
				}
			}
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xhttp.send(`diff=${diff}&type=${type}`);
}

//Handles resetting the question bank to list all questions
function resetQBank(){
		var div = document.getElementById("makeExamDiv");
		if(div.contains(document.getElementById("choice"))) {
			div.removeChild(document.getElementById("choice"));
			getQuestions();
		}
		else{
			var para = document.getElementById("response");
			para.removeChild(para.childNodes[0]);
			getQuestions();
		}
}