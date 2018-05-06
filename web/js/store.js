var RC = RC || {};

(function() {

var Utils = RC.Utils;
var mask = Utils.mask;
var valida = Utils.valida;
var services = RC.Services;
var hasOwnProperty = Object.prototype.hasOwnProperty;
var query = Utils.parseQuery(location.search);

var state = {
	baseUrl: (RC.BaseUrl || ''),
	query: query,
	pageScroll: [0, 0],
	screen: null
};
var getters = {};
var actions = {};
var mutations = {
	setPageScroll: function(state, ps) {
		var sps = state.pageScroll;
		if (ps[0] != null && !isNaN(+ps[0])) sps[0] = ps[0];
		if (ps[1] != null && !isNaN(+ps[1])) sps[1] = ps[1];
	},
	setScreen: function(state, screen) {
		state.screen = screen;
	},
	setFormCampoValue: function(state, payload) {
		payload.campo.valor = payload.value;
	},
	setFormCampoSelecionado: function(state, payload) {
		payload.campo.selecionado = payload.selecionado;
	},
	setFormCampoOpcoes: function(state, payload) {
		payload.campo.opcoes = payload.opcoes;
	},
	setFormCampoErro: function(state, payload) {
		var campo = payload.campo;
		var v = payload.validacao;
		campo.falta = v && v.falta || false;
		campo.erro = v && v.erro || null;
	}
};

var store = new Vuex.Store({
	state: state,
	getters: getters,
	actions: actions,
	mutations: mutations
});

RC.store = store;

})();
