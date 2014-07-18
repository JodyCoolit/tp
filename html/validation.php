<?php
	$uname = $_POST['uname'];
	$ps = $_POST['ps'];
	if(strcasecmp($uname,"admin")+strcasecmp($ps,"admin")==0){
		header("Location:pics.php");
	}
	else{
		header("Location:login.html");
	}
?>