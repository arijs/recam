(function() {

var Utils = RECAM.Utils;

RECAM.comp['root'] = {
	computed: {
		getLoginLoading: function() {
			return this.$store.state.serviceGetLoginLoading;
		},
		session: function() {
			return this.$store.state.session;
		}
	}
};

})();
