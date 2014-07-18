<!DOCTYPE html>
<!---
	KinderDraw version 2.0
	5/20/2014
	Developed by Huang Qian, Han Kaiding
-->
<?php
	session_start();
	$allowext = array("jpg");
	$pics = scanDirectories("image/pics",$allowext);
	$bgs = scanDirectories("image/bgs",$allowext);
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
	
	$bgsNumber = count($bgs);
	$_SESSION['bgsNumber'] = $bgsNumber;
	$picsNumber = count($pics);
	$_SESSION['picsNumber'] = $picsNumber;
?>
<html lang="en">
<head>
	<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
	<meta content="utf-8" http-equiv="encoding"> 
	<meta name="viewport" content="width=device-width, initial-scale=0.5, user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>Canvas</title>
	<link rel="stylesheet" type="text/css" href="css/main.css"/>
	<script src="js/jquery-1.11.1.js"></script>
	<script src="js/base64.js"></script>
	<script src="js/canvas2image.js"></script>
	<script src="js/colorpicker.js"></script>
</head>
<body>
<nav>
	<table>
		<tr>
			<td><button onclick="showFile()" class="btn"/>File</button></td>
			<td><button onclick="showDraw()"class="btn"/>Draw</button></td>
			<td><button onclick="showBackground()"class="btn"/>Background</button></td>
			<td><button onclick="showPicture()"class="btn"/>Picture</button></td>
		</tr>
</table>
</nav>
<canvas id="mc2" width="260" height="128" style="solid #000000;"></canvas><br/>
<progress id="p_bar"value="50" max="100" style="width:260px;height:20px;" onclick="changePaintSize()"></progress>
<div id="optionButton">
<table id="fileButton">
		<tr>
			<td rowspan="2"><button><a href="login.html"><img src="image/admin.jpg"/></a></button></td>
			<td><button onclick="newCanvas()"><img src="image/new.jpg"/></button></td>
			<td><div class="inputWrapper"><input id="uploadimage" class="fileInput" type="file" name="impfile" onchange="importt()"/></div></td>
		</tr>
		<tr>
			<td><a id="dlLink" download><button onclick="exportt()"><img src="image/export.jpg"/></button></a></td>
			<td><button onclick="print()"><img src="image/print.jpg"/></button></td>
		</tr>
</table>
</div>
<div id="toolsButton">
	<table>
		<tr>
			<td><button onclick="drawLine()"><img src="image/pencil.jpg"/></button></td>
			<td><button onclick="drawSLine()"><img src="image/stroke.jpg"/></button></td>			
			<td><button onclick="strokeRec()"><img src="image/rectangle.jpg"/></button></td>
			<td><button onclick="strokeTri()"><img src="image/triangle.jpg"/></button></td>
			<td><button onclick="strokeOval()"><img src="image/oval.jpg"/></button></td>
			<td><button onclick="clearAll()"><img src="image/clear.jpg"/></button></td>
			<td><button onclick="removePic()"><img src="image/delete.jpg"/></button></td>			
			<td><button onclick="unDo()"><img src="image/undo.jpg" width="70px" height="70px"/></button></td>
		</tr>
		<tr>
			<td><button onclick="writeText()"><img src="image/text.jpg"/></button></td>
			<td><button onclick="erase()"><img src="image/eraser.jpg"/></button></td>
			<td><button onclick="drawRec()"><img src="image/fillRect.jpg"/></button></td>
			<td><button onclick="drawTri()"><img src="image/fillTri.jpg"/></button></td>
			<td><button onclick="drawOval()"><img src="image/fillOval.jpg"/></button></td>
			<td><button onclick="copy()"><img src="image/copy.jpg"/></button></td>
			<td><button onclick="selectCanvas()"><img src="image/select.jpg"/></button></td>
			<td><button onclick="reDo()"><img src="image/redo.jpg" width="70px" height="70px"/></button></td>
		</tr>
	</table>
</div>
<canvas id="myCanvas" width="1145" height="640"style="background-color:white"></canvas>
<div id="bgs"><!-- backgrounds -->
<table cellspacing="5px">
	<tr id="bgtabletr">
	</tr>
</table>
</div>
<div id="pictures"><!-- import pictures-->
</div>
<div id="can_tabs">
</div>
<script>
	var c = document.getElementById("myCanvas");
	var ctx=c.getContext('2d');
	//var currentCanvas = c;
	var paintColor = undefined;
	var paintSize = undefined;
	var x,y;
	var rgb_red;
	var rgb_green;
	var rgb_blue;
	var bgtabletr = document.getElementById("bgtabletr");
	var bgs = new Array();
	var canElements = new Array();
	var tdd;
	var c_events = new Array();
	//undo&redo
	var undoArray = [];
	var redoArray = [];
	var currentFrame;
	var frameCount = 0;
	var canvasData = new Image();
	var exp_background = new Image();// before export draw background
	exp_background.src="image/bgs/bg0.jpg";
	var bgChanged = false;
	var selectedImage;
	var degree = 0;
	var canvasArray = [];
	canvasArray.push(c);
	pushFrame();
	window.onload = drawLine();
	showDraw();
	var newCan;
	var canButton;
	canButton = document.createElement('button');
	canButton.style.width = "100px";
	canButton.style.height = "40px";
	canButton.style.backgroundColor = "#FF3399";
	canButton.style.padding = "6px, 25px";
	canButton.style.border = "1px solid";
	canButton.style.borderColor = "#FF66FF";
	canButton.style.borderBottomWidth = "6px";
	canButton.style.fontSize = "18px";
	canButton.style.fontWeight = "bold";
	canButton.style.color = "#FFF";
	canButton.style.textShadow = "0 1px 0 #000";
	canButton.style.borderRadius = "6px";
	canButton.innerHTML = "Canvas";
	canButton.setAttribute("onclick",'tabCanvas(this)');
	document.getElementById('can_tabs').appendChild(canButton);
	
	//textCursor
	var blinkingInterval = false;
	TextCursor = function (fillStyle, width) {
	this.fillStyle = fillStyle || 'rgba(0, 0, 0, 0.7)';
	this.width = width || 1;
	this.left = 0;
	this.top = 0;
};

TextCursor.prototype = {
   getHeight: function (context) {
      var h = ctx.measureText('M').width;
      return h + h/6;
   },
      
   createPath: function (context) {
      context.beginPath();
      context.rect(this.left, this.top,
                   this.width, this.getHeight(context));
   },
   
   draw: function (context, left, bottom) {
      context.save();

      this.left = left;
      this.top = bottom - this.getHeight(context);

      this.createPath(context);
      cursor.fillStyle = paintColor;
	  context.fillStyle = paintColor;
      context.fill();
         
      context.restore();
   },

   erase: function (context, imageData) {
      context.putImageData(imageData, 0, 0,
         this.left, this.top,
         this.width, this.getHeight(context));
   }
};

