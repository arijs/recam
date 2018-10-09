(function() {

RECAM.comp['form-campo/texto'] = {
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
      focused: false,
      autofill: false
    }
  },
  computed: {
    cssClass: function() {
      var campo = this.campo;
      return {
        'campo-filled': campo.valor || this.autofill,
        'campo-focused': this.focused,
        'campo-missing': campo.falta,
        'campo-error': campo.erro
      };
    }
  },
  methods: {
    focus: function() {
      if (this.mask) {
        this.$refs.inputMask.$refs.input.focus();
      } else {
        this.$refs.input.focus();
      }
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
      this.$store.commit('setFormCampoValor', {
        campo: this.campo,
        valor: valor
      });
      this.$emit('input', evt);
    },
    onAnimationStart: function(evt) {
      switch (evt.animationName) {
        case 'onAutoFillStart':
          this.onAutoFill(true); break;
        case 'onAutoFillCancel':
          this.onAutoFill(false); break;
      }
      this.$emit('animationstart', evt);
      // console.log('animationstart', evt, this.autofill, this.cssClass);
    },
    onAutoFill: function(af) {
      this.autofill = af;
      this.$emit('autofill', af);
    },
    campoClick: function(event) {
      var input = this.campo.mask ? this.$refs.inputMask.$el : this.$refs.input;
      if (event.target !== input) {
        input.focus();
      }
    }
  }
};

})();
