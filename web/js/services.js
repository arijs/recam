var RECAM = RECAM || {};

(function() {

	var Utils = RECAM.Utils;
	var Env = RECAM.Env;

	function validateRede(key, name, val) {
		if (!this.redesMap[key]) {
			this.invalid.push(name);
			return false;
		}
		if (val) {
			if (!val.id || !val['id_'+key]) {
				this.invalid.push(name);
				return false;
			}
			this.redesAtivas.push(key);
		}
		return true;
	}
	function validateNaoVazio(key, name, val) {
		if (!val) {
			this.missing.push(name);
			return false;
		}
		return true;
	}
	function opcionalSeLoginRede(key, name, val) {
		if (!val && this.redesAtivas.length == 0) {
			this.missing.push(name);
			return false;
		}
		return true;
	}

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
					var redes = ['facebook', 'google', 'twitter', 'linkedin'];
					var ctx = {
						req: req,
						missing: [],
						invalid: [],
						redesMap: Utils.forEach(redes, {}, function(name) {
							this.result[name] = true;
						}),
						redesAtivas: []
					};
					var fields = [
						{key: 'facebook', name: 'login Facebook', val: validateRede},
						{key: 'google', name: 'login Google', val: validateRede},
						{key: 'twitter', name: 'login Twitter', val: validateRede},
						{key: 'linkedin', name: 'login Linkedin', val: validateRede},
						{key: 'name', name: 'nome', val: validateNaoVazio},
						{key: 'email', name: 'e-mail', val: validateNaoVazio},
						{key: 'password', name: 'senha', val: opcionalSeLoginRede},
						{key: 'password_confirm', name: 'confirmação da senha', val: opcionalSeLoginRede}
					];

					Utils.forEach(fields, function(f) {
						f.val.call(ctx, f.key, f.name, req[f.key]);
					});
					var invalid = ctx.invalid;
					var missing = ctx.missing;
					if (invalid.length) {
						return {
							message: (1 === invalid.length
							? 'Campo inválido: '
							: 'Campos inválidos: ') +
							invalid.join(', ')
						};
					}
					if (ctx.missing.length) {
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
