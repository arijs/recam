(function() {

var Utils = RECAM.Utils;
var reDate = /(\d+)-(\d+)-(\d+)/;

RECAM.comp['logado'] = {
	computed: {
		logoutLoading: function() {
			return this.$store.state.serviceLogoutLoading;
		},
		session: function() {
			return this.$store.state.session;
		},
		usuario: function() {
			return this.session.usuario;
		},
		acesso: function() {
			return this.session.acesso;
		}
	},
	methods: {
		printModelDate: function(dt) {
			var m = String(dt||'').match(reDate);
			if (m) {
				return [
					Utils.padStart(m[3],2,'0'),
					Utils.padStart(m[2],2,'0'),
					m[1]
				].join('/');
			}
		},
		clickLogout: function() {
			this.$store.dispatch('loadLogout');
		}
	}
};

})();
