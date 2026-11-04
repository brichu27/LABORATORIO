/**
 * "Cargar más" propiedades desde la API (api/propiedades.php) - datos reales de la base.
 * Botón esperado: <button data-cargar-mas data-contenedor="#lista" data-offset="6" data-limite="6" data-filtros="operacion=VENTA">
 */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.CargarMas = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  function el(tag, clase, texto) {
    const n = document.createElement(tag);
    if (clase) n.className = clase;
    if (texto !== undefined) n.textContent = texto;   // textContent: nunca interpreta HTML (anti-XSS)
    return n;
  }

  function icono(clase) {
    const i = el('i', clase);
    i.setAttribute('aria-hidden', 'true');
    return i;
  }

  /** Construye la tarjeta con la misma estructura que render_tarjeta_propiedad() en PHP. Toda la tarjeta es el enlace. */
  function tarjetaPropiedad(p) {
    const tarjeta = el('article', 'card-propiedad');
    const enlace = el('a', 'card-propiedad__enlace');
    enlace.href = p.url;

    const foto = el('div', 'card-propiedad__foto');
    const img = document.createElement('img');
    img.src = p.imagen;
    img.alt = p.titulo;
    img.loading = 'lazy';
    img.width = 640;
    img.height = 400;
    const operacion = p.operacion || 'Venta';
    foto.append(img, el('span', 'card-propiedad__etiqueta card-propiedad__etiqueta--' + operacion.toLowerCase(), operacion));

    const cuerpo = el('div', 'card-propiedad__cuerpo');
    cuerpo.appendChild(el('p', 'card-propiedad__tipo', [p.tipo, p.barrio].filter(Boolean).join(' · ')));
    cuerpo.appendChild(el('p', 'card-propiedad__precio', p.precio));
    cuerpo.appendChild(el('h3', 'card-propiedad__titulo', p.titulo));
    const dir = el('p', 'card-propiedad__direccion');
    dir.append(icono('fa-solid fa-location-dot'), ' ' + (p.calle || p.direccion));
    cuerpo.appendChild(dir);

    const datos = el('ul', 'card-propiedad__datos');
    const dato = function (clase, texto) {
      const li = el('li');
      li.append(icono(clase), ' ' + texto);
      datos.appendChild(li);
    };
    dato('fa-solid fa-bed', p.dormitorios === 1 ? '1 dorm.' : p.dormitorios + ' dorm.');
    dato('fa-solid fa-bath', p.banos === 1 ? '1 baño' : p.banos + ' baños');
    dato('fa-solid fa-ruler-combined', Math.round(p.superficie) + ' m²');
    cuerpo.appendChild(datos);

    enlace.append(foto, cuerpo);
    tarjeta.appendChild(enlace);
    return tarjeta;
  }

  /**
   * Pide la siguiente pagina y la agrega al contenedor.
   * Devuelve la cantidad de items agregados, o -1 si hubo error.
   */
  async function cargarMas(boton, contenedor, fetchFn) {
    const pedir = fetchFn || (typeof fetch !== 'undefined' ? fetch.bind(window) : null);
    const offset = parseInt(boton.dataset.offset, 10) || 0;
    const limite = parseInt(boton.dataset.limite, 10) || 6;
    const params = new URLSearchParams(boton.dataset.filtros || '');
    params.set('offset', String(offset));
    params.set('limite', String(limite));
    const errorEl = boton.parentElement ? boton.parentElement.querySelector('[data-cargar-mas-error]') : null;

    boton.disabled = true;
    try {
      const resp = await pedir('api/propiedades.php?' + params.toString());
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      const data = await resp.json();
      data.items.forEach(function (p) { contenedor.appendChild(tarjetaPropiedad(p)); });
      boton.dataset.offset = String(offset + data.items.length);
      if (!data.hayMas) boton.style.display = 'none';
      if (errorEl) errorEl.textContent = '';
      return data.items.length;
    } catch (err) {
      if (errorEl) errorEl.textContent = 'No se pudieron cargar más propiedades. Intente nuevamente.';
      if (typeof console !== 'undefined') console.error(err);
      return -1;
    } finally {
      boton.disabled = false;
    }
  }

  function inicializar() {
    const boton = document.querySelector('[data-cargar-mas]');
    if (!boton) return;
    const contenedor = document.querySelector(boton.dataset.contenedor);
    if (!contenedor) return;
    boton.addEventListener('click', function () { cargarMas(boton, contenedor); });
  }

  if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', inicializar);
  }

  return { tarjetaPropiedad, cargarMas, inicializar };
});
