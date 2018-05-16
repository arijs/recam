var RECAM = RECAM || {};

(function() {

	var env = {};
	var services = {};
	var Utils = RECAM.Utils;

	env.name = 'local';
	env.Services = services;
	RECAM.Env = env;

	services.getLogin = function(req, cb) {
		return {
			url: '/api/login',
			cb: cb
		};
	};

	services.postLogin = function(req, cb) {
		return {
			method: 'POST',
			url: '/api/login',
			headers: [
				{ name: 'Content-Type', value: 'application/x-www-form-urlencoded; charset=UTF-8' }
			],
			body: Utils.stringifyQuery(req),
			cb: cb
		};
	};

	services.logout = function(req, cb) {
		return {
			url: '/api/logout',
			cb: cb
		};
	};

})();
