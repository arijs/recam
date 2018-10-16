(function() {

var Utils = RECAM.Utils;

RECAM.comp['logado/menu'] = {
	data: function() {
		return {
			menuOpen: false
		};
	},
	methods: {
		toggleMenu: function() {
			this.menuOpen = !this.menuOpen;
		}
	}
};

})();
