<?php
	function scanDirectories($rootDir, $allowext, $allData=array()) {
    $dirContent = scandir($rootDir);
    foreach($dirContent as $key => $content) {
        $path = $rootDir.'/'.$content;
        $ext = substr($content, strrpos($content, '.') + 1);
        
        if(in_array($ext, $allowext)) {
            if(is_file($path) && is_readable($path)) {
                $allData[] = $path;
            }elseif(is_dir($path) && is_readable($path)) {
                // recursive callback to open new directory
                $allData = scanDirectories($path, $allData);
            }
        }
    }
		return $allData;
	}
	
	$pics = $_POST['pics'];
	echo var_dump($pics);
	$del_num = 0;
	while($del_num<count($pics)){
		unlink('image/pics/'.$pics[$del_num].'.jpg');
		$del_num++;
	}

	$allowext = array("jpg");
	$picss = scanDirectories("image/pics",$allowext);
	echo var_dump($picss);
	$pics_num = 0;
	while($pics_num<count($picss)){
		rename($picss[$pics_num],'image/pics/'.$pics_num.'.jpg');
		$pics_num++;
	}
header("Location:pics.php");
die();
?>