// Text lines.....................................................

TextLine = function (x, y) {
   this.text = '';
   this.left = x;
   this.bottom = y;
   this.caret = 0;
};

TextLine.prototype = {
   insert: function (text) {
      this.text = this.text.substr(0, this.caret) + text +
                  this.text.substr(this.caret);
      this.caret += text.length;
   },

   removeCharacterBeforeCaret: function () {
      if (this.caret === 0)
         return;

      this.text = this.text.substring(0, this.caret-1) +
                  this.text.substring(this.caret);

      this.caret--;
   },

   getWidth: function(context) {
      return context.measureText(this.text).width;
   },

   getHeight: function (context) {
      var h = context.measureText('W').width;
      return h + h/6;
   },

   draw: function(context) {
		context.save();
		context.textAlign = 'start';
		context.textBaseline = 'bottom';
		context.strokeText(this.text, this.left, this.bottom);
		context.fillStyle = paintColor;
		context.fillText(this.text, this.left, this.bottom);
		context.restore();
   },

   erase: function (context, imageData) {
      context.putImageData(imageData, 0, 0);
   }
};

    cursor = new TextCursor(),
	
    line = undefined,
	
    BLINK_TIME = 1000,
    BLINK_OFF = 300;

// General-purpose functions.....................................


function windowToCanvas(x, y) {
   var bbox = c.getBoundingClientRect();
   return { x: x - bbox.left * (c.width / bbox.width),
            y: y - bbox.top * (c.height / bbox.height)
          };
}

// Drawing surface...............................................

function saveDrawingSurface() {
   drawingSurfaceImageData = ctx.getImageData(0, 0,
                             c.width,
                             c.height);
}

// Text..........................................................



function blinkCursor(x, y) {
   clearInterval(blinkingInterval);
   blinkingInterval = setInterval( function (e) {
   cursor.erase(ctx, drawingSurfaceImageData);
      setTimeout( function (e) {
         if (cursor.left == x &&
            cursor.top + cursor.getHeight(ctx) == y) {
            cursor.draw(ctx, x, y);
         }
      }, 300);
   }, 1000);
}

function moveCursor(x, y) {
   cursor.erase(ctx, drawingSurfaceImageData);
   saveDrawingSurface();
   ctx.putImageData(drawingSurfaceImageData, 0, 0);
   cursor.draw(ctx, x, y);
   blinkCursor(x, y);
}

// Event handlers................................................

function writeT(e) {
		e.preventDefault;
		ctx.font = paintSize*200+"px Arial";
		var loc = windowToCanvas(e.clientX, e.clientY),
		fontHeight = ctx.measureText('W').width;
		fontHeight += fontHeight/6;
		line = new TextLine(loc.x, loc.y);
		moveCursor(loc.x, loc.y);
};
function writeText_tm(e) {
		e.preventDefault;
};
function writeText_te(e) {
		e.preventDefault;
};





// Key event handlers............................................

document.onkeydown = function (e) {
   if (e.keyCode === 8 || e.keyCode === 13) {
      // The call to e.preventDefault() suppresses
      // the browser's subsequent call to document.onkeypress(),
      // so only suppress that call for backspace and enter.
      e.preventDefault();
   }
   
   if (e.keyCode === 8) { // backspace
      ctx.save();

      line.erase(ctx, drawingSurfaceImageData);
      line.removeCharacterBeforeCaret();

      moveCursor(line.left + line.getWidth(ctx),
                 line.bottom);

      line.draw(ctx);

      ctx.restore();
   }
}
   
