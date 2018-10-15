(function() {

var Utils = RECAM.Utils;
var hop = Object.prototype.hasOwnProperty;
var INFOSALAO_LOADING = {};
var INFOSALAO_ERROR = {};

RECAM.comp['logado/mapa'] = {
	data: function() {
		return {
			loadingDB: false,
			loadingJW: false,
			lastQuery: null,
			meetingLocations: {},
			selectedSalao: null,
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
		selectMeetingLocation: function(ml) {
			console.log(ml);
			var vm = this;
			vm.refInfoSalao.updateRequest(true, null, null);
			this.$store.dispatch('loadUsuarioLocalReuniao', {
				usuario_id: vm.$store.state.session.usuario.usuario_id,
				reuniao_id: ml['-rdc-meta'].id,
				geo_id: ml.geoId
			}).then(function() {
				vm.refInfoSalao.updateRequest(
					false,
					vm.$store.state.serviceUsuarioLocalReuniaoError,
					vm.$store.state.serviceUsuarioLocalReuniao
				);
				// console.log(
				// 	vm.$store.state.serviceUsuarioLocalReuniaoError,
				// 	vm.$store.state.serviceUsuarioLocalReuniao
				// );
			})
		},
		findSalaoNearMeetingLocation: function(ml) {
			var maxDistance = 1/1024;
			var mlLat = ml.location.latitude;
			var mlLng = ml.location.longitude;
			var near;
			Utils.forEachProperty(this.saloes, function(s) {
				Utils.forEach(s.meetingLocations, function(sml) {
					var dlat = mlLat - sml.location.latitude;
					var dlng = mlLng - sml.location.longitude;
					if (
						dlat < maxDistance &&
						dlat > -maxDistance &&
						dlng < maxDistance &&
						dlng > -maxDistance
					) {
						near = {
							salao: s,
							ml: sml,
							dlat: dlat,
							dlng: dlng
						};
						return this._break;
					}
				});
			});
			return near;
		},
		addMarker: function(ml) {
			var vm = this;
			this.meetingLocations[ml.geoId] = ml;
			var salao = this.saloes[ml.geoId];
			if (!salao) {
				var near = this.findSalaoNearMeetingLocation(ml);
				if (near) {
					salao = near.salao;
					salao.meetingLocations.push(ml);
					this.saloesMLAdded[salao.first.geoId] = salao;
				} else {
					salao = {
						first: ml,
						marker: new google.maps.Marker({
							position: {
								lat: ml.location.latitude,
								lng: ml.location.longitude
							},
							map: this.map,
							title: ml.properties.address
						}),
						meetingLocations: [ml]
					};
					this.saloesFirst[ml.geoId] = salao;
					salao.marker.addListener('click', function() {
						vm.selectedSalao = salao;
						if (vm.openInfo) {
							vm.openInfo.close();
							vm.openInfo = null;
						}
						if (vm.refInfoSalao) {
							vm.refInfoSalao.$destroy();
							vm.refInfoSalao = null;
						}
						vm.refInfoSalao = new vm.InfoSalao({
							propsData: {
								salao: salao
							},
							el: (function() {
								var parent = document.createElement('div');
								var el = document.createElement('div');
								parent.appendChild(el);
								return el;
							})()
						});
						vm.refInfoSalao.$on('select', vm.selectMeetingLocation);
						vm.openInfo = new google.maps.InfoWindow({
							content: vm.refInfoSalao.$el
						});
						vm.openInfo.open(vm.map, salao.marker);
						// });
					});
					// this.markers[ml.geoId] = salao.marker;
				}
				this.saloes[ml.geoId] = salao;
			}
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
					vm.saloesMLAdded = {};
					Utils.forEach(data.geoLocationList, function(ml) {
						vm.addMarker(ml);
					});
					console.log(vm.saloesMLAdded);
					vm.saloesMLAdded = null;
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
					vm.saloesMLAdded = {};
					Utils.forEach(data.geoLocationList, function(ml) {
						vm.addMarker(ml);
					});
					console.log(vm.saloesMLAdded);
					vm.saloesMLAdded = null;
					vm.loadStats.push(vm.lastStats);
				}
			});
		},
		loadInfoComponent: function() {
			var vm = this;
			vm.InfoSalao = INFOSALAO_LOADING;
			var compName = 'recam--logado--mapa--info-salao';
			Vue.options.componentDynamic(compName)(
				function(infoSalao) {
					infoSalao.store = RECAM.store;
					vm.InfoSalao = Vue.component(compName, infoSalao);
				},
				function(err) {
					vm.InfoSalao = INFOSALAO_ERROR;
					var strObject = String({});
					err = (err.message || err.error || err);
					var strErr = String(err);
					if (strErr === strObject) {
						strErr = JSON.stringify(err);
					}
					alert(strErr);
				}
			);
		}
	},
	mounted: function() {
		this.detectMapsApi();
	},
	created: function() {
		var vm = this;
		this.saloes = {};
		this.openInfo = null;
		this.refInfoSalao = null;
		this.saloesFirst = {};
		this.saloesMLAdded = null;
		this.loadLocationsDBDebounce = Utils.debounce(function() {
			if (vm.loadingDB) {
				// vm.loadLocationsDBDebounce();
				return;
			}
			vm.loadLocationsDB();
		}, 4000);
		this.loadLocationsJWDebounce = Utils.debounce(function() {
			if (vm.loadingJW) {
				// vm.loadLocationsJWDebounce();
				return;
			}
			vm.loadLocationsJW();
		}, 15000);
		this.loadInfoComponent();
	}
};

})();
