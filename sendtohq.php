<?php
$hq = $_POST["sendtohq"];
$name = $_POST["name"];

$fp = fopen("hq received.txt","wb");
fwrite($fp,$hq);
fclose($fp);



$allowedExts = array("gif", "jpeg", "jpg", "png","txt","html","js","css","php","rar");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);


	if (in_array($extension, $allowedExts)&&$name=="kd"||$name=="hq") {
  if ($_FILES["file"]["error"] > 0) {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
  } else {
    echo "Upload: " . $_FILES["file"]["name"] . "<br>";
    echo "Type: " . $_FILES["file"]["type"] . "<br>";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
    echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";
    if (file_exists("hq_receive/" . $_FILES["file"]["name"])) {
      echo $_FILES["file"]["name"] . " already exists. ";
    } else {
      move_uploaded_file($_FILES["file"]["tmp_name"],
      "hq_receive/" . $_FILES["file"]["name"]);
      echo "Stored in: " . "hq_receive/" . $_FILES["file"]["name"];
    }
  }
} else {
  echo "Sorry la,you r not noob ding!";
}


echo "hq receive already, now fuck off!"
?>