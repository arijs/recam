
var RC = RC || {};

(function() {

var Utils = RC.Utils;
RC.comp = {};

var compFactory = {};

Vue.mixin({
	componentDynamic: function(id) {
		//console.log('Component Dynamic: '+id);
		var factory = compFactory[id];
		if (factory) {
			return factory;
		}
		var prefix = 'rc--';
		var plen = prefix.length;
		if (id.substr(0, plen).toLowerCase() === prefix) {
			var name = id.substr(plen).replace(/--/g,'/');
			var last = name.lastIndexOf('/');
			last = name.substr(last+1);
			var href = (RC.BaseUrl || '') + '/comp/'+name+'/'+last;
			factory = Utils.componentDynamic(name, href, RC.comp);
			compFactory[id] = factory;
			return factory;
		}
	}
});

Vue.component('masked-input', vueTextMask.default);

Vue.options.componentDynamic('rc--root')(
	function(compRoot) {
		compRoot.store = RC.store;
		var CompRoot = Vue.component('rc--root', compRoot);
		var root = new CompRoot();
		root.$mount('#rc-mount');
		RC.$root = root;
	},
	function(err) {
		new Vue({
			el: '#rc-mount',
			template: '<div class="rc--component-error">'
				+ Utils.htmlEntitiesEncode(String(err))
				+ '</div>'
		});
	}
);

RC.store.commit('setScreen', 1);

})();
