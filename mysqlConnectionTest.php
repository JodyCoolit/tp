<?php 

$db = mysqli_connect("www.nmmapp.com", "yeospace_1B14037", "HuangQian2014","yeospace_1B14037"); 

if (!$db){ 
    die('Could not connect' . mysql_error()); 
}else{ 
    echo 'Connected successfully'; 
} 
?>