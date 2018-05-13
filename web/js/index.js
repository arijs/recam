
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

Vue.options.componentDynamic('recam--root')(
	function(compRoot) {
		compRoot.store = RECAM.store;
		var CompRoot = Vue.component('recam--root', compRoot);
		var root = new CompRoot();
		root.$mount('#recam-mount');
		RECAM.$root = root;
	},
	function(err) {
		new Vue({
			el: '#recam-mount',
			template: '<div class="recam--component-error">'
				+ Utils.htmlEntitiesEncode(String(err))
				+ '</div>'
		});
	}
);

RECAM.store.commit('setScreen', 1);

})();
