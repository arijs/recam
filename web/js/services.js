var RECAM = RECAM || {};

(function() {

	var Utils = RECAM.Utils;
	var Env = RECAM.Env;

	var services = {
		getLogin: function(req, callback) {
			Utils.loadService({
				req: req,
				envPrepare: Env.Services.getLogin,
				callback: callback,
				reqValidate: function(req) {},
				dataValidate: function(data) {
					if (!data) {
						return {
							message: 'Resposta vazia do servidor'
						};
					}
				}
			});
		},
		postLogin: function(req, callback) {
			Utils.loadService({
				req: req,
				envPrepare: Env.Services.postLogin,
				callback: callback,
				reqValidate: function(req) {
					if (!req || !req.username || !req.password) {
						return {
							message: req
								? ( !req.username
									? ( !req.password
										? 'Login e senha não informados'
										: 'Login não informado' )
									: 'Senha não informada' )
								: 'Dados do login não informados'
						};
					}
				},
				dataValidate: function(data) {
					if (!data || !data.session) {
						return {
							message: 'Login ou senha inválidos',
							error: data && data.error
						};
					}
				}
			});
		},
		logout: function(req, callback) {
			Utils.loadService({
				req: req,
				envPrepare: Env.Services.logout,
				callback: callback,
				reqValidate: function(req) {},
				dataValidate: function(data) {
					if (!data) {
						return {
							message: 'Resposta vazia do servidor'
						};
					}
				}
			});
		},
	};

	RECAM.Services = services;

})();
