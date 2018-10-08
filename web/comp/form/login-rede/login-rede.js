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
		},
		sessionFacebook: function() {
			var session = this.$store.state.session;
			return session && session.facebook;
		},
		sessionGoogle: function() {
			var session = this.$store.state.session;
			return session && session.google;
		},
		sessionTwitter: function() {
			var session = this.$store.state.session;
			return session && session.twitter;
		},
		sessionLinkedin: function() {
			var session = this.$store.state.session;
			return session && session.linkedin;
		}
	}
};

})();
