//This function takes the given login credentials and sends them to the database to see if the user exist
function login(UN,PW){
	sessionStorage.setItem("currentUser", UN);
	var currentUser = sessionStorage.getItem("currentUser");
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if(this.readyState == 4 && this.status == 200){
			 var response = this.responseText;
			 if( response.includes("instructor")){
					window.location.href = "https://web.njit.edu/~efc9/cs490/instructor/instructorHome.html";
			 }
			 else if( response.includes("student") ){
					window.location.href = "https://web.njit.edu/~efc9/cs490/student/studentHome.html";
			 }
			 else{
					document.getElementById("Error").innerHTML = this.responseText;
			}
		}
	};
	xhttp.open("POST", "https://web.njit.edu/~efc9/cs490/php/login_page.php",true);
	xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xhttp.send(`uname=${UN}&psw=${PW}&currentUser=${currentUser}`);
}