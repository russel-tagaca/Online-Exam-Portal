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
	
	//Get login information from html form
 if (isset($_POST["qBank"])){
    getAllQuestions();
	}
 
 if (isset($_POST["diff"])){
		$diff = $_POST["diff"];
    $type = $_POST["type"];
    
    getQuestions( $diff, $type);
	}

	if (isset($_POST["exam"])){
     $pointQArray = $_POST["exam"];
     if( $conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
     }

     $sql   =  "select * from  examList"; 
  
     ($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
     while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
        $lastId = $r["examId"];
     };
     if (empty($lastId)){
       $lastId = 1;
     } else{
       $lastId += 1;
     }
     createExam($pointQArray,$lastId);
  }
   
	function getQuestions ($diff, $type) {
		$output = "";
		global $conn;
		$i = 1;
	 	$sql = "select * from QBank WHERE difficulty = '$diff' AND type = '$type' ";
		$oldquestion = "";
		$allQsArray = array();
		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
		    $question = $r["question"];
		    if ($question == $oldquestion) {
		    	continue;
		    }
		 	else {
		 		array_push($allQsArray, $question);
		    	$oldquestion = $question;
		    }
		}

		$size = count($allQsArray);

		for ($i = 0; $i < $size; $i++) {
		   	if ($i == ($size-1)){
		   		$output .= "$allQsArray[$i]";
		   	}
		   	else{
		   		$output .= "$allQsArray[$i].[^&*]";
		   	}

		}

		if ($size > 0){
			echo $output;
		}
		else {
			echo "Unavailable";
		}
	}
 
  function getAllQuestions () {
		$output = "";
		global $conn;
		$allQsArray = array();

	 	$sql = "select * from QBank";
		$oldquestion = "";
		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
		$num = mysqli_num_rows($t);

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
		    $question = $r["question"];
		    if ($question == $oldquestion) {
		    	continue;
		    }
		 	else {
		 		array_push($allQsArray, $question);
		    	$oldquestion = $question;
		    }
		}

		$size = count($allQsArray);

		for ($i = 0; $i < $size; $i++) {
		   	if ($i == ($size-1)){
		   		$output .= "$allQsArray[$i]";
		   	}
		   	else{
		   		$output .= "$allQsArray[$i].[^&*]";
		   	}

		}
	  	echo "$output" ;
	}

	function createExam ($pointQArray, $lastId) {
		$question = "";
		global $conn;
    for ($i = 0; $i <= count($pointQArray) - 1; $i+=2) {
      
      $questionPt = $pointQArray[$i];
      $question = $pointQArray[$i+1];
      $sql = "INSERT INTO examList VALUES ( '', '$question', '$questionPt', '$lastId')";
      ($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
      
      $x += 1;
		}
    echo "Exam created with $x questions. Exam Id: $lastId";
	}	  
	
	$conn->close();
?>
