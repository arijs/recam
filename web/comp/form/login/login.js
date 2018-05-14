(function() {

var Utils = RECAM.Utils;

RECAM.comp['form/login'] = {
	computed: {
		campos: function() {
			return this.$store.state.formLogin;
		},
		erro: function() {
			return this.$store.state.formLoginErro;
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
				context.commit('setFormLoginErro', result.erroMensagem);
			});
		}
	}
};

})();
