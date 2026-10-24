/**
 * Pruebas de js/cargar-mas.js con fetch simulado (sin servidor).
 */
const { cargarMas, tarjetaPropiedad } = require('../js/cargar-mas.js');

const propiedad = (id) => ({
  id, titulo: 'Depto ' + id, precio: 'USD 100.000', direccion: 'Calle ' + id + ', Palermo, CABA.',
  dormitorios: 2, banos: 1, superficie: 60, imagen: 'imagenes/propiedad-1.jpg', url: 'propiedad.php?id=' + id,
});

const respuesta = (cuerpo, ok = true, status = 200) => Promise.resolve({ ok, status, json: () => Promise.resolve(cuerpo) });

function armarDom() {
  document.body.innerHTML = `
    <div id="lista"></div>
    <div class="load-more">
      <button data-cargar-mas data-contenedor="#lista" data-offset="6" data-limite="6" data-filtros="operacion=VENTA">Cargar más</button>
      <p data-cargar-mas-error></p>
    </div>`;
  return { boton: document.querySelector('button'), lista: document.getElementById('lista') };
}

describe('Cargar más propiedades (CP-03)', () => {
  test('agrega tarjetas, actualiza offset y envía filtros/paginación', async () => {
    const { boton, lista } = armarDom();
    const fetchMock = jest.fn(() => respuesta({ items: [propiedad(7), propiedad(8), propiedad(9)], total: 15, hayMas: true }));
    const agregadas = await cargarMas(boton, lista, fetchMock);
    expect(agregadas).toBe(3);
    expect(lista.querySelectorAll('.box')).toHaveLength(3);
    expect(boton.dataset.offset).toBe('9');
    expect(boton.style.display).not.toBe('none');
    const url = fetchMock.mock.calls[0][0];
    expect(url).toContain('operacion=VENTA');
    expect(url).toContain('offset=6');
    expect(url).toContain('limite=6');
  });

  test('oculta el botón cuando no hay más resultados', async () => {
    const { boton, lista } = armarDom();
    await cargarMas(boton, lista, () => respuesta({ items: [propiedad(7)], total: 7, hayMas: false }));
    expect(boton.style.display).toBe('none');
  });

  test('muestra mensaje de error si la API falla', async () => {
    const { boton, lista } = armarDom();
    jest.spyOn(console, 'error').mockImplementation(() => {});
    const r = await cargarMas(boton, lista, () => respuesta({}, false, 500));
    expect(r).toBe(-1);
    expect(document.querySelector('[data-cargar-mas-error]').textContent).toMatch(/No se pudieron/);
    expect(boton.disabled).toBe(false);
  });
});

describe('Seguridad de la tarjeta (CP-17)', () => {
  test('no interpreta HTML recibido (anti-XSS)', () => {
    const p = propiedad(1);
    p.titulo = '<img src=x onerror=alert(1)>';
    p.direccion = '<b>inyectado</b>';
    const tarjeta = tarjetaPropiedad(p);
    expect(tarjeta.querySelector('b')).toBeNull();
    expect(tarjeta.querySelectorAll('img')).toHaveLength(1);
    expect(tarjeta.textContent).toContain('<b>inyectado</b>');
  });
});
