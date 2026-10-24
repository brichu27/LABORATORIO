/**
 * Validaciones del lado cliente (mismas reglas que includes/validador.php).
 * Uso en HTML: <form data-validar-form novalidate> y en cada campo data-validar="regla".
 * Reglas: requerido | nombre | email | telefono[:opcional] | texto:min:max | entero:min:max
 *         decimal:min:max[:opcional] | url | clave[:opcional]
 * Exporta las funciones para Jest (module.exports) y como window.Validaciones en el navegador.
 */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.Validaciones = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  const RE_NOMBRE = /^[\p{L}][\p{L}\s'\-]{1,79}$/u;
  const RE_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const RE_TELEFONO = /^\+?\(?\d[\d\s()\-]{6,18}\d$/;

  function nombreValido(v) {
    return typeof v === 'string' && RE_NOMBRE.test(v.trim());
  }

  function emailValido(v) {
    const t = String(v || '').trim();
    return t.length <= 120 && RE_EMAIL.test(t);
  }

  /** Rechaza negativos (-123...), letras y largos fuera de 8-15 digitos. */
  function telefonoValido(v) {
    const t = String(v || '').trim();
    if (!RE_TELEFONO.test(t)) return false;
    const digitos = t.replace(/\D/g, '').length;
    return digitos >= 8 && digitos <= 15;
  }

  function textoValido(v, min, max) {
    const largo = String(v || '').trim().length;
    return largo >= min && largo <= max;
  }

  function enteroValido(v, min, max) {
    const t = String(v || '').trim();
    if (!/^-?\d+$/.test(t)) return false;
    const n = Number(t);
    return n >= min && n <= max;
  }

  function decimalValido(v, min, max) {
    const t = String(v || '').trim().replace(',', '.');
    if (!/^-?\d+(\.\d+)?$/.test(t)) return false;
    const n = Number(t);
    return n >= min && n <= max;
  }

  function urlValida(v) {
    const t = String(v || '').trim();
    if (t === '') return true;
    try {
      const u = new URL(t);
      return u.protocol === 'https:' || u.protocol === 'http:';
    } catch (e) {
      return false;
    }
  }

  function claveValida(v) {
    const t = String(v || '');
    return t.length >= 8 && t.length <= 72 && /[A-Za-z]/.test(t) && /\d/.test(t);
  }

  /**
   * Evalua una regla. Devuelve '' si es valido o el mensaje de error.
   */
  function validarValor(regla, valor) {
    const partes = String(regla || '').split(':');
    const tipo = partes[0];
    const opcional = partes.includes('opcional');
    const vacio = String(valor || '').trim() === '';
    if (opcional && vacio) return '';

    switch (tipo) {
      case 'requerido':
        return vacio ? 'Este campo es obligatorio.' : '';
      case 'nombre':
        return nombreValido(valor) ? '' : 'Solo letras, espacios, apóstrofe o guion (2 a 80 caracteres).';
      case 'email':
        return emailValido(valor) ? '' : 'Ingrese un email válido (ej: nombre@dominio.com).';
      case 'telefono':
        return telefonoValido(valor) ? '' : 'Teléfono inválido: entre 8 y 15 dígitos, sin signos negativos (ej: 11 2345-6789).';
      case 'texto': {
        const min = Number(partes[1] || 0);
        const max = Number(partes[2] || 1000);
        return textoValido(valor, min, max) ? '' : `Debe tener entre ${min} y ${max} caracteres.`;
      }
      case 'entero': {
        const min = Number(partes[1]);
        const max = Number(partes[2]);
        return enteroValido(valor, min, max) ? '' : `Ingrese un número entero entre ${min} y ${max}.`;
      }
      case 'decimal': {
        const min = Number(partes[1]);
        const max = Number(partes[2]);
        return decimalValido(valor, min, max) ? '' : `Ingrese un número entre ${min} y ${max}.`;
      }
      case 'url':
        return urlValida(valor) ? '' : 'Ingrese una URL válida que comience con https://';
      case 'clave':
        return claveValida(valor) ? '' : 'Mínimo 8 caracteres, con letras y números.';
      default:
        return '';
    }
  }

  /** Muestra/oculta el error de un campo. Devuelve true si es valido. */
  function validarCampo(input) {
    const mensaje = validarValor(input.dataset.validar, input.value);
    let span = input.parentElement ? input.parentElement.querySelector('.error-msg') : null;
    if (!span && input.parentElement) {
      span = document.createElement('span');
      span.className = 'error-msg';
      input.insertAdjacentElement('afterend', span);
    }
    if (span) span.textContent = mensaje;
    input.classList.toggle('campo-invalido', mensaje !== '');
    input.setAttribute('aria-invalid', mensaje !== '' ? 'true' : 'false');
    return mensaje === '';
  }

  /** Valida todos los campos con data-validar. Enfoca el primero con error. */
  function validarFormulario(form) {
    const campos = Array.from(form.querySelectorAll('[data-validar]'));
    let primero = null;
    campos.forEach(function (c) {
      if (!validarCampo(c) && !primero) primero = c;
    });
    if (primero && typeof primero.focus === 'function') primero.focus();
    return primero === null;
  }

  function inicializar() {
    document.querySelectorAll('form[data-validar-form]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (!validarFormulario(form)) e.preventDefault();
      });
      form.querySelectorAll('[data-validar]').forEach(function (c) {
        c.addEventListener('blur', function () { validarCampo(c); });
      });
    });
  }

  if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', inicializar);
  }

  return {
    nombreValido, emailValido, telefonoValido, textoValido, enteroValido, decimalValido,
    urlValida, claveValida, validarValor, validarCampo, validarFormulario, inicializar
  };
});
