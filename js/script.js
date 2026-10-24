/**
 * Interacciones generales del sitio.
 * Correcciones respecto de la version anterior:
 *  - Cada bloque verifica que el elemento exista (antes fallaba con TypeError en paginas sin ".properties").
 *  - Los Swiper se inicializan solo si estan en la pagina y con botones acotados a su contenedor.
 *  - La logica de "Cargar más" se movio a js/cargar-mas.js (datos reales desde la API).
 */
(function () {
  'use strict';

  const menu = document.querySelector('#menu-btn');
  const navbar = document.querySelector('.header .navbar');
  if (menu && navbar) {
    const alternar = function () {
      menu.classList.toggle('fa-times');
      navbar.classList.toggle('active');
    };
    menu.addEventListener('click', alternar);
    menu.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); alternar(); }
    });
    window.addEventListener('scroll', function () {
      menu.classList.remove('fa-times');
      navbar.classList.remove('active');
    });
  }

  if (typeof Swiper !== 'undefined') {
    if (document.querySelector('.home-slider')) {
      new Swiper('.home-slider', {
        loop: true,
        navigation: { nextEl: '.home-slider .swiper-button-next', prevEl: '.home-slider .swiper-button-prev' }
      });
    }
    if (document.querySelector('.card-wrapper')) {
      new Swiper('.card-wrapper', {
        loop: document.querySelectorAll('.card-wrapper .swiper-slide').length > 3,
        spaceBetween: 30,
        pagination: { el: '.card-wrapper .swiper-pagination', clickable: true, dynamicBullets: true },
        navigation: { nextEl: '.card-wrapper .swiper-button-next', prevEl: '.card-wrapper .swiper-button-prev' },
        breakpoints: { 0: { slidesPerView: 1 }, 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } }
      });
    }
  }

  // Galeria de la ficha de propiedad: click en miniatura => imagen principal
  const principal = document.getElementById('galeria-principal');
  if (principal) {
    document.querySelectorAll('[data-galeria-mini]').forEach(function (mini) {
      mini.addEventListener('click', function () { principal.src = mini.src; });
    });
  }
})();
