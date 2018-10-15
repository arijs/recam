(function() {

var Utils = RECAM.Utils;

RECAM.comp['logado/mapa/info-salao'] = {
	props: {
		salao: {
			type: Object
		},
		saveLoading: {
			type: Boolean,
			required: true
		},
		saveError: {
			type: Object
		},
		saveData: {
			type: Object
		}
	},
	data: function() {
		return {
			select: null
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
		}
	}
};

})();
