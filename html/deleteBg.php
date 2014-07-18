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
	
	$bgs = $_POST['bgs'];
	echo var_dump($bgs);
	$del_num = 0;
	while($del_num<count($bgs)){
		unlink('image/bgs/bg'.$bgs[$del_num].'.jpg');
		$del_num++;
	}

	$allowext = array("jpg");
	$picss = scanDirectories("image/bgs",$allowext);
	echo var_dump($picss);
	$bgs_num = 0;
	while($bgs_num<count($picss)){
		rename($picss[$bgs_num],'image/bgs/bg'.$bgs_num.'.jpg');
		$bgs_num++;
	}
header("Location:pics.php");
die();
?>
