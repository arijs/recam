var fs = require('fs');
var https = require('https');
var express = require('express');
var proxy = require('express-http-proxy');
// var http = require('http');

/*
var dns = require('dns');
var start = Date.now();

var iv = setInterval(function() {
	console.log(Date.now()-start);
}, 990)

dns.lookup('registrodecampo-local.com.br', function(err, addr, fam) {
	clearInterval(iv);
	console.log(err, addr, fam);
});

return;
/*/

var app = express();

app.use('/', proxy('registrodecampo-local.com.br'));
// app.use('/', proxy('http://127.0.0.1'));

var options = {
	key: fs.readFileSync('key.pem'),
	cert: fs.readFileSync('cert.pem'),
	passphrase: '2701'
};
// http.createServer(app).listen(80);
https.createServer(options, app).listen(443);
//*/
