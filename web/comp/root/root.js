(function() {

var Utils = RECAM.Utils;

RECAM.comp['root'] = {
	computed: {
		usuario: function() {
			return this.$store.state.usuario;
		}
	}
};

})();
