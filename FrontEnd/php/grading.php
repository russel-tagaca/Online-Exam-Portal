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

	//Submits the students exam to be saved to the database
	function submitExam($studentAnswers){
		//Get the name of the student and their index in the array
		$currentUser = $studentAnswers[sizeof($studentAnswers)-2];
		$currentUserIndex = sizeof($studentAnswers)-2;
		
		//Get the exam ID from the array and its index
		$ID = $studentAnswers[sizeof($studentAnswers)-1];
		$examIndex = sizeof($studentAnswers)-1;	
		
		//Remove the examID and students name completly from the array
		unset($studentAnswers[$examIndex]);
		unset($studentAnswers[$currentUserIndex]);

		//Strip only the students answers from the array ($studentAnswers array contains questions and answers)
		$rawAnswers = [];
		$rawAnsIndex = 0;
		for($i = 0; $i < sizeof($studentAnswers); $i+=2){
			$rawAnswers[$rawAnsIndex] = $studentAnswers[$i];
			$rawAnsIndex++;
		}
		
		$data = array(
			"id"			 => $ID,
			"studentAnswers" => $rawAnswers,
			"currentUser"	 => $currentUser
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);

		$response = json_encode(curl_exec($ch));
		curl_close($ch);
		
		$special_ID = substr($response, 28);
		$response = preg_replace("/[0-9]/", "", $response);
		
		sleep(3);
		gradeExam($studentAnswers, $ID, $currentUser, $special_ID);
		return $response;
	}

	//Is called from submitExam() and grades the students exam as soon as they are finished taking it
	function gradeExam($answers, $id, $student, $special_ID){
		$totalPoints = 0;
		$questionValue = 0;
		$correctAnswers = 0;
		$finalGrade = 0;
		$totalTestCaseValue = .8;
		$totalKeyWordValue = .2;
		
		for($i = 0; $i < sizeof($answers); $i+=2 ){
			$studentOutput = []; //This array will hold the students output to each test case
			$totalDeductions = 0;
			$keyWordReport = "";
			$testCaseReport = "";
			$comments = "";
			
			$data = array(
				"answer" => $answers[$i+1],
				"EXAMID" => $id
			);
			$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
			$question = json_decode(curl_exec($ch));
			curl_close($ch);
			
			$questionValue = $question[0];
			$totalPoints += $questionValue;
			$keywords = $question[1];
			
			//Calculate the points to deduct for any missing keywords
			$keyWordArray = explode(",", $keywords);
			$eachKeyWordValue = ($totalKeyWordValue/sizeof($keyWordArray))*$questionValue;
			$functionName = $keyWordArray[0];
			for($j = 0; $j < sizeof($keyWordArray); $j++){
				if(strpos($answers[$i], $keyWordArray[$j]) === false){
					$totalDeductions += $eachKeyWordValue;
					$keyWordReport .= "Missing keyword(".$keyWordArray[$j].") deduct ".round($eachKeyWordValue, 2)." points\n";
				}
				else{
					$keyWordReport .= "Contains keyword(".$keyWordArray[$j].") received ".round($eachKeyWordValue, 2)." points\n";
				}
			}
			//Create a python file for each test case and see if the students function runs
			for($k = 2; $k < sizeof($question); $k+=2){
				$eachTestCaseValue = ($totalTestCaseValue/((sizeof($question)-2)/2))*$questionValue;
				
				$withOutFunction = substr($answers[$i], strpos($answers[$i], "("));
				$withFunction = "def " . $functionName;
				$answerCopy = $withFunction . $withOutFunction;
				//echo $answerCopy;
				
				$functionCall = $question[$k];
				$expectedAnswer = $question[$k+1];
				$python = $answerCopy . "\n" . "print(" . $functionCall . ")";
				$fileName = "/afs/cad/u/e/f/efc9/public_html/grading/test.py";
				$fh = fopen($fileName, 'w');

				if ($fh === false) {
					ini_set('display_errors', 1); 
					error_reporting(E_ALL);
					$testCaseReport .= "Failed to grade this question. Could not open python file";
					$totalDeductions += 0;
					continue;
				}
				else if( fwrite($fh, $python) ){
					fclose($fh);
					$run = exec("/afs/cad/linux/anaconda3.6/anaconda/bin/python3 {$fileName}", $output, $status);
					if( $output[0] !== $expectedAnswer ){
						$totalDeductions += $eachTestCaseValue;
						$testCaseReport .= "Failed Test case ".$functionCall." deduct ".round($eachTestCaseValue, 2)." points\n";
					}else{
						$testCaseReport .= "Passed Test case ".$functionCall." received ".round($eachTestCaseValue, 2)." points\n";
					}
					if( sizeof($output) < 1 ){
						array_push($studentOutput,"Solution could not run");
					}else{
						array_push($studentOutput, $output[0]);
					}
					unset($output);
					//echo var_dump($output);
				}
			}
			$finalQuestionGrade = round($questionValue - $totalDeductions);
			$correctAnswers += $finalQuestionGrade;
			$comments .= $keyWordReport . $testCaseReport . $finalQuestionGrade ."/". $questionValue ." points for this question";
			
			$data = array(
				"gradedReport" => $answers[$i],
				"comments" => $comments,
				"questionGrade" => $finalQuestionGrade,
				"student" => $student,
				"studentOutput" => $studentOutput, //Send an array containing the students output for each test case to the database
				"EXAMID" => $id,
				"special_ID" => $special_ID
			);
			$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
			$dbResponse = json_encode(curl_exec($ch));
			curl_close($ch);
		}
	}

	//Checks to see if the student is submitting an exam
	if( isset($_POST["studentAnswers"]) ){
		$retrieveExam = submitExam( $_POST["studentAnswers"] );
		echo $retrieveExam;
	}

	exit();
?>