
var RECAM = RECAM || {};

(function() {

var Utils = RECAM.Utils;
RECAM.comp = {};

var compFactory = {};

Vue.mixin({
	componentDynamic: function(id) {
		//console.log('Component Dynamic: '+id);
		var factory = compFactory[id];
		if (factory) {
			return factory;
		}
		var prefix = 'recam--';
		var plen = prefix.length;
		if (id.substr(0, plen).toLowerCase() === prefix) {
			var name = id.substr(plen).replace(/--/g,'/');
			var last = name.lastIndexOf('/');
			last = name.substr(last+1);
			var href = (RECAM.BaseUrl || '') + '/comp/'+name+'/'+last;
			factory = Utils.componentDynamic(name, href, RECAM.comp);
			compFactory[id] = factory;
			return factory;
		}
	}
});

Vue.component('masked-input', vueTextMask.default);

Vue.component('vnode', {
	functional: true,
	render: function(h, context){
		return context.props.node;
	}
});

var strObject = String({});

Vue.options.componentDynamic('recam--root')(
	function(compRoot) {
		compRoot.store = RECAM.store;
		var CompRoot = Vue.component('recam--root', compRoot);
		var root = new CompRoot();
		root.$mount('#recam-mount');
		RECAM.$root = root;
	},
	function(err) {
		var CompError = Vue.extend({
			template: '<div class="recam--component-error">'
				+ '<pre v-text="errorText"></pre>'
				+ '</div>',
			data: function() {
				return { error: err };
			},
			computed: {
				errorText: function() {
					var err = this.error;
					if (err && err.error && err.error.stack) {
						err.error = err.error.stack;
					}
					if (String(err) === strObject) {
						err = JSON.stringify(err, null, 2);
					}
					return err;
				}
			}
		});
		var root = new CompError();
		root.$mount('#recam-mount');
		RECAM.$rootError = root;
	}
);

RECAM.store.dispatch('loadGetLogin');
// RECAM.store.commit('setScreen', 1);

})();
