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
	data() {
		return {
			// id: '',
			header: '',
			_active: false
		};
	},
	computed: {
		active: {
			get() { return this.$data._active; },
			set(val) {
				this.$data._active = val;
				//if ( val ) {
					// this.onSelected();
				//} // end if
				//console.log('tab-panel:computed-active', val);
				this.$emit('tab-activate', !!val);
			}
		},
		show () {
			return this.$data._active;
		},
		transition () {
			return this.$parent.transition;
		}
	},
	created() {
		this.$on('is-active?', function() {
			//console.log('tab-panel:is-active');
			this.active = this.$data._active;
		});
	},
	beforeMount() {
		this.$parent.registerTab(this);
	},
	beforeDestroy() {
		this.$parent.removeTab(this);
	},
	mounted() {
		// Support Header element
		var headerElem = this.$slots.header;
		if ( headerElem ) {
			this.header = headerElem;
		} // end if
	}
};

})();
