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
  if (isset($_POST["questions"])){
       $newQArray = $_POST["questions"];
#      encoding array for printing out..
	     $question = $newQArray[0];
       $keywords = $newQArray[1];    
       array_splice($newQArray, 0,2);
       $size =  count($newQArray);
       $type = $newQArray[$size - 1]; 
       $diff = $newQArray[$size - 2];
       array_splice($newQArray, $size - 2, 2);
       $newQArray = array_filter($newQArray); 
       depositQ( $question, $keywords, $newQArray, $diff, $type);

  }
  
	function depositQ ( $question, $keywords, $testCaseArray, $diff, $type) {
		$output = "";
		global $conn;
    $x = 0;

    for ($i = 0; $i <= count($testCaseArray); $i+=2) {
      
      $testCase = $testCaseArray[$i];
      $answer = $testCaseArray[$i+1];
      if ($testCase == "" or $answer == ""){
      		continue;
      }
      
      $sql = "INSERT INTO QBank VALUES ( '', '$question', '$keywords', '$testCase', '$answer', '$diff', '$type' )";
      ($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
      
      $x += 1;
		}
	 echo "New question w/ $x test cases created.";
	}
	
	$conn->close();
?>
