(function() {

OiMPSAC.comp['form-campo/texto-multilinha'] = {
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
      // valor: this.campo.valor,
      focused: false
    }
  },
  computed: {
    cssClass: function() {
      var campo = this.campo;
      return {
        'campo-filled': campo.valor,
        'campo-focused': this.focused,
        'campo-missing': campo.falta,
        'campo-error': campo.erro
      };
    }
  },
  methods: {
    focus: function() {
      this.$refs.input.focus();
    },
    reloadValue: function() {
      // this.valor = this.campo.valor;
    },
    onFocus: function(evt) {
      this.focused = true;
      // console.log('campo/texto focus', evt, this);
      this.$emit('focus', evt);
      if (this.focusRevertScroll) {
        // console.log('texto revertNextScroll', this);
        this.$root.revertNextScroll();
      }
    },
    onBlur: function(evt) {
      this.focused = false;
      this.$emit('blur', evt);
    },
    onInput: function(evt) {
      //this.$emit('input', this.value);
      var valor;
      if ('string' === typeof evt) {
        valor = evt;
      } else if (evt && evt.target && 'string' === typeof evt.target.value) {
        valor = evt.target.value;
      }
      this.$store.commit('setFormCampoValue', {
        campo: this.campo,
        value: valor
      });
      this.$emit('input', evt);
    },
    campoClick: function(event) {
      var input = this.$refs.input;
      if (event.target !== input) {
        input.focus();
      }
    }
  }
};

})();
