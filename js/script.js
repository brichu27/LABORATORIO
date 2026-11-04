/**
 * Interacciones generales del sitio (menu movil, carrusel de noticias, galeria de la ficha e indice de las paginas legales).
 *  - Cada bloque verifica que el elemento exista, porque el archivo se carga en todas las paginas.
 *  - "Cargar más" vive en js/cargar-mas.js (datos reales desde la API).
 */
(function () {
  'use strict';

  const menu = document.querySelector('#menu-btn');
  const navbar = document.querySelector('.header .navbar');
  if (menu && navbar) {
    // <button>: el teclado (Enter / Espacio) ya dispara "click"; aria-expanded informa el estado a lectores de pantalla
    const abrir = function (abierto) {
      menu.classList.toggle('fa-times', abierto);
      navbar.classList.toggle('active', abierto);
      menu.setAttribute('aria-expanded', String(abierto));
      menu.setAttribute('aria-label', abierto ? 'Cerrar menú' : 'Abrir menú');
    };
    menu.addEventListener('click', function () { abrir(!navbar.classList.contains('active')); });
    window.addEventListener('scroll', function () { abrir(false); });
  }

  // Carrusel de noticias (inicio y ficha de noticia). Avanza solo salvo que el usuario pida reducir el movimiento.
  if (typeof Swiper !== 'undefined' && document.querySelector('.card-wrapper')) {
    const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    new Swiper('.card-wrapper', {
      rewind: true,   // al llegar a la ultima noticia vuelve a la primera
      spaceBetween: 30,
      autoplay: reducirMovimiento ? false : { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true },
      pagination: { el: '.card-wrapper .swiper-pagination', clickable: true, dynamicBullets: true },
      navigation: { nextEl: '.card-wrapper .swiper-button-next', prevEl: '.card-wrapper .swiper-button-prev' },
      breakpoints: { 0: { slidesPerView: 1 }, 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } }
    });
  }

  // Galeria de la ficha de propiedad: click en miniatura => imagen principal y miniatura marcada como activa
  const principal = document.getElementById('galeria-principal');
  if (principal) {
    const minis = document.querySelectorAll('[data-galeria-mini]');
    minis.forEach(function (mini) {
      mini.addEventListener('click', function () {
        principal.src = mini.dataset.galeriaMini;
        minis.forEach(function (m) {
          m.classList.toggle('activa', m === mini);
          if (m === mini) { m.setAttribute('aria-current', 'true'); } else { m.removeAttribute('aria-current'); }
        });
      });
    });
  }

  // Índice de Política y Términos: en celular arranca plegado y se pliega al elegir una sección;
  // al desplazarse marca el artículo que se está leyendo
  const indice = document.querySelector('.legal__indice details');
  if (indice) {
    const movil = window.matchMedia('(max-width: 991px)');
    if (movil.matches) { indice.open = false; }
    const enlaces = indice.querySelectorAll('a[href^="#"]');
    enlaces.forEach(function (a) {
      a.addEventListener('click', function () { if (movil.matches) { indice.open = false; } });
    });
    if ('IntersectionObserver' in window) {
      const marcar = function (id) {
        enlaces.forEach(function (a) {
          const activo = a.getAttribute('href') === '#' + id;
          a.classList.toggle('activo', activo);
          if (activo) { a.setAttribute('aria-current', 'true'); } else { a.removeAttribute('aria-current'); }
        });
      };
      const observador = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (e) { if (e.isIntersecting) { marcar(e.target.id); } });
      }, { rootMargin: '-120px 0px -60% 0px' });
      document.querySelectorAll('.legal__articulo[id]').forEach(function (art) { observador.observe(art); });
    }
  }
})();
