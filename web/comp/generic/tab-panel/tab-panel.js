(function() {

var Utils = RECAM.Utils;

RECAM.comp['generic/tab-panel'] = {
	props: {
		/*header: {
			type: String
		},*/
		panelClass: {
			type: String,
			default: 'tab-pane'
		},
		activeClass: {
			type: String,
			default: 'active'
		},
		disabled: {
			type: Boolean,
			default: false
		}/*,
		onSelected: {
			type: Function,
			default() {}
		}*/
	},
	data: function() {
		return {
			// id: '',
			header: '',
			_active: false
		};
	},
	computed: {
		active: {
			get: function() { return this.$data._active; },
			set: function(val) {
				this.$data._active = val;
				//if ( val ) {
					// this.onSelected();
				//} // end if
				//console.log('tab-panel:computed-active', val);
				this.$emit('tab-activate', !!val);
			}
		},
		show: function() {
			return this.$data._active;
		},
		transition: function() {
			return this.$parent.transition;
		}
	},
	created: function() {
		this.$on('is-active?', function() {
			//console.log('tab-panel:is-active');
			this.active = this.$data._active;
		});
	},
	beforeMount: function() {
		this.$parent.registerTab(this);
	},
	beforeDestroy: function() {
		this.$parent.removeTab(this);
	},
	mounted: function() {
		// Support Header element
		var headerElem = this.$slots.header;
		if ( headerElem ) {
			this.header = headerElem;
		} // end if
	}
};

})();
