<?php 

	//Setup mysql database variables
	$hostname = ""     ;
	$username = "" ;
	$project  = "" ;
	$password = "" ;

	//Create connection
	$conn = new mysqli($hostname, $username, $password, $project);

	//Check connection
	if( $conn->connect_error){
		die("Connection failed: " . $conn->connect_error);
	}
	
	if (isset($_POST["exam"])){
		getExamIds();
	}

	if (isset($_POST["examID"])){
		$chosenID = $_POST["examID"];
 	  	getQuestions($chosenID);
	}

	if (isset($_POST["getExams"])){
		getPendingExams();
	}

	if (isset($_POST["studentAnswers"])){
		$ansExamId = $_POST["id"];
		$ansExam = $_POST["studentAnswers"];
    $currentUser = $_POST["currentUser"];
 	  $specExamId = saveSubmittedExam($currentUser, $ansExamId);
		saveExamAnswers($ansExam, $specExamId, $ansExamId, $currentUser);
	}

	if (isset($_POST["revisedExam"])){
		$finalDetails = $_POST["revisedExam"];
		$size =  count($finalDetails);
    $studentName = $finalDetails[$size - 3];
		$examId = $finalDetails[$size - 2]; 
		$submittedExam_id = $finalDetails[$size - 1]; 
		$submittedExam_id = preg_replace('/[^0-9]/', '', $submittedExam_id); 
    array_splice($finalDetails, sizeof($finalDetails) - 3, 3);
		saveFinalExam($finalDetails, $submittedExam_id, $studentName, $examId);
		approveRevisedExam($studentName, $submittedExam_id);
	}  
	
	if (isset($_POST["answer"])){
		$question = $_POST["answer"];
 	  $examId = $_POST["EXAMID"];
		getExamDetails($question, $examId);
	}
 
	if (isset($_POST["gradedReport"])){
	    $studentName = $_POST["student"];
		$answer = $_POST["gradedReport"];
	    $questionGrade = $_POST["questionGrade"];
	    $comments = $_POST["comments"];
	    $gradingOutput = $_POST["studentOutput"];
	    $examId = $_POST["EXAMID"];
      	$special_ID = $_POST["special_ID"];
		saveExamGrades($studentName, $answer, $questionGrade, $comments, $gradingOutput, $examId, $special_ID);
	} 

  	if (isset($_POST["studentName"])){
    	$studentName = $_POST["studentName"];
		  getRevisedExamIds ($studentName);
	} 


 
  if (isset($_POST["choosenExam"])){
	  $pendingExam_id = $_POST["choosenExam"];
    $pendingExam_id = preg_replace('/[^0-9]/', '', $pendingExam_id);   
	  getPendingExamDetails($pendingExam_id);
	}

  if (isset($_POST["getStudentScores"])){
	  $studentName = $_POST["getStudentScores"];
    $uniqueExamID = $_POST["uniqueExam"]; 
	  getRevisedExamDetails($studentName, $uniqueExamID);
	}
 
#	Get exam IDs for choosing as student

	function getExamIds () {
		$output = "";
		global $conn;
		$i = 0;
	 	$sql = "select * from examList";
	
		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);
		$ids = array();

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
			if ( $i == 0){
				$id = $r["examId"];
		    	$lastid = $id;
		    	array_push($ids,$id);
		    	$i += 1;	
		    	continue;
			}
			$id = $r["examId"];
			If ($lastid != $id){
		    	array_push($ids,$id);
		    	$lastid = $id;
			}
		}
		for ($j = 0; $j < count($ids); $j++) {
			if ( $j == (count($ids)-1)){
				$output .= "$ids[$j]";
			}
			else{
		   		$output .= "$ids[$j].[^&*]";
			}
		}
		echo "$output" ;
	}

#	Get exams IDs due for revision. (Sends exam Ids, student name, and exam identifier)

	function getPendingExams () {
		$output = "";
		global $conn;
		$i = 0;
	 	$sql = "select * from submittedExams where revised = '0' ";
	
		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);
		$pendingExams = array();

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
				$id = $r["examId"];
        $exam_identifier = $r["exam_index"];
        $student_name = $r["student_name"];
                
        array_push($pendingExams,$exam_identifier);        
        array_push($pendingExams,$student_name);    
   	    array_push($pendingExams,$id);                  
			}
    echo json_encode($pendingExams);
  }

#	Get exam IDs of revised exams available for viewing for student

	function getRevisedExamIds ($studentName) {
		$output = "";
		global $conn;
		$i = 0;
	 	$sql = "select * from submittedExams where student_name = '$studentName' and revised = '1' ";
	
		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);
		$gradedExams = array();

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
			$id = $r["examId"];
      $examSpecId = $r["exam_index"];
      array_push($gradedExams, $examSpecId, $id);                  
		}
 	  echo json_encode($gradedExams);
  }

