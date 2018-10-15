(function() {

var Utils = RECAM.Utils;

RECAM.comp['logado/mapa/info-salao'] = {
	props: {
		salao: {
			type: Object
		}
	},
	data: function() {
		return {
			select: null,
			loading: false,
			error: null,
			data: null
		};
	},
	methods: {
		selectMeetingLocation: function(ml) {
			// this.$emit('select', ml);
			this.select = ml;
		},
		cancelSelection: function() {
			this.select = null;
		},
		confirmSelection: function() {
			this.$emit('select', this.select);
		},
		updateRequest: function(loading, error, data) {
			this.loading = loading;
			this.error = error;
			this.data = data;
		}
	}
};

})();
