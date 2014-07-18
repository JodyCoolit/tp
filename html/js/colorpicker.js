      function getMousePos(canvas, e) {
        e.preventDefault();
		var touchobj = e.targetTouches[0] // reference first touch point for this event
		return {
		x : parseInt(touchobj.clientX) - canvas.getBoundingClientRect().left, // get x position of touch point relative to left edge of browser
		y : parseInt(touchobj.clientY) - canvas.getBoundingClientRect().top
		}
      }
      function drawColorSquare(canvas, color, imageObj) {
        var colorSquareSize = 128;
        var padding = 0;
        var context = canvas.getContext('2d');
        var squareX = 130;//(canvas.width - colorSquareSize + imageObj.width) / 2;
        var squareY = 0;//(canvas.height - colorSquareSize) / 2;
        context.beginPath();
        context.fillStyle = color;
        context.fillRect(squareX, squareY, colorSquareSize, colorSquareSize);
        //context.strokeRect(squareX, squareY, colorSquareSize, colorSquareSize);
      }
      function init(imageObj) {
        var padding = 0;
        var canvas = document.getElementById('mc2');
        var context = canvas.getContext('2d');
        var mouseDown = false;//mouseDown=touchstart
        context.strokeStyle = '#444';
        context.lineWidth = 2;
        canvas.addEventListener('touchstart', function(evt) {
          mouseDown = true;
		  var mousePos = getMousePos(canvas, evt);
          var color = undefined;

          if(mouseDown && mousePos !== null && mousePos.x > padding && mousePos.x < padding + imageObj.width && mousePos.y > padding && mousePos.y < padding + imageObj.height) {

            // color picker image is 256x256 and is offset by 10px, 128*128 for now
            // from top and bottom
            var imageData = context.getImageData(padding, padding, imageObj.width, imageObj.width);
            var data = imageData.data;
            var x = mousePos.x - padding;
            var y = mousePos.y - padding;
            var red = data[((imageObj.width * y) + x) * 4];
            var green = data[((imageObj.width * y) + x) * 4 + 1];
            var blue = data[((imageObj.width * y) + x) * 4 + 2];
            var color = 'rgb(' + red + ',' + green + ',' + blue + ')';
			rgb_red = red;
			rgb_green = green;
			rgb_blue = blue;
            drawColorSquare(canvas, color, imageObj);
			paintColor=color;
          }
        }, false);

        canvas.addEventListener('touchend', function() {
          mouseDown = false;
        }, false);

        canvas.addEventListener('touchmove', function(evt) {
          var mousePos = getMousePos(canvas, evt);
          var color = undefined;

          if(mouseDown && mousePos !== null && mousePos.x > padding && mousePos.x < padding + imageObj.width && mousePos.y > padding && mousePos.y < padding + imageObj.height) {

            // color picker image is 256x256 and is offset by 10px
            // from top and bottom
            var imageData = context.getImageData(padding, padding, imageObj.width, imageObj.width);
            var data = imageData.data;
            var x = mousePos.x - padding;
            var y = mousePos.y - padding;
            var red = data[((imageObj.width * y) + x) * 4];
            var green = data[((imageObj.width * y) + x) * 4 + 1];
            var blue = data[((imageObj.width * y) + x) * 4 + 2];
            var color = 'rgb(' + red + ',' + green + ',' + blue + ')';
			rgb_red = red;
			rgb_green = green;
			rgb_blue = blue;
            drawColorSquare(canvas, color, imageObj);
			console.log(color);
			console.log(x+"   "+y);
			paintColor=color;
          }
        }, false);
        context.drawImage(imageObj, padding, padding);
        drawColorSquare(canvas, 'white', imageObj);
      }
		var imageObj = new Image();
		imageObj.src = "image/color_picker.jpeg";
		imageObj.onload = function() {
		init(this);
		};