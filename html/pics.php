<!DOCTYPE html>
<html>
<body>
<head>
<title>Pictures Management</title>
<style type="text/css">
	*{
		margin : 0px;
		padding : 0px;
	}
	body{
		width : 100%;
		height : 100%;
	}
	header{
		position : absolute;
		left : 0px;
		height :50px;
		width : 99%;
		border-style: solid;
		border-width: 5px;
		border-color: red;
	}
	main{
		position : absolute;
		top : 60px;
		width : 99%;
		border-style: solid;
		border-width: 5px;
		border-color: blue;
	}
	section{
		width : 50%;
		border-style: solid;
		border-width: 5px;
		border-color: green;
	}
	aside{
		position : absolute;
		left : 50.5%;
		top : 0px;
		width : 49%;
		height :98%;
		border-style: solid;
		border-width: 5px;
		border-color: yellow;
	}
</style>
<script>
	function chkBoxes (form) { 
		var state = document.getElementById("cb_all").checked;
		for (var i=0;i<form.elements.length;i++) {  
		var obj = form.elements[i]; 
		if ((obj.type == 'checkbox') && (obj.name!= 'cb_all2')) {  
			obj.checked = state;
		}
		}
		console.log(state);
		document.getElementById("cb_all").checked = state;
	}
	function chkBoxes2 (form) { 
		var state = document.getElementById("cb_all2").checked;
		for (var i=0;i<form.elements.length;i++) {  
		var obj = form.elements[i]; 
		if ((obj.type == 'checkbox') && (obj.name!= 'cb_all2')) {  
			obj.checked = state;
		}
		}
		console.log(state);
		document.getElementById("cb_all").checked = state;
	}
</script>
</head>
<header>
<h1><center>Welcome , Admin! &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp <a href="Canvas.php">Back to canvas</a></center></h1>
</header>
<main>
<section>
<form action="addPic.php" method="post" enctype="multipart/form-data">
	<input type="file" name="picfile" id="picfile" multiple="true">
	<input type="submit" value="Add picture"/>
</form>
<br/>
<form action="deletePic.php" method="post">
	<input type="checkbox" id="cb_all" onClick="chkBoxes(this.form)"/>&nbspSelect All
<?php
	session_start();
	//$temp = scandir("image/pics");
	$allowext = array("jpg");
	$pics = scanDirectories("image/pics",$allowext);
	$_SESSION['picsNumber'] = count($pics);
	
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
	
	$pics_num = 0;
	while($pics_num<count($pics)){
?>
	<br/>
	<input type="checkbox" name="pics[]"id = <?php echo "cb".$pics_num ?> value=<?php echo $pics_num?>>
	<img src=<?php echo $pics[$pics_num]?> widtd="50"height="50"/>
<?php
	$pics_num++;
	}
?>
&nbsp&nbsp&nbsp&nbsp&nbsp<input type="submit" value="delete"/>
</form>
</section>
<aside>
<form action="addBg.php" method="post" enctype="multipart/form-data">
	<input type="file" name="bgfile" id="bgfile" multiple="true">
	<input type="submit" value="Add background"/>
</form>
<br/>
<form action="deleteBg.php" method="post">
	<input type="checkbox" id="cb_all2" onClick="chkBoxes2(this.form)"/>&nbspSelect All
<?php
	//$temp = scandir("image/bgs");
	$allowext = array("jpg");
	$bgs = scanDirectories("image/bgs",$allowext);
	$_SESSION['bgsNumber'] = count($bgs);
	
	$bgs_num = 0;
	while($bgs_num<count($bgs)){
?>
	<br/>
	<input type="checkbox" name="bgs[]"id = <?php echo "ccb".$bgs_num ?> value=<?php echo $bgs_num?>>
	<img src=<?php echo $bgs[$bgs_num]?> widtd="50"height="50"/>
<?php
	$bgs_num++;
	}
?>
&nbsp&nbsp&nbsp&nbsp&nbsp<input type="submit" value="delete"/>
</form>
</aside>
</main>
<script>
	window.onload=function(){
	console.log(document.getElementById("cb0"));
	console.log(document.getElementById("ccb0"))
	};
</script>
</body>
</html>