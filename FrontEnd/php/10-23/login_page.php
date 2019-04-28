<?php
	header('Content-type:application/json;charset=utf-8');

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
	
	//Handl question submisso frominstructo
	function submitQustions($question1,$function1,$answer1){
		$data = array(
			"question1" => $question1,
			"function1" => $function1,
			"answer1" => $answer1
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

		$response = json_encode(curl_exec($ch));
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
	
	//Submits the students exam to be saved to the database
	function submitExam($studentAnswers){
		$currentUser = $studentAnswers[sizeof($studentAnswers)-2]; //Get the name of the student
		$currentUserIndex = sizeof($studentAnswers)-2;		//Get the index of the students name in the array
		
		$ID = $studentAnswers[sizeof($studentAnswers)-1];	//Get the exam ID from the array
		$examIndex = sizeof($studentAnswers)-1;		//Get the index in the array for the exam ID
		
		unset($studentAnswers[$examIndex]);		//Remove the examID completly from the array
		unset($studentAnswers[$currentUserIndex]); //Remove the students name from the array

		$data = array(
			"id"			 => $ID,
			"studentAnswers" => $studentAnswers,
			"currentUser"	 => $currentUser
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);

		$response = json_encode(curl_exec($ch));
		curl_close($ch);
		sleep(3);
		$gradeResponse = gradeExam($studentAnswers, $ID, $currentUser);
		//$allResponses = "submitExam() response = " . $response . "\n" . "gradeExam() response = " . $gradeResponse;
		return $response;
	}

	//Is called from submitExam() and grades the students exam as soon as they are finished taking it
	function gradeExam($answers, $id, $student){
		$totalPoints = sizeof($answers);
		$correctAnswers = 0;

		$data = array(
			"getExamDetails" => $id,
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
		$examAnswers = json_decode(curl_exec($ch));	//Get and exam answers and function calls should be a 2d Array
		
		$x = 0;
		for ($col = 0; $col < sizeof($examAnswers[0]); $col++) {
	  		$row = 0;
    		$expectedAnswer = $examAnswers[$row][$col];
	  		$functionCall = $examAnswers[$row+1][$col];
			
			$python = $answers[$col] . "\n" . "print(" . $functionCall . ")";
			$file_name = "/afs/cad/u/e/f/efc9/public_html/grading/test.py";

			$fh = fopen($file_name, 'w');

			if ($fh === false) {
			  ini_set('display_errors', 1); 
			  error_reporting(E_ALL);
			  echo "Cant open file";
			}
			if(fwrite($fh, $python)) {
				fclose($fh);
				$run = exec("/afs/cad/linux/anaconda3.6/anaconda/bin/python3 {$file_name}", $output, $status);
				if( $output[$x] === $expectedAnswer ){
					$correctAnswers += 1;
					$x += 1;
				}
			}
  		}
		curl_close($ch);
		$finalGrade = ($correctAnswers / $totalPoints)*100;
		$tally = "You scored " . $finalGrade . "%";
		
		$data = array(
			"finalGrade"    => $finalGrade,
			"submitGradeId" => $id,
			"student"		=> $student
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
		$submitGrade = curl_exec($ch);
		curl_close($ch);
		return $submitGrade;
	}

	//Handles returning all of the students grades from graded exams
	function getGrades($student){
		$data = array(
			"studentName" => $student
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);

		$response = explode(".[^&*]", (curl_exec($ch)));
		$gradeDB = json_encode($response);
		curl_close($ch);
		return $gradeDB;
	}

	//Checks to see if we are logging in
	if( isset($_POST["uname"]) && isset($_POST["psw"]) ){
		$dbResponse = loginDB( $_POST["uname"], $_POST["psw"] );
		//$njitResponse = loginNJIT( $_POST["uname"], $_POST["psw"] );
		echo $dbResponse;
	}
	//Checks to see if we are submitting questions to the question bank
	else if( isset($_POST["question1"]) ){
		$sendQuestions = submitQustions($_POST["question1"], $_POST["function1"], $_POST["answer1"]);
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
	//Checks to see if the student is submitting an exam
	else if( isset($_POST["studentAnswers"]) ){
		$retrieveExam = submitExam( $_POST["studentAnswers"] );
		echo $retrieveExam;
	}
	//Checks to see if we are going to make an exam for a student to take
	else if( isset($_POST['formCheck']) ){
		$qs = $_POST['formCheck'];
		$examRespone = makeExam($qs);
		echo $examRespone;
	}
	//Checks to see if the database should get the grades for the posted student
	else if( isset($_POST['currentUser']) ){
		$gradeRespone = getGrades( $_POST['currentUser'] );
		echo $gradeRespone;
	}
	//Checks to see if the question bank needs to be filtered
	else if( isset($_POST['diff']) && isset($_POST['type']) ){
		echo $_POST['diff'] . $_POST['type'];
	}
	
	exit();
?>
