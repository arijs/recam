(function() {

var Utils = RECAM.Utils;

RECAM.comp['form/usuario/cadastrar'] = {
	computed: {
		campos: function() {
			return this.$store.state.formUsuarioCadastrar;
		},
		erro: function() {
			return this.$store.state.formUsuarioCadastrarErro;
		},
		sucesso: function() {},
		usuarioCadastrarLoading: function() {
			return this.$store.state.serviceUsuarioCadastrarLoading;
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
				context.commit('setFormUsuarioCadastrarErro', result.erroMensagem);
				if (!erro) {
					context.dispatch('loadUsuarioCadastrar').then(function() {
						var data = context.state.serviceUsuarioCadastrar;
						var error = context.state.serviceUsuarioCadastrarError;
						console.log('usuarioCadastrar', error, data);
						if (error) {
							var errorList = [];
							if (error.errorFields) {
								Utils.forEachProperty(error.errorFields, function(elist) {
									errorList = errorGroup.concat(elist || []);
								});
							}
							if (!errorList.length) {
								errorList.push(error.message || 'Erro ao fazer o cadastro. Tente novamente mais tarde.');
							}
							context.commit('setFormUsuarioCadastrarErro', errorList.join(' / '));
						}
					});
				}
			});
		}
	}
};

})();