document.onkeypress = function (e) {
   var key = String.fromCharCode(e.which);

   if (e.keyCode !== 8 && !e.ctrlKey && !e.metaKey) {
     e.preventDefault(); // no further browser processing

     ctx.save();

     line.erase(ctx, drawingSurfaceImageData);
     line.insert(key);

     moveCursor(line.left + line.getWidth(ctx),
                line.bottom);

     ctx.shadowColor = 'rgba(0, 0, 0, 0.5)';
     ctx.shadowOffsetX = 1;
     ctx.shadowOffsetY = 1;
     ctx.shadowBlur = 2;

     line.draw(ctx);

     ctx.restore();
   }
}
// Initialization................................................
	ctx.font = "50px Arial";
	cursor.fillStyle = paintColor||'000000';
	cursor.strokeStyle = '000000';
	ctx.fillStyle = paintColor||'000000';
	ctx.strokeStyle = '000000';
	saveDrawingSurface();
	//textCursor end
	
	function newCanvas(){
		if(canvasArray.length>5){
			alert("Sorry,only 6 canvas supported!");
		}else{
			canButton = document.createElement('button');
			canButton.style.width = "100px";
			canButton.style.height = "40px";
			canButton.style.backgroundColor = "#FF3399";
			canButton.style.padding = "6px, 25px";
			canButton.style.border = "1px solid";
			canButton.style.borderColor = "#FF66FF";
			canButton.style.borderBottomWidth = "6px";
			canButton.style.fontSize = "18px";
			canButton.style.fontWeight = "bold";
			canButton.style.color = "#FFF";
			canButton.style.textShadow = "0 1px 0 #000";
			canButton.style.borderRadius = "6px";
			canButton.innerHTML="Canvas"+canvasArray.length;
			canButton.setAttribute("onclick",'tabCanvas(this)');
			document.getElementById('can_tabs').appendChild(canButton);
			newCan = document.createElement('canvas');
			newCan.width = 1145;
			newCan.height = 640;
			newCan.id="myCanvas"+(canvasArray.length);
			document.body.appendChild(newCan);
			$('#'+newCan.id).hide();
			newCan.style.position = "absolute";
			newCan.style.top = '0px';
			newCan.style.left = '0px';
			newCan.style.backgroundColor = 'white';
			currentCanvas = newCan;
			canvasArray.push(newCan);
			console.log(canvasArray);
		}
	}
	
	function tabCanvas(button){
		var canID = "my"+button.innerHTML;
		for(var canNums = 0; canNums<canvasArray.length;canNums++){
			$('#'+canvasArray[canNums].id).hide();
			console.log(canvasArray[canNums]);
		}
		$('#'+canID).show();
		c = document.getElementById(canID);
		ctx = c.getContext('2d');
		drawLine();
		console.log(canID);
	}
	
	function removePic(){
		console.log("before remove"+canElements.length);
		document.body.removeChild(selectedImage);
		canElements.splice( $.inArray(selectedImage, canElements), 1 );	
		console.log("after remove"+canElements.length);		
	}
	
	function drawSLine(){
		var x1,y1,x2,y2;
		console.log("before remove"+c_events);
		remove_all_events();
		console.log("after remove"+c_events);
		c.addEventListener('touchstart',drawSLine_ts,false);
		c.addEventListener('touchmove',drawSLine_tm,false);
		c.addEventListener('touchend',drawSLine_te,false);
		c_events.push(['touchstart', drawSLine_ts]);
		c_events.push(['touchmove', drawSLine_tm]);
		c_events.push(['touchend', drawSLine_te]);
		console.log("after push"+c_events);
		function drawSLine_ts(e){
			e.preventDefault();
			var touchobj = e.targetTouches[0];
			x1 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y1 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			ctx.beginPath();
			ctx.moveTo(x1,y1);
		}
		function drawSLine_tm(e){
		e.preventDefault();
		var touchobj = e.targetTouches[0];
		x2 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
		y2 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
		}
		function drawSLine_te(e){
			e.preventDefault();
			ctx.lineTo(x2,y2);
			ctx.strokeStyle = paintColor;
			ctx.lineWidth = paintSize*20;
			ctx.stroke();
			ctx.closePath();
			pushFrame();
			console.log(ctx.strokeStyle+","+ctx.lineWidth);
			console.log("from ("+x1+","+y1+") to ("+x2+","+y2+")")
			}
	}
	function clearAll(){
		//var confirmInfo = confirm("Do you want to save the current canvas?");
		clearCanvas();
		for(var canLength = 0; canLength < canElements.length; canLength++){
			$("body").find("#picture"+canLength).remove();
		}
	}
	function copy(){
		var copy = new Image();
		selectedImage.index++;
		copy.id = "picture"+canElements.length;
		copy.src = selectedImage.src;
		copy.width = selectedImage.width;
		copy.height = selectedImage.height;
		copy.style.position = "absolute";
		copy.style.left = parseInt(selectedImage.style.left)+10*selectedImage.index+'px';
		copy.style.top = parseInt(selectedImage.style.top)+10*selectedImage.index+'px';
		copy.addEventListener('touchstart',select_ts,false);
		copy.addEventListener('touchmove',select_tm,false);
		copy.addEventListener('touchend',select_te,false);
		document.body.appendChild(copy);
		canElements.push(copy);//add to canElement array. canElement stores all the images
		console.log(copy);
		console.log(selectedImage.index);
	}
	
	function drawOval(){
		remove_all_events();
		c.addEventListener('touchstart',drawOval_ts,false);
		c.addEventListener('touchmove',drawOval_tm,false);
		c.addEventListener('touchend',drawOval_te,false);
		c_events.push(['touchstart',drawOval_ts]);
		c_events.push(['touchmove',drawOval_tm]);
		c_events.push(['touchend',drawOval_te]);
		console.log("after push"+c_events);
		function drawOval_ts(e){
			e.preventDefault();
			touchstart = true;
			var touchobj = e.targetTouches[0];
			x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			if(x>0&&x<1920&&y>0&&y<800){
			x1 = x;
			y1 = y;
			console.log("start from point ("+x+","+y+")");
			}
		}
		function drawOval_tm(e){
		e.preventDefault();
		var touchobj = e.targetTouches[0];
		x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
		y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
		if(touchstart&&x>0&&x<1920&&y>0&&y<800){
		x2 = x;
		y2 = y;
		dx = x2 - x1;
		dy = y2 - y1;
		/*
		if(dx>0&&dy>0)
		{
			console.log("drawing rec("+x1+","+y1+","+Math.abs(dx)+","+Math.abs(dy)+")");
			ctx.strokeRect(x1,y1,Math.abs(dx),Math.abs(dy));	
			console.log("clearing rec("+x1+","+y1+","+Math.abs(dx)+","+Math.abs(dy)+")");
			setTimeout(ctx.clearRect(x1,y1,Math.abs(dx),Math.abs(dy)),20);			
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx>0&&dy<0){
			ctx.strokeRect(x1,y2,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x1,y2,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx<0&&dy>0){
			ctx.strokeRect(x2,y1,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x2,y1,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx<0&&dy<0){
			ctx.strokeRect(x2,y2,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x2,y2,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}
		console.log('drawing rec');
		*/
		}
		}
		function drawOval_te(e){
		e.preventDefault();
		if(touchstart&&x>0&&x<1920&&y>0&&y<800){
		var scale = Math.abs(dx/dy);
		ctx.fillStyle = paintColor;
		ctx.save();
		ctx.scale(scale, 1);
		ctx.beginPath();
		ctx.arc((x1+dx/2)/scale,y1+dy/2,Math.abs(dy/2), 0, 2 * Math.PI, false);
		console.log(x1+" "+y1);
		ctx.restore();
		ctx.fill();
		touchstart = false;
		pushFrame();
		}
		}
	}
	function strokeOval(){
		remove_all_events();
		c.addEventListener('touchstart',drawOval_ts,false);
		c.addEventListener('touchmove',drawOval_tm,false);
		c.addEventListener('touchend',drawOval_te,false);
		c_events.push(['touchstart',drawOval_ts]);
		c_events.push(['touchmove',drawOval_tm]);
		c_events.push(['touchend',drawOval_te]);
		console.log("after push"+c_events);
		function drawOval_ts(e){
			e.preventDefault();
			touchstart = true;
			var touchobj = e.targetTouches[0];
			x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			if(x>0&&x<1920&&y>0&&y<800){
			x1 = x;
			y1 = y;
			console.log("start from point ("+x+","+y+")");
			}
		}
		function drawOval_tm(e){
		e.preventDefault();
		var touchobj = e.targetTouches[0];
		x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
		y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
		if(touchstart&&x>0&&x<1920&&y>0&&y<800){
		x2 = x;
		y2 = y;
		dx = x2 - x1;
		dy = y2 - y1;
		/*
		if(dx>0&&dy>0)
		{
			console.log("drawing rec("+x1+","+y1+","+Math.abs(dx)+","+Math.abs(dy)+")");
			ctx.strokeRect(x1,y1,Math.abs(dx),Math.abs(dy));	
			console.log("clearing rec("+x1+","+y1+","+Math.abs(dx)+","+Math.abs(dy)+")");
			setTimeout(ctx.clearRect(x1,y1,Math.abs(dx),Math.abs(dy)),20);			
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx>0&&dy<0){
			ctx.strokeRect(x1,y2,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x1,y2,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx<0&&dy>0){
			ctx.strokeRect(x2,y1,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x2,y1,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx<0&&dy<0){
			ctx.strokeRect(x2,y2,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x2,y2,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}
		console.log('drawing rec');
		*/
		}
		}
		function drawOval_te(e){
		e.preventDefault();
		if(touchstart&&x>0&&x<1920&&y>0&&y<800){
		var scale = Math.abs(dx/dy);
		ctx.fillStyle = paintColor;
		ctx.save();
		ctx.scale(scale, 1);
		ctx.beginPath();
		ctx.arc((x1+dx/2)/scale,y1+dy/2,Math.abs(dy/2), 0, 2 * Math.PI, false);
		console.log(x1+" "+y1);
		ctx.restore();
		ctx.stroke();
		touchstart = false;
		pushFrame();
		}
		}
	}
	function selectCanvas(){
		remove_all_events();
		c.addEventListener('touchstart',selectCanvas_ts,false);
		c.addEventListener('touchmove',selectCanvas_tm,false);
		c.addEventListener('touchend',selectCanvas_te,false);
		c_events.push(['touchstart',selectCanvas_ts]);
		c_events.push(['touchmove',selectCanvas_tm]);
		c_events.push(['touchend',selectCanvas_te]);
		console.log("after push"+c_events);
		var positionX,positionY,width,height;
		var dx,dy,dxx,dyy;
		var px1,px2,px3,py1,py2,py3,ppx1,ppx2,ppx3,ppy1,ppy2,ppy3;
		var cropImage;
		var selectedArea;
			function selectCanvas_ts(e){
				e.preventDefault();
				touchstart = true;
				var touchobj = e.targetTouches[0];
				x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
				y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
				if(x>0&&x<c.width&&y>0&&y<c.height){
				x1 = x;
				y1 = y;
				positionX = x;
				positionY = y;
				console.log("start from point ("+x+","+y+")");
				}
			}
			
			function selectCanvas_tm(e){
			e.preventDefault();
			var touchobj = e.targetTouches[0];
			x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height){
				x2 = x;
				y2 = y;
				dx = x2 - x1;
				dy = y2 - y1;
				width = dx;
				height = dy;
				}
			}
			
			function selectCanvas_te(e){
				e.preventDefault();
				if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height){
				ctx.save();
				ctx.lineWidth = 1;
				ctx.setLineDash([5]);
				ctx.fillStyle = "AABBCC55";
				ctx.strokeStyle = "333EFF";
				ctx.strokeRect(x1,y1,dx,dy);
				ctx.restore();
				touchstart = false;
				pushFrame();
				selectedArea = ctx.getImageData(positionX,positionY,width,height);
				ctx.clearRect(positionX-1,positionY-1,width+2,height+2);
				var c_temp= document.createElement('canvas');
				c_temp.setAttribute('id', 'c_temp');
				c_temp.width = width;
				c_temp.height = height;
				c_temp.getContext('2d').putImageData(selectedArea, 0, 0);
				selectedArea = c_temp.toDataURL();
				c_temp.style.display='none';
				console.log("dataURL : "+selectedArea);
				var cropImage = new Image();
				cropImage.id = "picture"+canElements.length;
				cropImage.src = selectedArea;
				cropImage.width = width;
				cropImage.height = height;
				cropImage.style.position = 'absolute';
				cropImage.style.left = positionX+'px';
				cropImage.style.top = positionY+'px';
				canElements.push(cropImage);
				document.body.appendChild(cropImage);
				console.log(c_temp);
				console.log(cropImage);
				cropImage.addEventListener('touchstart',select_ts,false);
				cropImage.addEventListener('touchmove',select_tm,false);
				cropImage.addEventListener('touchend',select_te,false);
				remove_all_events();
				}
			}
	}
	
	function showFile(){
		$("#optionButton").show();
		$("#pictures").hide();
		$("#bgs").hide();
		$("#p_bar").hide();
		$("#toolsButton").hide();
		$("#mc2").hide();
		$("#addBackground").hide();
		$("#addPicture").hide();
	}
	
	function showDraw(){
		$("#optionButton").hide();
		$("#pictures").hide();
		$("#bgs").hide();
		$("#p_bar").show();
		$("#toolsButton").show();
		$("#mc2").show();
		$("#addBackground").hide();
		$("#addPicture").hide();
	}
	
	function showBackground(){
		$("#optionButton").hide();
		$("#pictures").hide();
		$("#bgs").show();
		$("#p_bar").hide();
		$("#toolsButton").hide();
		$("#mc2").hide();
		$("#addBackground").show();
		$("#addPicture").hide();
	}
	
	function showPicture(){
		$("#optionButton").hide();
		$("#pictures").show();
		$("#bgs").hide();
		$("#p_bar").hide();
		$("#toolsButton").hide();
		$("#mc2").hide();
		$("#addBackground").hide();
		$("#addPicture").show();
	}
	
	function unDo(){
		if(frameCount > 1)
		{
			canvasData.src = undoArray[frameCount-2];  //[0 = blank,1,2]
			canvasData.width = "1740";
			canvasData.height = "720";
			clearCanvas();
			ctx.drawImage(canvasData,0,0);
			redoArray.push(undoArray.pop());
			frameCount--;
		}else{
			clearCanvas();
		}
		console.log("undo : "+canvasData.src);
	}
	
	function reDo(){
		if(redoArray.length!=0){
			canvasData.src = redoArray.pop();
			canvasData.width = "1740";
			canvasData.height = "720";
			if(canvasData.src!=undefined&&canvasData.src!=c.toDataURL()){
			clearCanvas();
			ctx.drawImage(canvasData,0,0);
			undoArray.push(canvasData.src);
			frameCount++;
			}
			console.log(frameCount);
			}
	}
	
	function pushFrame(){
		currentFrame = c.toDataURL();
		undoArray.push(currentFrame);
		frameCount++;
		console.log(currentFrame);
		console.log("undoArray.length : "+undoArray.length);
	}
	//end undo&redo
	for(var m=0;m<<?php echo $bgsNumber ?>;m++){
		bgs[m] = new Image();
		bgs[m].src="image/bgs/bg"+m+".jpg";
		bgs[m].width="150";
		bgs[m].height="150";
		tdd = document.createElement('td');
		tdd.appendChild(bgs[m]);
		bgtabletr.style.overflow = 'auto';
		bgtabletr.style.width = '200px';
		bgtabletr.appendChild(tdd);
		bgs[m].addEventListener('touchstart',function(){
			c.style.backgroundImage = "url("+this.src+")";
			exp_background.src=this.src;
			bgChanged = true;
			console.log("!!!"+this.src);
		},false);
	}
	function writeText(){
		remove_all_events();
		c.addEventListener('click',writeT,false);
		c_events.push(['click',writeT]);
	}
	
	function erase(){
		var eraseSize = 10;
		var x1,x2,x3,y1,y2,y3,x11,x22,x33,y11,y22,y33;
		var touchobj,touch2,touch3;
		var touchstart = false;
		var pennumber = 0;
		console.log("before remove"+c_events);
		remove_all_events();
		console.log("after remove"+c_events);
		c.addEventListener('touchstart',erase_ts,false);
		c.addEventListener('touchmove',erase_tm,false);
		c.addEventListener('touchend',erase_te,false);
		c_events.push(['touchstart', erase_ts]);
		c_events.push(['touchmove', erase_tm]);
		c_events.push(['touchend', erase_te]);
		console.log("after push"+c_events);
		
		function erase_ts(e){/*
		e.preventDefault();
		touchstart = true;
		if(e.targetTouches.length==1){
			touchobj = e.targetTouches[0];
			x1 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y1 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			ctx.beginPath();
			ctx.moveTo(x1,y1);
		}else if(e.targetTouches.length==2){
			touchobj = e.targetTouches[0];
			x11 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y11 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			//touch2
			touch2 = e.targetTouches[1];
			x22= parseInt(touch2.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y22 = parseInt(touch2.clientY) - this.getBoundingClientRect().top;
			}
		else if(e.targetTouches.length==3){
			touchobj = e.targetTouches[0];
			x11 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y11 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			console.log
			//touch2
			touch2 = e.targetTouches[1];
			x22= parseInt(touch2.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y22 = parseInt(touch2.clientY) - this.getBoundingClientRect().top;
			//touch3
			touch3 = e.targetTouches[2];
			x33= parseInt(touch3.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y33 = parseInt(touch3.clientY) - this.getBoundingClientRect().top; 
			}
			*/
		}
		
		
		function erase_tm(e){
		e.preventDefault();
			if(e.targetTouches.length==1){
				touchobj = e.targetTouches[0];
				x1 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
				y1 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
				if(x1>0&&x1<c.width&&y1>0&&y1<c.height){
				if(paintSize){
				eraseSize = paintSize*30;
				}
				ctx.clearRect(x1,y1,eraseSize,eraseSize);
				console.log(eraseSize);
				}
			}else if(e.targetTouches.length==2){
					touchobj = e.targetTouches[0];
					x1 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y1 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
					if(x1>0&&x1<c.width&&y1>0&&y1<c.height){
				
					if(paintSize){
				eraseSize = paintSize*30;
				}
				ctx.clearRect(x1,y1,eraseSize,eraseSize);
					console.log('t1 erasing');
					x11 = x1; y11 = y1;
					}
					
					touch2 = e.targetTouches[1];
					x2 = parseInt(touch2.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y2 = parseInt(touch2.clientY) - this.getBoundingClientRect().top;
					if(x2>0&&x2<c.width&&y2>0&&y2<c.height){
					
					if(paintSize){
				eraseSize = paintSize*30;
				}
				ctx.clearRect(x2,y2,eraseSize,eraseSize);
					console.log('t2 erasing');
					x22 = x2;y22 = y2;
					}
			}
			else if(e.targetTouches.length==3){
					touchobj = e.targetTouches[0];
					x1 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y1 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
					if(x1>0&&x1<c.width&&y1>0&&y1<c.height){
					
					if(paintSize){
				eraseSize = paintSize*30;
				}
				ctx.clearRect(x1,y1,eraseSize,eraseSize);
					console.log('t1 erasing');
					x11 = x1; y11 = y1;
					}
					
					touch2 = e.targetTouches[1];
					x2 = parseInt(touch2.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y2 = parseInt(touch2.clientY) - this.getBoundingClientRect().top;
					if(x2>0&&x2<c.width&&y2>0&&y2<c.height){
					
					if(paintSize){
				eraseSize = paintSize*30;
				}
				ctx.clearRect(x2,y2,eraseSize,eraseSize);
					console.log('t2 erasing');
					x22 = x2;y22 = y2;
					}
					
					touch3 = e.targetTouches[2];
					x3 = parseInt(touch2.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y3 = parseInt(touch2.clientY) - this.getBoundingClientRect().top;
					if(x3>0&&x3<c.width&&y3>0&&y3<c.height){

					if(paintSize){
				eraseSize = paintSize*30;
				}
				ctx.clearRect(x3,y3,eraseSize,eraseSize);
					console.log('t3 erasing');
					x33 = x3;y33 = y3;
					}
			}
		}

		function erase_te(e){
		e.preventDefault();
		touchstart = false;
		console.log('end');
		console.log(c_events);
		pushFrame();
		}
	
	}
	
	//remove all
	function remove_all_events() {
		if(blinkingInterval){
			clearInterval(blinkingInterval);
		}
		for(var eventLength = 0; c_events[eventLength];eventLength++)
		{
			c.removeEventListener(c_events[eventLength][0],c_events[eventLength][1],false);
		}
	}
	function drawLine(){
		var x1,x2,x3,y1,y2,y3,x11,x22,x33,y11,y22,y33;
		var touchobj,touch2,touch3;
		var touchstart = false;
		var pennumber = 0;
		console.log("before remove"+c_events);
		remove_all_events();
		console.log("after remove"+c_events);
		c.addEventListener('touchstart',drawLine_ts,false);
		c.addEventListener('touchmove',drawLine_tm,false);
		c.addEventListener('touchend',drawLine_te,false);
		c_events.push(['touchstart', drawLine_ts]);
		c_events.push(['touchmove', drawLine_tm]);
		c_events.push(['touchend', drawLine_te]);
		console.log("after push"+c_events);
		function drawLine_ts(e){
		e.preventDefault();
		touchstart = true;
		if(e.targetTouches.length==1){
			touchobj = e.targetTouches[0];
			x1 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y1 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			ctx.beginPath();
			ctx.moveTo(x1,y1);
		}else if(e.targetTouches.length==2){
			touchobj = e.targetTouches[0];
			x11 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y11 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			//touch2
			touch2 = e.targetTouches[1];
			x22= parseInt(touch2.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y22 = parseInt(touch2.clientY) - this.getBoundingClientRect().top;
			}
		else if(e.targetTouches.length==3){
			touchobj = e.targetTouches[0];
			x11 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y11 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			console.log
			//touch2
			touch2 = e.targetTouches[1];
			x22= parseInt(touch2.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y22 = parseInt(touch2.clientY) - this.getBoundingClientRect().top;
			//touch3
			touch3 = e.targetTouches[2];
			x33= parseInt(touch3.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y33 = parseInt(touch3.clientY) - this.getBoundingClientRect().top; 
			}
		}
		
		function drawLine_tm(e){
		e.preventDefault();
			if(e.targetTouches.length==1){
				touchobj = e.targetTouches[0];
				x1 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
				y1 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
				if(x1>10&&x1<c.width&&y1>0&&y1<c.height){
				ctx.strokeStyle = paintColor;
				ctx.lineWidth = paintSize*20;
				ctx.lineTo(x1,y1);
				ctx.stroke();
				console.log('drawing');
				}
			}else if(e.targetTouches.length==2){
					touchobj = e.targetTouches[0];
					x1 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y1 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
					if(x1>0&&x1<c.width&&y1>0&&y1<c.height){
					ctx.strokeStyle = paintColor;
					ctx.lineWidth = paintSize*20;
					ctx.moveTo(x11,y11);
					ctx.lineTo(x1,y1);
					ctx.stroke();
					console.log('t1 drawing');
					x11 = x1; y11 = y1;
					}
					
					touch2 = e.targetTouches[1];
					x2 = parseInt(touch2.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y2 = parseInt(touch2.clientY) - this.getBoundingClientRect().top;
					if(x2>0&&x2<c.width&&y2>0&&y2<c.height){
					ctx.strokeStyle = paintColor;
					ctx.lineWidth = paintSize*20;
					ctx.moveTo(x22,y22);
					ctx.lineTo(x2,y2);
					ctx.stroke();
					console.log('t2 drawing');
					x22 = x2;y22 = y2;
					}
			}
			else if(e.targetTouches.length==3){
					touchobj = e.targetTouches[0];
					x1 = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y1 = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
					if(x1>0&&x1<c.width&&y1>0&&y1<c.height){
					ctx.strokeStyle = paintColor;
					ctx.lineWidth = paintSize*20;
					ctx.moveTo(x11,y11);
					ctx.lineTo(x1,y1);
					ctx.stroke();
					console.log('t1 drawing');
					x11 = x1; y11 = y1;
					}
					
					touch2 = e.targetTouches[1];
					x2 = parseInt(touch2.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y2 = parseInt(touch2.clientY) - this.getBoundingClientRect().top;
					if(x2>0&&x2<c.width&&y2>0&&y2<c.height){
					ctx.strokeStyle = paintColor;
					ctx.lineWidth = paintSize*20;
					ctx.moveTo(x22,y22);
					ctx.lineTo(x2,y2);
					ctx.stroke();
					console.log('t2 drawing');
					x22 = x2;y22 = y2;
					}
					
					touch3 = e.targetTouches[2];
					x3 = parseInt(touch3.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
					y3 = parseInt(touch3.clientY) - this.getBoundingClientRect().top;
					if(x3>0&&x3<c.width&&y3>0&&y3<c.height){
					ctx.strokeStyle = paintColor;
					ctx.lineWidth = paintSize*20;
					ctx.moveTo(x33,y33);
					ctx.lineTo(x3,y3);
					ctx.stroke();
					console.log('t3 drawing');
					x33 = x3;y33 = y3;
					}
			}
		}

		function drawLine_te(e){
		e.preventDefault();
		touchstart = false;
		console.log('end');
		console.log(c_events);
		pushFrame();
		}
	}

	function drawRec(){
		console.log("before remove"+c_events);
		remove_all_events();
		console.log("after remove"+c_events);
		c.addEventListener('touchstart',drawRec_ts,false);
		c.addEventListener('touchmove',drawRec_tm,false);
		c.addEventListener('touchend',drawRec_te,false);
		c_events.push(['touchstart',drawRec_ts]);
		c_events.push(['touchmove',drawRec_tm]);
		c_events.push(['touchend',drawRec_te]);
		console.log("after push"+c_events);
		function drawRec_ts(e){
			e.preventDefault();
			touchstart = true;
			var touchobj = e.targetTouches[0];
			x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			if(x>0&&x<c.width&&y>0&&y<c.height){
			x1 = x;
			y1 = y;
			console.log("start from point ("+x+","+y+")");
			}
		}
		function drawRec_tm(e){
		e.preventDefault();
		var touchobj = e.targetTouches[0];
		x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
		y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
		if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height){
		x2 = x;
		y2 = y;
		dx = x2 - x1;
		dy = y2 - y1;
		/*
		if(dx>0&&dy>0)
		{
			console.log("drawing rec("+x1+","+y1+","+Math.abs(dx)+","+Math.abs(dy)+")");
			ctx.strokeRect(x1,y1,Math.abs(dx),Math.abs(dy));	
			console.log("clearing rec("+x1+","+y1+","+Math.abs(dx)+","+Math.abs(dy)+")");
			setTimeout(ctx.clearRect(x1,y1,Math.abs(dx),Math.abs(dy)),20);			
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx>0&&dy<0){
			ctx.strokeRect(x1,y2,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x1,y2,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx<0&&dy>0){
			ctx.strokeRect(x2,y1,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x2,y1,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx<0&&dy<0){
			ctx.strokeRect(x2,y2,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x2,y2,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}
		console.log('drawing rec');	
		*/
			}
		}
		function drawRec_te(e){
		e.preventDefault();
		if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height){
		ctx.fillStyle = paintColor;
		ctx.fillRect(x1,y1,dx,dy);
		touchstart = false;
		pushFrame();
		}
		}
	}
	function strokeRec(){
		console.log("before remove"+c_events);
		remove_all_events();
		console.log("after remove"+c_events);
		c.addEventListener('touchstart',drawRec_ts,false);
		c.addEventListener('touchmove',drawRec_tm,false);
		c.addEventListener('touchend',drawRec_te,false);
		c_events.push(['touchstart',drawRec_ts]);
		c_events.push(['touchmove',drawRec_tm]);
		c_events.push(['touchend',drawRec_te]);
		console.log("after push"+c_events);
		function drawRec_ts(e){
			e.preventDefault();
			touchstart = true;
			var touchobj = e.targetTouches[0];
			x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			if(x>0&&x<c.width&&y>0&&y<c.height){
			x1 = x;
			y1 = y;
			console.log("start from point ("+x+","+y+")");
			}
		}
		function drawRec_tm(e){
		e.preventDefault();
		var touchobj = e.targetTouches[0];
		x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
		y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
		if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height){
		x2 = x;
		y2 = y;
		dx = x2 - x1;
		dy = y2 - y1;
		/*
		if(dx>0&&dy>0)
		{
			console.log("drawing rec("+x1+","+y1+","+Math.abs(dx)+","+Math.abs(dy)+")");
			ctx.strokeRect(x1,y1,Math.abs(dx),Math.abs(dy));	
			console.log("clearing rec("+x1+","+y1+","+Math.abs(dx)+","+Math.abs(dy)+")");
			setTimeout(ctx.clearRect(x1,y1,Math.abs(dx),Math.abs(dy)),20);			
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx>0&&dy<0){
			ctx.strokeRect(x1,y2,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x1,y2,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx<0&&dy>0){
			ctx.strokeRect(x2,y1,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x2,y1,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}else if(dx<0&&dy<0){
			ctx.strokeRect(x2,y2,Math.abs(dx),Math.abs(dy));
			ctx.clearRect(x2,y2,Math.abs(dx),Math.abs(dy));
			//console.log("x1 : "+x1+"\t y1 : "+y1+"\nx2 : "+x2+"\t y2 : "+y2+"\ndx : "+dx+"\t dy : "+dy);
		}
		console.log('drawing rec');	
		*/
			}
		}
		function drawRec_te(e){
		e.preventDefault();
		if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height){
		ctx.fillStyle = paintColor;
		ctx.strokeRect(x1,y1,dx,dy);
		touchstart = false;
		pushFrame();
		}
		}
	}	
	function drawTri(){
		ctx.fillStyle = paintColor;
		remove_all_events();
		c.addEventListener('touchstart',drawTri_ts,false);
		c.addEventListener('touchmove',drawTri_tm,false);
		c.addEventListener('touchend',drawTri_te,false);
		c_events.push(['touchstart',drawTri_ts]);
		c_events.push(['touchmove',drawTri_tm]);
		c_events.push(['touchend',drawTri_te]);
		function drawTri_ts(e){
		e.preventDefault();
		touchstart = true;
		var touchobj = e.targetTouches[0];
		x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
		y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
		if(x>0&&x<c.width&&y>0&&y<c.height){
		x1 = x;
		y1 = y;
		console.log("start from point ("+x+","+y+")");
		}
		}
	
		function drawTri_tm(e){
		e.preventDefault();
		var touchobj = e.targetTouches[0];
		x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
		y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
		if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height){
		x2 = x;
		y2 = y;
		}
		}

		function drawTri_te(e){
		e.preventDefault();
		if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height&&x1!=x2){
		ctx.fillStyle = paintColor;
		ctx.beginPath();
		ctx.moveTo(x1,y1);
		ctx.lineTo(x2,y2);
		ctx.lineTo(2*x1-x2,y2);
		ctx.closePath();
		ctx.fill();
		touchstart = false;
		pushFrame();
			}
		}
	}
	function strokeTri(){
		ctx.fillStyle = paintColor;
		remove_all_events();
		c.addEventListener('touchstart',drawTri_ts,false);
		c.addEventListener('touchmove',drawTri_tm,false);
		c.addEventListener('touchend',drawTri_te,false);
		c_events.push(['touchstart',drawTri_ts]);
		c_events.push(['touchmove',drawTri_tm]);
		c_events.push(['touchend',drawTri_te]);
		function drawTri_ts(e){
		e.preventDefault();
		touchstart = true;
		var touchobj = e.targetTouches[0];
		x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
		y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
		if(x>0&&x<c.width&&y>0&&y<c.height){
		x1 = x;
		y1 = y;
		console.log("start from point ("+x+","+y+")");
		}
		}
	
		function drawTri_tm(e){
		e.preventDefault();
		var touchobj = e.targetTouches[0];
		x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
		y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
		if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height){
		x2 = x;
		y2 = y;
		}
		}

		function drawTri_te(e){
		e.preventDefault();
		if(touchstart&&x>0&&x<c.width&&y>0&&y<c.height&&x1!=x2){
		ctx.fillStyle = paintColor;
		ctx.moveTo(x1,y1);
		ctx.lineTo(x2,y2);
		ctx.lineTo(2*x1-x2,y2);
		ctx.lineTo(x1,y1);
		ctx.stroke();
		touchstart = false;
		pushFrame();
			}
		}
	}
	
	function clearCanvas(){
		ctx.clearRect(0,0,c.width,c.height);
	}
	
	function exportt(){
		ctx.save();
		ctx.globalCompositeOperation = "destination-over";
		pushFrame();
		ctx.drawImage(exp_background,0,0,c.width,c.height);
		/*
		for(var can = 0;can<canElements.length;can++)
		{
			ctx.drawImage(canElements[can],parseInt(canElements[can].style.top),parseInt(canElements[can].style.left),canElements[can].width,canElements[can].height);
			console.log(canElements[can]+" top : "+ parseInt(canElements[can].style.top) + " left: "+ parseInt(canElements[can].style.left)+" width : "+canElements[can].width+" height : "+canElements[can].height);
		}
		*/
		var imgURI = c.toDataURL("image/jpeg");
		unDo();//remove the background which is draw on the canvas.
		ctx.restore();
		var dl = document.getElementById('dlLink');
		dl.href=imgURI;
	}
	
	function image_ts(e){
				e.preventDefault();
				var touchobj = e.targetTouches[0];
				x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
				y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
				if(x>0){
					picTouchstart = true;
				}
				console.log("start");
	}
	
	function image_tm(e){
				e.preventDefault();
				var touchobj = e.targetTouches[0];
				x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
				y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
				console.log("moving");
	}
	
	function image_te(e){
				e.preventDefault();
				var imgIndex = this.id.substring(3);
				if(picTouchstart){
					//ctx.drawImage(this,x+1500,y-125,250,250);//x y is based on picture index or change it to pageX Y later.
					var newImage = document.createElement("IMG");
					newImage.src = this.src;
					newImage.id = "picture"+canElements.length;
					newImage.width = "150";
					newImage.height = "150";
					newImage.style.position = "absolute";
					newImage.style.left = (x-200+((parseInt(imgIndex)+1)*150))+"px";
					newImage.style.top = (y+680)+"px";
					if(parseInt(newImage.style.left)>1000||parseInt(newImage.style.top)>1000){
						newImage.style.left = "500px";
						newImage.style.top = "300px";
					}
					document.body.appendChild(newImage);
					console.log("end already");
					canElements.push(newImage);
					select();
					picTouchstart = false;
					console.log(newImage);
					console.log(this);
					console.log("no."+imgIndex+" picture");
					console.log(canElements);
					remove_all_events();
				}
	}
			
	var picsDiv = document.getElementById("pictures");
	var imgg = new Array();
	var picTouchstart = false;
	for(var i=0;i<<?php echo $picsNumber;?>;i++)
		{
			imgg[i]=new Image();
			imgg[i].src="image/pics/"+i+".png";
			imgg[i].id="img"+i;
			imgg[i].width="150";
			imgg[i].height="150";
			picsDiv.appendChild(imgg[i]);
			imgg[i].addEventListener('touchstart',image_ts,false);
			
			imgg[i].addEventListener('touchmove',image_tm,false);
			
			imgg[i].addEventListener('touchend',image_te,false);
		}
	var touch_scale = false;
	var touch_move = false;
	function select_ts(e){
			e.preventDefault();
			console.log("sssssstart!");
			touch_move = true;
			if(e.targetTouches.length==1){
					var p1 = e.targetTouches[0];
					console.log((p1.pageX)+" "+p1.pageY);
			}
			else if(e.targetTouches.length==2){
					touch_move = false;
					touch_scale = true;
					var p1 = e.targetTouches[0];
					var p2 = e.targetTouches[1];
					dx = Math.abs(p1.pageX - p2.pageX);
					dy = Math.abs(p1.pageY - p2.pageY);
				}
			else if(e.targetTouches.length>=3){
					touch_scale = false;
					var p1 = e.targetTouches[0];
					var p2 = e.targetTouches[1];
					var p3 = e.targetTouches[2];
					px1 = p1.pageX;
					px2 = p2.pageX;
					px3 = p3.pageX;
					py1 = p1.pageY;
					py2 = p2.pageY;
					py3 = p3.pageY;
				}
	}
	
	function select_tm(e){
				e.preventDefault();
				if(touch_move&&e.targetTouches.length==1){
					var touch = e.targetTouches[0];
					this.style.left = touch.pageX-this.width/2 + 'px';
					this.style.top = touch.pageY-this.height/2 + 'px';
				}else if(touch_scale&&e.targetTouches.length == 2){
					var p11 = e.targetTouches[0];
					var p22= e.targetTouches[1];
					dxx = Math.abs(p11.pageX - p22.pageX);
					dyy = Math.abs(p11.pageY - p22.pageY);
					
					var scale = (dxx/dx)+(dyy/dy);
					if(scale<0.3)
					{
						this.width = 0.3*125;
						this.height= 0.3*125;
					}else if(scale>4){
						this.width = 4*125;
						this.height= 4*125;
					}else{
						this.width = scale*125;
						this.height= scale*125;
					}
					
					/*
					if(this.width<100){
						this.width = 100;
					}
					else if(this.height<100){
						this.width = 100;
					}else if(this.width>500){
						this.width = 500;
					}else if(this.height>500){
						this.height=500;
					}else{
						this.width += 0.1*(dxx-dx);
						this.height+= 0.1*(dyy-dy);
					}
					dx = dxx;
					dy = dyy;
					*/
					
				}else if(e.targetTouches.length >= 3){
					var p1 = e.targetTouches[0];
					var p2= e.targetTouches[1];
					var p3= e.targetTouches[2];
					ppx1 = p1.pageX;
					ppx2 = p2.pageX;
					ppx3 = p3.pageX;
					ppy1 = p1.pageY;
					ppy2 = p2.pageY;
					ppy3 = p3.pageY;
					function compare(a,b){
						if(a>b){
							return 1;
						}
						else return 0;
					}
					var rtt = compare(ppy1,py1)+compare(ppy2,py2)+compare(ppy3,py3);
					console.log("rtt : "+rtt);
					degree-=2;
					this.style.WebkitTransform="rotate("+degree+"deg)";
					/*
					if(rtt==2){
						degree-=1;
						this.style.WebkitTransform="rotate("+degree+"deg)";
					}else if(rtt==1){ 
						degree+=1;
						this.style.WebkitTransform="rotate("+degree+"deg)";
					}
					*/
				}
	}
	
	function select_te(e){
				e.preventDefault();
				selectedImage = this;
				selectedImage.index = 0;
				console.log("touchend");
	}
	
	function select(){
		// add listener for all the elements on canvas -- drag,scale,rotate...
		//p.pageX - canvas.getBoundingClientRect().left;
		var canvasTouchstart = false;
		var dx,dy,dxx,dyy;
		var px1,px2,px3,py1,py2,py3,ppx1,ppx2,ppx3,ppy1,ppy2,ppy3;
		for(var eles=0;eles < canElements.length;eles++)
		{
			canElements[eles].addEventListener('touchstart',select_ts,false);
			
			canElements[eles].addEventListener('touchmove',select_tm,false);
			
			canElements[eles].addEventListener('touchend',select_te,false);
		}
		console.log("selected!");
	}
	
	function importt(){
		var imp_image = new Image();
		var ff = document.getElementById("uploadimage").files[0];
		var url = window.URL || window.webkitURL;
		var src = url.createObjectURL(ff);
		imp_image.src = src;
		imp_image.onload = function(){
			ctx.drawImage(imp_image,0,0,c.width,c.height);
			url.revokeObjectURL(src);
		}
	}
	
	var imgURI;
	function print(){
		ctx.save();
		ctx.globalCompositeOperation = "destination-over";
		if(bgChanged)
		{
			pushFrame();
			ctx.drawImage(exp_background,0,0,c.width,c.height);	
			imgURI = c.toDataURL();
			unDo();//remove the background which is draw on the canvas.
		}else{
			imgURI = c.toDataURL();
		}
		ctx.restore();
		console.log(imgURI);
		var pr = window.open();
		pr.document.write("<img width=1080 height=1080 src='"+imgURI+"'/>");
        pr.print();
	}
	
	function changePaintSize(){//260*20
			var p_bar=document.getElementById("p_bar");
			var x,y;
			p_bar.addEventListener('touchstart',changePaintSize_ts,false);
			function changePaintSize_ts(e){
			e.preventDefault();
			var touchobj = e.targetTouches [0] // reference first touch point (ie: first finger)
			x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			console.log('start at point('+x+','+y+')');
			if(x>0&&x<260&&y>0&&y<20){
			paintSize=x/260;
			p_bar.value=x/2.6;
			console.log("paint size is "+paintSize);
			console.log("bar value is "+p_bar.value);
			}
		}
		
		p_bar.addEventListener('touchmove',changePaintSize_tm,false);
		function changePaintSize_tm(e){
			e.preventDefault();
			var touchobj = e.targetTouches [0] // reference first touch point for this event
			x = parseInt(touchobj.clientX) - this.getBoundingClientRect().left; // get x position of touch point relative to left edge of browser
			y = parseInt(touchobj.clientY) - this.getBoundingClientRect().top;
			if(x>0&&x<260&&y>0&&y<20){
			paintSize=x/260;
			p_bar.value=x/2.6;
			console.log(p_bar.value);
			}
		}
		
		p_bar.addEventListener('touchend',changePaintSize_te,false);
		function changePaintSize_te(e){
			e.preventDefault();
			var touchobj = e.targetTouches [0] // reference first touch point for this event
			console.log('end');
		}
	}

</script>
</body>
</html>