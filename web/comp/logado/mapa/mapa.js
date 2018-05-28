(function() {

var Utils = RECAM.Utils;

RECAM.comp['logado/mapa'] = {
	methods: {
		loadMapsApi: function() {
			var vm = this;
			var mapsApi = 'https://maps.googleapis.com/maps/api/js'+
				'?key=AIzaSyBVHAI49-HAqKFspoU9V2fe6S3W2h1b1Uw';
			Utils.loadScript(mapsApi, function() {
				vm.renderMap();
			});
		},
		detectMapsApi: function() {
			var g = window.google;
			if (g && g.maps && g.maps.Map) {
				return this.renderMap();
			} else {
				return this.loadMapsApi();
			}
		},
		renderMap: function() {
			this.map = new google.maps.Map(this.$refs.map, {
				zoom: 4,
				center: {lat: -16, lng: -55}
			});
		}
	},
	mounted: function() {
		this.detectMapsApi();
	}
};

})();
