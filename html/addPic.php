<?php
	session_start();
	$picsNumber = $_SESSION['picsNumber'];
	echo $_SESSION['picsNumber'];
	echo "<br/>";
$allowedExts = array("gif", "jpeg", "jpg", "png","txt","html","js","css");
$temp = explode(".", $_FILES["picfile"]["name"]);
$extension = end($temp);


  if ($_FILES["picfile"]["error"] > 0) {
    echo "Return Code: " . $_FILES["picfile"]["error"] . "<br>";
  } else {
    echo "Upload: " . $_FILES["picfile"]["name"] . "<br>";
    echo "Type: " . $_FILES["picfile"]["type"] . "<br>";
    echo "Size: " . ($_FILES["picfile"]["size"] / 1024) . " kB<br>";
    echo "Temp picfile: " . $_FILES["picfile"]["tmp_name"] . "<br>";
    if (file_exists("image/pics/" . $_FILES["picfile"]["name"])) {
      echo $_FILES["picfile"]["name"] . " already exists. ";
    } else {
      move_uploaded_file($_FILES["picfile"]["tmp_name"],
      "image/pics/" . $_FILES["picfile"]["name"]);
	  rename("image/pics/" . $_FILES["picfile"]["name"],"image/pics/".$picsNumber.".jpg");
      echo "Stored in: " . "image/pics/" . $_FILES["picfile"]["name"];
    }
  }
header("Location:pics.php");
die();
?>