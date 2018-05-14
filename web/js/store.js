var RECAM = RECAM || {};

(function() {

var Utils = RECAM.Utils;
var mask = Utils.mask;
var valida = Utils.valida;
var services = RECAM.Services;
var hasOwnProperty = Object.prototype.hasOwnProperty;
var query = Utils.parseQuery(location.search);

var state = {
	baseUrl: (RECAM.BaseUrl || ''),
	query: query,
	pageScroll: [0, 0],
	screen: null,
	usuario: null,
	formLogin: {
		login: {
			nome: 'login',
			rotulo: 'E-mail',
			tipo: 'email',
			valor: '',
			erro: null,
			falta: false,
			valida: [
				valida.naoVazio,
				valida.email,
			]
		},
		senha: {
			nome: 'senha',
			rotulo: 'Senha',
			tipo: 'password',
			valor: '',
			erro: null,
			falta: false,
			valida: [
				valida.naoVazio
			]
		}
	},
	formLoginErro: null,
	formUsuarioCadastrar: {
		nome: {
			nome: 'nome',
			rotulo: 'Nome',
			tipo: 'text',
			valor: '',
			erro: null,
			falta: false,
			valida: [
				valida.naoVazio
			]
		},
		email: {
			nome: 'email',
			rotulo: 'E-mail',
			tipo: 'email',
			valor: '',
			erro: null,
			falta: false,
			valida: [
				valida.naoVazio,
				valida.email,
			]
		},
		senha: {
			nome: 'senha',
			rotulo: 'Senha',
			tipo: 'password',
			valor: '',
			erro: null,
			falta: false,
			valida: [
				valida.naoVazio
			]
		},
		senhaConfirmacao: {
			nome: 'senha-confirmacao',
			rotulo: 'Confirme a senha',
			tipo: 'password',
			valor: '',
			erro: null,
			falta: false,
			valida: [
				valida.naoVazio
			]
		}
	},
	formUsuarioCadastrarErro: null
};
var getters = {};
var actions = {
	testaCampo: function(context, campo) {
		var validacao = null;
		if (campo.valida) {
			Utils.forEach(campo.valida, function(fn) {
				validacao = fn(campo, context);
				if (validacao) return this._break;
			});
		}
		return Promise.resolve({
			campo: campo,
			validacao: validacao
		});
	},
	validarCampo: function(context, campo) {
		return new Promise(function(resolve, reject) {
			context.dispatch('testaCampo', campo).then(function(item) {
				context.commit('setFormCampoErro', item);
				resolve(item);
			});
		});
	},
	testaFormGrupo: function(context, grupo) {
		var camposPromise = [];
		Utils.forEachProperty(g, function(campo) {
			camposPromise.push(context.dispatch('testaCampo', campo));
		});
		return Promise.all(camposPromise);
	},
	testaForm: function(context, form) {
		// var grupos = context.state.formGrupos;
		var camposPromise = [];
		// Utils.forEachProperty(grupos, function(g, gkey) {
			Utils.forEachProperty(form, function(campo) {
				camposPromise.push(context.dispatch('testaCampo', campo));
			});
		// });
		return Promise.all(camposPromise);
	},
	validarForm: function(context, form) {
		return context.dispatch('testaForm', form).then(function(lista) {
			var result = {
				erroMensagem: null,
				lista: lista,
				erros: 0,
				faltas: 0
			};
			Utils.forEach(lista, function(item) {
				context.commit('setFormCampoErro', item);
				var v = item.validacao;
				if (!v) return;
				if (v.falta) result.faltas++;
				if (v.erro) result.erros++;
			});
			if (result.erros) {
				result.erroMensagem = 'Um ou mais campos possuem dados inválidos';
			} else if (result.faltas) {
				result.erroMensagem = 'Você precisa preencher todas as informações';
			}
			return result;
		});
	}
};
var mutations = {
	setPageScroll: function(state, ps) {
		var sps = state.pageScroll;
		if (ps[0] != null && !isNaN(+ps[0])) sps[0] = ps[0];
		if (ps[1] != null && !isNaN(+ps[1])) sps[1] = ps[1];
	},
	setScreen: function(state, screen) {
		state.screen = screen;
	},
	setFormCampoValor: function(state, payload) {
		payload.campo.valor = payload.valor;
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
	},
	setFormLoginErro: function(state, erro) {
		state.formLoginErro = erro;
	},
	setFormUsuarioCadastrarErro: function(state, erro) {
		state.formUsuarioCadastrarErro = erro;
	}
};

var store = new Vuex.Store({
	state: state,
	getters: getters,
	actions: actions,
	mutations: mutations
});

RECAM.store = store;

})();
