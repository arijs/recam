(function() {

var Utils = RECAM.Utils;
var reDate = /(\d+)-(\d+)-(\d+)/;

RECAM.comp['logado'] = {
	computed: {
		logoutLoading: function() {
			return this.$store.state.serviceLogoutLoading;
		},
		// session: function() {
		// 	return this.$store.state.session;
		// },
		usuario: function() {
			return this.$store.state.session.usuario;
		},
		acesso: function() {
			return this.$store.state.session.acesso;
		},
		reuniao: function() {
			return this.$store.state.session.reuniao;
		},
	},
	data: function() {
		return {
			renderizarMapa: false,
			exibirMapa: false
		};
	},
	methods: {
		printModelDate: function(dt) {
			var m = String(dt||'').match(reDate);
			if (m) {
				return [
					Utils.padStart(m[3],2,'0'),
					Utils.padStart(m[2],2,'0'),
					m[1]
				].join('/');
			}
		},
		selecionarCongregacao: function() {
			this.renderizarMapa = true;
			this.exibirMapa = true;
		},
		reuniaoSelecionada: function() {
			var vm = this;
			setTimeout(function() {
				vm.exibirMapa = false;
			}, 4000);
			vm.$forceUpdate();
		},
		clickLogout: function() {
			this.$store.dispatch('loadLogout');
		}
	}
};

})();
