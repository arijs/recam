(function() {

var Utils = RECAM.Utils;

RECAM.comp['logado'] = {
	computed: {
		usuario: function() {
			return this.$store.state.usuario;
		}
	},
	methods: {
		clickLogout: function() {}
	}
};

})();