#	Get questions after student has chosen an exam ID. Sends question and instructor assigned points.

	function getQuestions ($chosenID) {
		$output = "";
		global $conn;
		$i = 1;
	 	$sql = "select * from examList where examId = '$chosenID' ";
	
		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
		    $question = $r["question"];
        $point = $r["points"];
		    if ( $i == $num ) {
		    	$output .= "$question.[^&*]$point";
		 	}
		 	else {
		    	$output .= "$question.[^&*]$point.[^&*]";
		    }
		    $i += 1;
		}
	  	echo "$output" ;
	}

#	Get a student exam's details due for revising after instructor has chosen an exam to revise.
  function getPendingExamDetails($pendingExam_id){
    $examDetails = array();
    global $conn;
	$i = 1;
    $sql = "select * from submittedExams where exam_index = '$pendingExam_id'";
    $examId = "";
    $studentName = "";
    $question = "";
    ($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
		    $examId = $r["examId"];
        	$studentName = $r["student_name"];
		}

	  array_push($examDetails, $studentName, $examId);
    $sql = "select * from examAnswers where user = '$studentName' and exam_index = '$pendingExam_id' ";
 	  ($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
	  $num = mysqli_num_rows($t);

    while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
      $expectedAnswers = array();
      $studentAnswers = "";

      $question = $r["question"];
      $answer = $r["answer"];
      array_push($examDetails, $question, $answer);

      $sql2 = "select * from examGrades where user = '$studentName' and answer = '$answer' and exam_index = '$pendingExam_id' ";
      ($t2 = mysqli_query( $conn,  $sql2 ) ) or die( mysqli_error($conn) );
    
   	while ( $r2 = mysqli_fetch_array ($t2, MYSQLI_ASSOC)){
  		$grade = $r2["grade"];
  		$studentAnswers = $r2["caseOutputs"];
        $comments = $r2["comments"];
  		array_push($examDetails, $grade, $comments);
    }
    $studentAnsArray = explode(",", $studentAnswers);

    $sql3 = "select * from QBank where question = '$question'";
    ($t3 = mysqli_query( $conn,  $sql3 ) ) or die( mysqli_error($conn) );

    #error at 12
	while ( $r3 = mysqli_fetch_array ($t3, MYSQLI_ASSOC)){
	  		$expected = $r3["answer"];
	  		array_push($expectedAnswers, $expected);
	}
    array_push($examDetails, $studentAnsArray, $expectedAnswers);
//    echo print_r($studentAnsArray);
	}
#    array_push($examDetails, $pendingExam_id);
    echo json_encode($examDetails);
  }



#    Get a student exam's details due for revising after instructor has chosen an exam to revise.
  function getRevisedExamDetails($studentName, $uniqueExamId){
	$examDetails = array();
	global $conn;
      $i = 1;
	$sql = "select * from submittedExams where exam_index = '$uniqueExamId' and student_name = '$studentName'";
	$examId = "";
	$question = "";
    
	($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
   	 $num = mysqli_num_rows($t);

   	 while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
   	 	$examId = $r["examId"];
   	 }

	$sql = "select * from examAnswers where exam_index = '$uniqueExamId' ";
	   ($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
      $num = mysqli_num_rows($t);

	while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
	$expectedAnswers = array();
    $studentAnswers = "";

    $question = $r["question"];
  	$answer = $r["answer"];
  	array_push($examDetails, $question, $answer);

  	$sql2 = "select * from finalGrades where exam_index = '$uniqueExamId' and answer = '$answer'  ";
  	($t2 = mysqli_query( $conn,  $sql2 ) ) or die( mysqli_error($conn) );
    
  	   while ( $r2 = mysqli_fetch_array ($t2, MYSQLI_ASSOC)){
    	$grade = $r2["finalGrade"];
    	$comments = $r2["comments"];
     	array_push($examDetails, $grade, $comments);
  	}

  	$sqlAns = "select * from examGrades where exam_index = '$uniqueExamId' and answer = '$answer'  ";
  	($tAns = mysqli_query( $conn,  $sqlAns ) ) or die( mysqli_error($conn) );
    
  	   while ( $rAns = mysqli_fetch_array ($tAns, MYSQLI_ASSOC)){
    	$studentAnswers = $rAns["caseOutputs"];
    }
  	$studentAnsArray = explode(",", $studentAnswers);

	$sql3 = "select * from QBank where question = '$question'";
	($t3 = mysqli_query( $conn,  $sql3 ) ) or die( mysqli_error($conn) );

    while ( $r3 = mysqli_fetch_array ($t3, MYSQLI_ASSOC)){
     		 $expected = $r3["answer"];
     		 array_push($expectedAnswers, $expected);
    }
	array_push($examDetails, $studentAnsArray, $expectedAnswers);
    
	}
	echo json_encode($examDetails);
  }

  
