<?php
	session_start();
	$bgsNumber = $_SESSION['bgsNumber'];
	echo $_SESSION['bgsNumber'];
	echo "<br/>";

$allowedExts = array("gif", "jpeg", "jpg", "png","txt","html","js","css");
$temp = explode(".", $_FILES["bgfile"]["name"]);
$extension = end($temp);


  if ($_FILES["bgfile"]["error"] > 0) {
    echo "Return Code: " . $_FILES["bgfile"]["error"] . "<br>";
  } else {
    echo "Upload: " . $_FILES["bgfile"]["name"] . "<br>";
    echo "Type: " . $_FILES["bgfile"]["type"] . "<br>";
    echo "Size: " . ($_FILES["bgfile"]["size"] / 1024) . " kB<br>";
    echo "Temp bgfile: " . $_FILES["bgfile"]["tmp_name"] . "<br>";
    if (file_exists("image/bgs/" . $_FILES["bgfile"]["name"])) {
      echo $_FILES["bgfile"]["name"] . " already exists. ";
    } else {
      move_uploaded_file($_FILES["bgfile"]["tmp_name"],
      "image/bgs/" . $_FILES["bgfile"]["name"]);
	  rename("image/bgs/" . $_FILES["bgfile"]["name"],"image/bgs/bg".$bgsNumber.".jpg");
      echo "Stored in: " . "image/bgs/" . $_FILES["bgfile"]["name"];
    }
}
header("Location:pics.php");
die();
?>