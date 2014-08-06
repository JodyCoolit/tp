var http = require('http');
var url = require("url");

var data;
http.createServer(function (req, res){
	var url_parts = url.parse(req.url);
	console.log(url_parts);
    switch(url_parts.pathname){
    case '/retrieve':
		console.log(url_parts.pathname+'() is called ');
		retrieve(req,res);
	break;
    case '/insert':
		console.log(url_parts.pathname+'() is called ');
		insert(req,res);
	break;
    default:
		console.log('default() is called ');
		retrieve(req,res);
    }
    return;
	
	function insert(req,res){
		var body = '';
		// we want to get the data as utf8 strings
		// If you don't set an encoding, then you'll get Buffer objects
		req.setEncoding('utf8');

		// Readable streams emit 'data' events once a listener is added
		req.on('data', function (chunk) {
		body += chunk;
		});

		// the end event tells you that you have entire body
		req.on('end', function () {
			try {
			  data = JSON.parse(body);
			} catch (er) {
			  res.statusCode = 400;
			  return res.end('error: ' + er.message);
			}
			res.setHeader('Access-Control-Allow-Origin', '*');
			//res.setHeader("Content-Type", "application/json");
			
			var MongoClient = require('mongodb').MongoClient;
			// Connect to the db
			MongoClient.connect("mongodb://localhost:27017/hq", function(err, db){
				if(!err){
					console.log("We are connected");
					var collection = db.collection('haha');
					collection.insert(data,function(err,result){});
					res.end();
					/* var cursor = collection.find();
					cursor.each(function(err, item){
						if (item){
							console.log(JSON.stringify(item));
							res.write(JSON.stringify(item)+'\n');
						}else{
							res.end();
						}
					}); */
				}else{
					console.log('sorry, fail to connect');
				}
			});	
		});
	}
	
	function retrieve(req,res){
		var body = '';
		// we want to get the data as utf8 strings
		// If you don't set an encoding, then you'll get Buffer objects
		req.setEncoding('utf8');
		// Readable streams emit 'data' events once a listener is added
		req.on('data', function (chunk) {
		body += chunk;
		});
		req.on('end', function () {
			res.setHeader('Access-Control-Allow-Origin', '*');
			//res.setHeader("Content-Type", "application/json");
			
			var MongoClient = require('mongodb').MongoClient;
			// Connect to the db
			MongoClient.connect("mongodb://localhost:27017/hq", function(err, db){
				if(!err){
					console.log("We are connected");
					var collection = db.collection('haha');
					//collection.insert(data,function(err,result){});
					var cursor = collection.find();
					cursor.each(function(err, item){
						if (item){
							console.log(JSON.stringify(item));
							res.write(JSON.stringify(item)+'\n');
						}else{
							res.end();
						}
					});
				}else{
					console.log('sorry, fail to connect');
				}
			});	
		});
	}
	
}).listen(8888, '192.168.1.157');// 172.20.34.102