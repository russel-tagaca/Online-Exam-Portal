<?php 

	//Setup mysql database variables
	$hostname = ""     ;
	$username = "" ;
	$project  = "" ;
	$password = "" ;

	//Get login information from html form
	$remoteUser = $_POST["uname"];
	$remotePassword = $_POST["psw"];


	//Create connection
	$conn = new mysqli($hostname, $username, $password, $project);

	//Check connection
	if( $conn->connect_error){
		die("Connection failed: " . $conn->connect_error);
	}

	//Insert users login credentials to database
//	$sql = "INSERT INTO Login (username, password) VALUES ('".$remoteUser."','".$remotePassword."')";
  	$sql   =  "select * from  Login where username = '$remoteUser' and password = '$remotePassword' " ; 
  
  	($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );
  
//  	if($conn->query($sql) == TRUE){
//		echo "Query Successful!";
//	}
//	else{
//		echo "Error: " . $sql . "<br>" . $conn->error;
//	}

    $num = mysqli_num_rows($t);
  	if ( $num == 0 ) {
  		echo "Database: Bad credentials." ;
  	}
  	else{
  		$output = "";
  		$sql   =  "select * from  Login where username = '$remoteUser' "; 
  		($t = mysqli_query( $conn,  $sql ) ) or die( mysqli_error($conn) );

		while ( $r = mysqli_fetch_array ($t, MYSQLI_ASSOC)){
		    $occupation = $r["occupation"];
		    
		    $output .= "$occupation";
		  };
  		echo "$output" ;
  	}

	
	$conn->close();
?>
