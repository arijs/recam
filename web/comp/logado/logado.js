(function() {

var Utils = RECAM.Utils;

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
		printModelDate: Utils.printModelDate,
		clickLogout: function() {
			this.$store.dispatch('loadLogout');
		}
	}
};

})();
