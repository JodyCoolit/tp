var http = require('http');
var url = require("url");


http.createServer(function (req, res){	
			var MongoClient = require('mongodb').MongoClient;
			// Connect to the db
			MongoClient.connect("mongodb://localhost:27017/hq", function(err, db){
				if(!err){
					console.log("We are connected");
					var collection = db.collection('haha');
					//collection.insert(data,function(err,result){});
					collection.remove({character:"四"},function(e,a){
						if(!e){
							console.log('delete successful!');
						}
					});
				}
			});
}).listen(9999, '127.0.0.1');