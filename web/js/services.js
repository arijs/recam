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
						var error = [];
						if (data && data.errorFields) {
							Utils.forEachProperty(data.errorFields, function(errorList) {
								error = error.concat(errorList);
							});
						}
						return {
							message: error.length ? error.join(' / ') : 'Login ou senha inválidos',
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
		usuarioCadastrar: function(req, callback) {
			Utils.loadService({
				req: req,
				envPrepare: Env.Services.usuarioCadastrar,
				callback: callback,
				reqValidate: function(req) {
					if (!req) {
						return {
							message: 'Dados do login não informados'
						};
					}
					var fields = {
						name: 'nome',
						email: 'e-mail',
						password: 'senha',
						password_confirm: 'confirmação da senha'
					};
					var missing = [];
					Utils.forEachProperty(fields, function(text, name) {
						if (!req[name]) missing.push(text);
					});
					if (missing.length) {
						return {
							message: (1 === missing.length
							? 'Campo não informado: '
							: 'Campos não informados: ') +
							missing.join(', ')
						};
					}
				},
				dataValidate: function(data) {
					if (!data || data.error) {
						return data || {
							message: 'Resposta vazia do servidor'
						};
					}
				}
			});
		}
	};

	RECAM.Services = services;

})();
