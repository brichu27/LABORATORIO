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
    return el('i', clase);
  }

  /** Construye la tarjeta con la misma estructura que render_tarjeta_propiedad() en PHP. */
  function tarjetaPropiedad(p) {
    const box = el('div', 'box');
    const imgWrap = el('div', 'image');
    const img = document.createElement('img');
    img.src = p.imagen;
    img.alt = p.titulo;
    img.loading = 'lazy';
    imgWrap.appendChild(img);

    const content = el('div', 'content');
    content.appendChild(el('h3', '', p.precio));
    const dir = el('p');
    dir.append(icono('fa-solid fa-location-dot'), ' ' + p.direccion);
    content.appendChild(dir);

    const iconos = el('div', 'iconos');
    const banos = p.banos === 1 ? '1 baño' : p.banos + ' baños';
    const dorm = p.dormitorios === 1 ? '1 habitación' : p.dormitorios + ' habitaciones';
    iconos.append(icono('fa-solid fa-bath'), ' ' + banos + ' ', icono('fa-solid fa-bed'), ' ' + dorm + ' ',
      icono('fa-solid fa-ruler-horizontal'), ' ' + Math.round(p.superficie) + 'm²');
    content.appendChild(iconos);

    const link = el('a', 'btn', 'Más información');
    link.href = p.url;
    content.appendChild(link);

    box.append(imgWrap, content);
    return box;
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