#	Takes and saves student's answers to an exam.
	function saveExamAnswers ( $ansExam, $specExamId, $ansExamId, $currentUser ) {
		global $conn;
 	  	$questionArray = array();
   		$answerArray = $ansExam;

    $sql = "select * from examList where examId = '$ansExamId' ";
	
		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
		    $question = $r["question"];
		    array_push($questionArray, $question);
		}

	    for ($i = 0; $i < count($answerArray); $i++) {
	       $sql = "INSERT INTO examAnswers VALUES ( '', '$specExamId', '$currentUser', '$questionArray[$i]', '$answerArray[$i]', '$ansExamId')";
	       ($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
	    }
	    echo "Answers saved successfully. $specExamId";
	}	 

#	Saves student's name and exam Id of an exam they finished, now due for pending.
	function saveSubmittedExam ( $currentUser, $ansExamId ) {
		global $conn;
    $specialIds = array();
    
    $sql2 = "select * from submittedExams";
	
		($t2 = mysqli_query( $conn,  $sql2 ) ) or die( mysqli_error($conn) );
    while ( $r2 = mysqli_fetch_array ($t2, MYSQLI_ASSOC)){
		    $spec_id = $r2["exam_index"];
		    array_push($specialIds, $spec_id);
		}
    
    $rand_id = rand();
    while (in_array($rand_id, $specialIds)){
      $rand_id = rand();
    }

   
    $sql = "INSERT INTO submittedExams VALUES ( $rand_id, '$currentUser', '$ansExamId', '0')";
	
		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
   
    return $rand_id;
	}	

#	Changes a submitted exam as "Revised" by setting boolean to 1 in database.
	function approveRevisedExam ( $currentUser, $submittedExam_id ) {
		global $conn;
      
    $sql = "UPDATE submittedExams SET revised = '1' WHERE exam_index = $submittedExam_id ";
	
	($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );

	}	
 
#	Fetches details of an exam for grading system.
	function getExamDetails ($question, $examId) {
		global $conn;
		$detailsArray = array();
    $keyword = "";
		$sql = "select * from examList where question = '$question' and examId = '$examId' ";

		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);
		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
			    $points = $r["points"];
			    array_push($detailsArray, $points);
		}

		$sql = "select * from QBank where question = '$question'";

		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);
    $i = 0;
		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
        if ($i == 0){
          $keywords = $r["keywords"];
          array_push($detailsArray, $keywords);
          $i += 1; 
        }
				$testCase = $r["test_case"];
				$answer = $r["answer"];
				array_push($detailsArray, $testCase);
				array_push($detailsArray, $answer);
				}
	echo json_encode($detailsArray);
	}
 
#	Saves grades, comments and details of a recently submitted exam created by grading system.
  function saveExamGrades($studentName, $answer, $questionGrade, $comments, $gradingOutput, $examId, $special_ID) {
  	global $conn;
  	$caseOutput = "";
  	for ($i = 0; $i < count($gradingOutput); $i+= 1){
  		if ($i != count($gradingOutput) - 1){
  	  		$caseOutput .= (string)$gradingOutput[$i];
  	  		$caseOutput .= ",";
  	  	}
  	  	else{
  	  		$strOutput = (string)$gradingOutput[$i];
  	  		$caseOutput .= $strOutput;
  	  	}
  	}
 	$sql = "INSERT INTO examGrades VALUES ( '', '$special_ID', '$studentName', '$answer', '$questionGrade', '$caseOutput', '$comments', '$examId', NOW())";
  
  	($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
  
    echo "Exam grade saved successfully.";
  }	

#	Saves final (REVISED) grades, comments and details of an exam revised by instructor.

	function saveFinalExam($finalDetails, $submittedExam_id, $studentName, $examId) {
		global $conn;
  		$x = 0;
  		for ($i = 0; $i < count($finalDetails); $i+= 3) {
  			$answer = $finalDetails[$i];
  			$grade = $finalDetails[$i + 1];
  			$comments = $finalDetails[$i + 2];
	 	  	$sql = "INSERT INTO finalGrades VALUES ( '', '$submittedExam_id', '$studentName', '$answer', '$grade', '$comments', '$examId')";
	  
	  		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );

	  		$x += 1;
	    }
	    echo "$x final grades saved successfully.";
	}
  
	function sendExamGrades($studentName) {
		$output = "";
		global $conn;
		$i = 1;
	 	$sql = "select * from examGrades where user = '$studentName' ";
	
		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
		    $grade = $r["grade"];
        	$examId = $r["exam_id"];
        
	        if ( $i == $num ) {
			    	$aString = "ExamId: $examId, Grade: $grade";
			 	  }
	        else {
	          $aString = "ExamId: $examId, Grade: $grade.[^&*]";
	        }
	        $output .= "$aString";
	        $i += 1;
		}
    
    echo "$output";
	}

	$conn->close();
?>
