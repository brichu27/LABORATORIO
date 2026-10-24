/**
 * Pruebas unitarias de js/validaciones.js (se prueba el CODIGO REAL del sitio, no una copia).
 * IDs alineados con la especificacion formal de casos de prueba (CP-xx).
 */
const V = require('../js/validaciones.js');

describe('Teléfono (CP-05 / CP-06)', () => {
  test.each([
    ['11 2345-6789', true],
    ['+54 9 11 2345-6789', true],
    ['1123456789', true],
    ['(011) 4444-5555', true],
  ])('acepta formato válido %s', (valor, esperado) => {
    expect(V.telefonoValido(valor)).toBe(esperado);
  });

  test.each([
    ['-1123456789', 'número negativo'],
    ['-1', 'negativo corto'],
    ['abc12345678', 'contiene letras'],
    ['1234567', 'menos de 8 dígitos'],
    ['1234567890123456', 'más de 15 dígitos'],
    ['', 'vacío'],
  ])('rechaza %s (%s)', (valor) => {
    expect(V.telefonoValido(valor)).toBe(false);
  });
});

describe('Email (CP-07)', () => {
  test('acepta email válido', () => expect(V.emailValido('juan.perez@mail.com')).toBe(true));
  test.each(['juan', 'juan@', 'juan@mail', 'ju an@mail.com', '@mail.com'])('rechaza %s', (v) => {
    expect(V.emailValido(v)).toBe(false);
  });
});

describe('Nombre (CP-08)', () => {
  test.each(['José Pérez', "María O'Connor", 'Ana-Laura'])('acepta %s', (v) => expect(V.nombreValido(v)).toBe(true));
  test.each(['Juan123', 'A', '   ', '<script>'])('rechaza %s', (v) => expect(V.nombreValido(v)).toBe(false));
});

describe('Reglas genéricas', () => {
  test('texto con longitud mínima', () => {
    expect(V.validarValor('texto:10:1000', 'corto')).not.toBe('');
    expect(V.validarValor('texto:10:1000', 'Mensaje suficientemente largo')).toBe('');
  });
  test('campo opcional vacío es válido', () => {
    expect(V.validarValor('decimal:1:100000:opcional', '')).toBe('');
    expect(V.validarValor('telefono:opcional', '')).toBe('');
  });
  test('entero fuera de rango', () => {
    expect(V.validarValor('entero:0:60', '-3')).not.toBe('');
    expect(V.validarValor('entero:0:60', '5')).toBe('');
  });
  test('url debe ser http/https', () => {
    expect(V.urlValida('https://www.linkedin.com/in/demo')).toBe(true);
    expect(V.urlValida('javascript:alert(1)')).toBe(false);
  });
  test('clave requiere letras y números', () => {
    expect(V.claveValida('abcdefgh')).toBe(false);
    expect(V.claveValida('Clave2026')).toBe(true);
  });
});

describe('Formulario completo en el DOM (CP-04 / CP-05)', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <form data-validar-form>
        <div class="inputBox"><input name="nombre" data-validar="nombre"><span class="error-msg"></span></div>
        <div class="inputBox"><input name="email" data-validar="email"><span class="error-msg"></span></div>
        <div class="inputBox"><input name="telefono" data-validar="telefono"><span class="error-msg"></span></div>
        <div class="inputBox"><textarea name="mensaje" data-validar="texto:10:1000"></textarea><span class="error-msg"></span></div>
      </form>`;
  });

  test('datos válidos => formulario válido y sin mensajes', () => {
    const f = document.querySelector('form');
    f.nombre.value = 'Juan Pérez';
    f.email.value = 'juan@mail.com';
    f.telefono.value = '11 2345-6789';
    f.mensaje.value = 'Quisiera coordinar una visita.';
    expect(V.validarFormulario(f)).toBe(true);
    expect([...f.querySelectorAll('.error-msg')].every((s) => s.textContent === '')).toBe(true);
  });

  test('teléfono negativo => formulario inválido, campo marcado y mensaje visible', () => {
    const f = document.querySelector('form');
    f.nombre.value = 'Juan Pérez';
    f.email.value = 'juan@mail.com';
    f.telefono.value = '-1123456789';
    f.mensaje.value = 'Quisiera coordinar una visita.';
    expect(V.validarFormulario(f)).toBe(false);
    expect(f.telefono.classList.contains('campo-invalido')).toBe(true);
    expect(f.telefono.getAttribute('aria-invalid')).toBe('true');
    expect(f.telefono.nextElementSibling.textContent).toMatch(/Teléfono inválido/);
  });

  test('submit inválido se cancela (preventDefault)', () => {
    V.inicializar();
    const f = document.querySelector('form');
    const evento = new Event('submit', { cancelable: true });
    f.dispatchEvent(evento);
    expect(evento.defaultPrevented).toBe(true);
  });
});
