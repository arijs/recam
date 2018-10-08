var fs = require('fs');
var https = require('https');
var express = require('express');
var proxy = require('express-http-proxy');
// var http = require('http');
var app = express();

app.use('/', proxy('http://registrodecampo-local.com.br'));

var options = {
	key: fs.readFileSync('key.pem'),
	cert: fs.readFileSync('cert.pem'),
	passphrase: '2701'
};
// http.createServer(app).listen(80);
https.createServer(options, app).listen(443);
