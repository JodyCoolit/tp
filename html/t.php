<?php
	$allowext = array("jpg");
	$pics = scanDirectories("image/pics",$allowext);
	
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
	echo count($pics);
?>