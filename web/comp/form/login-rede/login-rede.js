(function() {

var Utils = RECAM.Utils;

RECAM.comp['form/login-rede'] = {
	computed: {
		facebook: function() {
			var login = this.$store.state.serviceGetLogin;
			return login && login.facebook;
		},
		google: function() {
			var login = this.$store.state.serviceGetLogin;
			return login && login.google;
		},
		twitter: function() {
			var login = this.$store.state.serviceGetLogin;
			return login && login.twitter;
		},
		linkedin: function() {
			var login = this.$store.state.serviceGetLogin;
			return login && login.linkedin;
		}
	}
};

})();
