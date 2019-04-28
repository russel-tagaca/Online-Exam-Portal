<?php
	header('Content-type:application/json;charset=utf-8');
	ini_set('display_errors', 1);
	function createConnection($url, $data){
		$ch = curl_init($url);
		$postString = http_build_query($data, '', '&');
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		return $ch;
	}

	//Send login credentials to database
	function loginDB($username, $password){
		$currentUser = $_POST["currentUser"];
		$data = array(
			"uname" => $username,
			"psw" => $password,
			"currentUser" => $currentUser
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testBack.php", $data);

		$response = json_encode(curl_exec($ch));
		curl_close($ch);
		return $response;
	}
	//Send login credentials to NJIT
	function loginNJIT($username, $password){
		$data = array(
			"ucid" => $username,
			"pass" => $password
		);
		$ch = createConnection("https://aevitepr2.njit.edu/myhousing/login.cfm", $data);

		$njitTest = curl_exec( $ch );
		curl_close($ch);

		if(strlen($njitTest) == 78){
			return "NJIT: Successful Login";
		}
		else{
			return "NJIT: Invalid Login";
		}
	}
	
	//Handl question submisso from instructor
	function submitQustions($questions){
		$data = array(
			"questions" => $questions
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testExamBack.php", $data);

		$response = json_encode(curl_exec($ch));
		curl_close($ch);
		return $response;
	}
	
	//Handle returning questions from question bank
	function getQuestionBank($qBank){
		$currentUser = $_POST["currentUser"];
		$data = array(
			"qBank" => $qBank,
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testQBank.php", $data);

		$response = explode(".[^&*]", (curl_exec($ch)));
		$questions = json_encode($response);
		curl_close($ch);
		return $questions;
	}
	
	//Handle submiting questions to make an exam
	function makeExam($examQuestions){
		$data = array(
			"exam" => $examQuestions
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testQBank.php", $data);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
	//Gets the exam IDs for printing the list of available exams to the student
	function getExamIDs($examID){
		$data = array(
			"exam" => $examID
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);

		$response = explode(".[^&*]", (curl_exec($ch)));
		$examDB = json_encode($response);
		curl_close($ch);
		return $examDB;
	}
	
	//Returns the appropriate exam to Exam.html for the student to take their test
	function getExam($exam){
		$data = array(
			"examID" => $exam
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);

		$response = explode(".[^&*]", (curl_exec($ch)));
		$examDB = json_encode($response);
		curl_close($ch);
		return $examDB;
	}
	
	//Returns the newly filtered questions
	function filterQBank($diff, $type){
		$data = array(
			"diff" => $diff,
			"type" => $type
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testQBank.php", $data);

		$response = explode(".[^&*]", (curl_exec($ch)));
		$questions = json_encode($response);
		curl_close($ch);
		return $questions;
	}

	//Handles returning a list of graded student exams
	function getGrades($student){
		$data = array(
			"studentName" => $student
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	//Instructor chose an exam to revise
	function instructorGetExams($getExams){
		$data = array(
			"getExams" => $getExams
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
		
		$response = json_decode(curl_exec($ch));
		curl_close($ch);
		return $response;
	}
	//Instructor chose an exam to revise
	function reviseExam($choosenExam){
		$data = array(
			"choosenExam" => $choosenExam,
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
		
		$response = curl_exec($ch);
        //echo $response;
		curl_close($ch);
		return $response;
	}
	//Instructor submits revised exam
	function submitRevision($revisedExam){
		$data = array(
			"revisedExam" => $revisedExam
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
		
		$response = json_encode(curl_exec($ch));
		curl_close($ch);
		return $response;
	}
	//Student is viewing their exam results
	function getScores($student, $uniqueExam){
		$data = array(
			"getStudentScores" => $student,
			"uniqueExam" => $uniqueExam
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
		
		$response = curl_exec($ch);
		$response = json_decode($response);
		curl_close($ch);
		$finalGrade = 0;
		for($j = 2; $j < sizeof($response); $j+=6){
			$finalGrade += $response[$j];
		}
		$response[sizeof($response)] = $finalGrade;
		$response = json_encode($response);
		return $response;
	}

	//Checks to see if we are logging in
	if( isset($_POST["uname"]) && isset($_POST["psw"]) ){
		$dbResponse = loginDB( $_POST["uname"], $_POST["psw"] );
		//$njitResponse = loginNJIT( $_POST["uname"], $_POST["psw"] );
		echo $dbResponse;
	}
	//Checks to see if we are submitting questions to the question bank
	else if( isset($_POST["questions"]) ){
		$sendQuestions = submitQustions($_POST["questions"]);
		echo $sendQuestions;
	}
	//Checks to see if we need to get questions from the question bank
	else if( isset($_POST["submit"]) ){
		$retrieveQustions = getQuestionBank( $_POST["submit"] );
		echo $retrieveQustions;
	}
	//Checks to see if we need to supply exams to the student home page
	else if( isset($_POST["exam"]) ){
		$retrieveExamIDs = getExamIDs( $_POST["exam"] );
		echo $retrieveExamIDs;
	}
	//Checks to see if we need to load an exam for the student to take
	else if( isset($_POST["examID"]) ){
		$retrieveExam = getExam( $_POST["examID"] );
		echo $retrieveExam;
	}
	//Checks to see if the question bank needs to be filtered
	else if( isset($_POST['diff']) && isset($_POST['type']) ){
		$filterResponse = filterQBank( $_POST['diff'], $_POST['type']);
		echo $filterResponse;
	}
	//Checks to see if we are going to make an exam for a student to take
	else if( isset($_POST['examQuestions'])){
		$examRespone = makeExam($_POST['examQuestions']);
		echo $examRespone;
	}
	//Checks to see if the instructor chose an exam to make revisions
	else if( isset($_POST['getExams'])){
		$Respone = instructorGetExams($_POST['getExams']);
		echo json_encode($Respone);
	}
	//Checks to see if the instructor chose an exam to make revisions
	else if( isset($_POST['uniqueID'])){
		$Respone = reviseExam($_POST['uniqueID']);
		echo $Respone;
	}
	//Checks to see if instructor is submitting a revised exam
	else if( isset($_POST['examRevision']) ){
		$response = submitRevision($_POST['examRevision']);
		echo $response;
	}
	//Checks to see if student is loading a list of available graded exams
	else if( isset($_POST['getStudentExams']) ){
		$response = getGrades($_POST['getStudentExams']);
		echo $response;
	}
	//Checks to see if student is viewing a graded exam
	else if( isset($_POST['student']) ){
		$response = getScores($_POST['student'],$_POST['uniqueExam']);
		echo $response;
	}
	exit();
?>
