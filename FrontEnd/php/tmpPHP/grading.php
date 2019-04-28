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
		$currentUser = $studentAnswers[sizeof($studentAnswers)-2]; //Get the name of the student
		$currentUserIndex = sizeof($studentAnswers)-2;		//Get the index of the students name in the array
		
		$ID = $studentAnswers[sizeof($studentAnswers)-1];	//Get the exam ID from the array
		$examIndex = sizeof($studentAnswers)-1;		//Get the index in the array for the exam ID
		
		unset($studentAnswers[$examIndex]);		//Remove the examID completly from the array
		unset($studentAnswers[$currentUserIndex]); //Remove the students name from the array

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
		sleep(3);
		gradeExam($studentAnswers, $ID, $currentUser);
		//$allResponses = "submitExam() response = " . $response . "\n" . "gradeExam() response = " . $gradeResponse;
		return $response;
	}

	//Is called from submitExam() and grades the students exam as soon as they are finished taking it
	function gradeExam($answers, $id, $student){
		$totalPoints = 0;
		$questionValue = 0;
		$correctAnswers = 0;
		$finalGrade = 0;
		
		for($i = 0; $i < sizeof($answers); $i+=2 ){
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
			
			$keyWordArray = explode(",", $keywords);
			for($j = 0; $j < sizeof($keyWordArray); $j++){
				if(strpos($answers[$i], $keyWordArray[$j]) === false){
					$totalDeductions += .05*$questionValue;
					$keyWordReport .= "\n\n# Missing (".$keyWordArray[$j].") deduct 5% of total points for this question\n";
				}
			}

			for($k = 2; $k < sizeof($question); $k+=2){
				$functionCall = $question[$k];
				$expectedAnswer = $question[$k+1];
				$python = $answers[$i] . "\n" . "print(" . $functionCall . ")";
				$fileName = "/afs/cad/u/e/f/efc9/public_html/grading/test.py";
				$fh = fopen($fileName, 'w');

				if ($fh === false) {
					ini_set('display_errors', 1); 
					error_reporting(E_ALL);
					echo "Cant open file";
				}
				if( fwrite($fh, $python) ){
					fclose($fh);
					$run = exec("/afs/cad/linux/anaconda3.6/anaconda/bin/python3 {$fileName}", $output, $status);
					if( $output[0] !== $expectedAnswer ){
						$totalDeductions += .2*$questionValue;
						$testCaseReport .= "\n# Failed Test case ".$functionCall." deduct 20% of total points\n";
					}
					unset($output);
				}
			}
			$finalQuestionGrade = ($questionValue - $totalDeductions);
			$correctAnswers += $finalQuestionGrade;
			$comments .= $keyWordReport . $testCaseReport . "# ". $finalQuestionGrade ."/". $questionValue ." points for this question";
			
			$data = array(
				"gradedReport" => $answers[$i],
				"comments" => $comments,
				"questionGrade" => $finalQuestionGrade,
				"student" => $student,
				"EXAMID" => $id
			);
			$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
			$dbResponse = json_encode(curl_exec($ch));
			curl_close($ch);
		}
		/*$finalGrade = ($correctAnswers / $totalPoints) * 100;
		$data = array(
			"finalGrade" => $finalGrade,
			"EXAMID" => $id,
			"student" => $student
		);
		$ch = createConnection("https://web.njit.edu/~ret5/CS490WebProj/testStudentBack.php", $data);
		$submitGrade = curl_exec($ch);
		curl_close($ch);*/
	}

	//Checks to see if the student is submitting an exam
	if( isset($_POST["studentAnswers"]) ){
		$retrieveExam = submitExam( $_POST["studentAnswers"] );
		echo $retrieveExam;
	}

	exit();
?>