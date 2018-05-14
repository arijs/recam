(function() {

var Utils = RECAM.Utils;

RECAM.comp['generic/tab-control'] = {
	props: {
		orientation: {
			type: String,
			default: 'top'
		},
		transition: {
			type: String,
			default: 'fadein'
		},
		classControl: {
			type: String,
			default: 'card card-default'
		},
		classHeader: {
			type: String,
			default: 'card-header'
		},
		classHeaderList: {
			type: String,
			default: 'nav nav-tabs card-header-tabs'
		},
		classHeaderItem: {
			type: String,
			default: 'nav-item'
		},
		classHeaderLink: {
			type: String,
			default: 'nav-link'
		},
		classHeaderLinkActive: {
			type: String,
			default: 'active'
		},
		classHeaderLinkDisabled: {
			type: String,
			default: 'disabled'
		},
		classBody: {
			type: String,
			default: 'card-body tab-content'
		}
	},
	data: function() {
		return {
			tabs: []
		};
	},
	methods: {
		orientationClass: function() {
			return 'tabs-' + this.orientation;
		},
		activateTab: function(index) {
			var tab = this.tabs[index];

			if ( tab && !tab.disabled ) {
				if ( index == 'first' ) {
					index = 0;
				} else if ( index == 'last' ) {
					index = this.tabs.length - 1;
				} // end if

				this.tabs.forEach(function(tab, idx) {
					tab.active = idx === index;
				});
			} // end if
		},
		ensureActiveTab: function() {
			var activeTab = 0;
			this.tabs.forEach(function(tab, index) {
				if ( tab.active ) {
					activeTab = index;
				} // end if
			});
			this.activateTab(activeTab);
		},
		registerTab: function(tab) {
			tab.id = this.tabs.length;
			this.tabs.push(tab);
			this.ensureActiveTab();
		},
		removeTab: function(tab) {
			if(this && this.tabs && this.tabs.$remove)
				this.tabs.$remove(tab);
			this.ensureActiveTab();
		}
	}
};

})();
