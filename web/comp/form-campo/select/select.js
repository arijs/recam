(function() {

var Utils = RECAM.Utils;

RECAM.comp['form-campo/select'] = {
  props: {
    campo: {
      type: Object,
      required: true
    },
    focusRevertScroll: {
      type: Boolean,
      default: false
    },
    hideErrorMessage: {
      type: Boolean,
      default: false
    }
  },
  data: function() {
    return {
      opened: false,
      focused: false
    }
  },
  computed: {
    cssClass: function() {
      var campo = this.campo;
      return {
        'campo-opened': Boolean(this.opened),
        'campo-focused': Boolean(this.focused),
        'campo-filled': Boolean(campo.selecionado),
        'campo-missing': Boolean(campo.falta),
        'campo-error': Boolean(campo.erro)
      };
    },
    selecionadoTexto: function() {
      var s = this.campo.selecionado;
      return s ? s.texto : '';
    }
  },
  methods: {
    setOpened: function(opened) {
      this.opened = opened;
    },
    setFocused: function(focused) {
      this.focused = focused;
    },
    campoClick: function(event) {
      // var lista = this.$refs.lista;
      this.opened = !(this.opened);// || Utils.isChildOf(event.target, lista));
    },
    opcaoClick: function(opcao) {
      this.$store.commit('setFormCampoSelecionado', {
        campo: this.campo,
        selecionado: opcao
      });
      // this.campo.selecionado = opcao;
      this.$emit('change');
      this.campoClick();
      this.emitBlur();
      this.$refs.select.focus();
    },
    pressSpaceCampo: function(event) {
      this.campoClick(event);
      event.preventDefault();
    },
    pressSpaceOpcao: function(opcao, event) {
      this.opcaoClick(opcao);
      event.preventDefault();
    },
    emitFocus: function(evt) {
      // console.log('campo/select focus', evt, this);
      this.$emit('focus', evt);
      this.focused = true;
      if (this.focusRevertScroll) {
        // console.log('select revertNextScroll', this);
        this.$root.revertNextScroll();
      }
    },
    emitBlur: function(evt) {
      var act = document.activeElement;
      if (!evt || !act || !Utils.isChildOf(act, this.$el)) {
        this.$emit('blur', evt);
      }
      this.focused = false;
    }
  },
  mounted: function() {
    var self = this;
    this.$root.$on('rootClick', function(ev) {
      if ( !Utils.isChildOf(ev.target, self.$el) ) {
        self.opened = false;
      }
    });
  }
};

})();
