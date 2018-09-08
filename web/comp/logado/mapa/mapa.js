(function() {

var Utils = RECAM.Utils;
var hop = Object.prototype.hasOwnProperty;

RECAM.comp['logado/mapa'] = {
	data: function() {
		return {
			loadingDB: false,
			loadingJW: false,
			lastQuery: null,
			meetingLocations: {},
			selectedMeeting: null,
			lastStats: null,
			loadStats: []
		};
	},
	computed: {
		printStats: function() {
			return Utils.forEach(this.loadStats, [], function(stats) {
				this.result.unshift(JSON.stringify(stats));
			}).join('\n')
		}
	},
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
		getMapBounds: function() {
			var bounds = this.map.getBounds();
			var sw = bounds.getSouthWest();
			var ne = bounds.getNorthEast();
			var query = {
				lowerLatitude: sw.lat(), //-23.71636&
				lowerLongitude: sw.lng(), //-46.74324&
				// searchLanguageCode: 'T', //&
				upperLatitude: ne.lat(), //-23.64754&
				upperLongitude: ne.lng() //-46.66874
			};
			return query;
		},
		renderMap: function() {
			var vm = this;
			var map = new google.maps.Map(this.$refs.map, {
				zoom: 4,
				center: {lat: -16, lng: -55}
			});
			this.map = map;
			map.addListener('bounds_changed', function() {
				// vm.lastQuery = vm.getMapBounds();
				vm.loadLocationsDBDebounce();
			});
		},
		addMarker: function(ml) {
			var vm = this;
			this.meetingLocations[ml.geoId] = ml;
			var marker = this.markers[ml.geoId];
			if (marker) {
				marker.setMap(null);
			}
			marker = new google.maps.Marker({
				position: {
					lat: ml.location.latitude,
					lng: ml.location.longitude
				},
				map: this.map,
				title: ml.properties.orgName
			});
			marker.addListener('click', function() {
				vm.selectedMeeting = vm.selectedMeeting === ml ? null : ml;
			});
			this.markers[ml.geoId] = marker;
			var stat = vm.lastStats;
			stat.total++;
			var meta = ml['-rdc-meta'];
			var action = meta && meta.action;
			if (action) {
				if ('number' === typeof stat[action]) {
					stat[action]++;
				} else {
					stat[action] = 1;
				}
			}
		},
		loadLocationsDB: function() {
			var vm = this;
			if (vm.loadingDB) return;
			vm.loadingDB = true;
			var query = Utils.stringifyQuery(vm.getMapBounds());
			Utils.loadAjax({
				url: '/api/meeting-locations-db?'+query,
				cb: function(err, data, xhr) {
					vm.loadingDB = false;
					if (err) {
						console.error(err, data, xhr);
						return;
					}
					console.log(data, query);
					vm.lastStats = {src:'db', total:0};
					Utils.forEach(data.geoLocationList, function(ml) {
						vm.addMarker(ml);
					});
					vm.loadStats.push(vm.lastStats);
				}
			});
		},
		loadLocationsJW: function() {
			var vm = this;
			if (vm.loadingJW) return;
			vm.loadingJW = true;
			var query = Utils.stringifyQuery(vm.getMapBounds());
			Utils.loadAjax({
				url: '/api/meeting-locations?'+query,
				cb: function(err, data, xhr) {
					vm.loadingJW = false;
					if (err) {
						console.error(err, data, xhr);
						return;
					}
					console.log(data, query);
					vm.lastStats = {src:'jw', total:0};
					Utils.forEach(data.geoLocationList, function(ml) {
						vm.addMarker(ml);
					});
					vm.loadStats.push(vm.lastStats);
				}
			});
		}
	},
	mounted: function() {
		this.detectMapsApi();
	},
	created: function() {
		var vm = this;
		this.markers = {};
		this.loadLocationsDBDebounce = Utils.debounce(function() {
			if (vm.loadingDB) {
				// vm.loadLocationsDBDebounce();
				return;
			}
			vm.loadLocationsDB();
		}, 3000);
		this.loadLocationsJWDebounce = Utils.debounce(function() {
			if (vm.loadingJW) {
				// vm.loadLocationsJWDebounce();
				return;
			}
			vm.loadLocationsJW();
		}, 10000);
	}
};

})();
