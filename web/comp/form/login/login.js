(function() {

var Utils = RECAM.Utils;

RECAM.comp['form/login'] = {
	computed: {
		campos: function() {
			return this.$store.state.formLogin;
		},
		erro: function() {
			return this.$store.state.formLoginErro;
		},
		postLoginLoading: function() {
			return this.$store.state.servicePostLoginLoading;
		}
	},
	methods: {
		validarCampo: function(campo, callback) {
			this.$store.dispatch('validarCampo', campo).then(callback);
		},
		campoFocus: function(evt) {
			this.$emit('campoFocus', {
				target: evt.target,
				wrap: this.$refs.wrap
			});
			evt.preventDefault();
		},
		campoBlur: function(campo) {
			this.validarCampo(campo, function(s) {
				//console.log(s);
			});
		},
		clickSubmit: function() {
			var context = this.$store;
			context.dispatch('validarForm', this.campos).then(function(result) {
				var erro = result.erroMensagem;
				context.commit('setFormLoginErro', erro);
				if (!erro) {
					context.dispatch('loadPostLogin').then(function() {
						var data = context.state.servicePostLogin;
						var error = context.state.servicePostLoginError;
						console.log('postLogin', error, data);
						if (error) {
							var errorList = [];
							if (error.errorFields) {
								Utils.forEachProperty(error.errorFields, function(elist) {
									errorList = errorGroup.concat(elist || []);
								});
							}
							if (!errorList.length) {
								errorList.push(error.message || 'Erro ao fazer o login. Tente novamente mais tarde.');
							}
							context.commit('setFormLoginErro', errorList.join(' / '));
						}
					});
				}
			});
		}
	}
};

})();
