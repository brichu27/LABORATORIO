/**
 * Botones "copiar email" (sitio y panel). Reemplazan a los enlaces mailto:, que no hacen nada
 * cuando el navegador no tiene un programa de correo configurado.
 *  Uso: <button type="button" class="copiar-email" data-copiar="persona@dominio.com">...</button>
 *  Al copiar muestra un aviso sobre el botón ("¡Email copiado!") y lo anuncia a lectores de pantalla.
 */
(function () {
  'use strict';

  // navigator.clipboard solo existe en https o localhost; en http se usa el método clásico
  function copiar(texto) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(texto);
    }
    return new Promise(function (resolver, rechazar) {
      const area = document.createElement('textarea');
      area.value = texto;
      area.setAttribute('readonly', '');
      area.style.position = 'fixed';
      area.style.opacity = '0';
      document.body.appendChild(area);
      area.select();
      try {
        if (document.execCommand('copy')) { resolver(); } else { rechazar(new Error('copy')); }
      } catch (err) {
        rechazar(err);
      } finally {
        area.remove();
      }
    });
  }

  let anuncio = null;
  function anunciar(texto) {
    if (!anuncio) {
      anuncio = document.createElement('div');
      anuncio.className = 'copiar-email__anuncio';
      anuncio.setAttribute('role', 'status');
      anuncio.setAttribute('aria-live', 'polite');
      document.body.appendChild(anuncio);
    }
    anuncio.textContent = '';
    window.setTimeout(function () { anuncio.textContent = texto; }, 50);
  }

  function avisar(boton, texto, ok) {
    window.clearTimeout(Number(boton.dataset.temporizador || 0));
    boton.dataset.aviso = texto;
    boton.classList.toggle('copiado', ok);
    anunciar(texto);
    boton.dataset.temporizador = String(window.setTimeout(function () {
      delete boton.dataset.aviso;
      boton.classList.remove('copiado');
    }, ok ? 2000 : 4000));
  }

  document.addEventListener('click', function (e) {
    const boton = e.target.closest('[data-copiar]');
    if (!boton) return;
    const email = boton.dataset.copiar;
    copiar(email).then(
      function () { avisar(boton, '¡Email copiado!', true); },
      function () { avisar(boton, 'No se pudo copiar: ' + email, false); }
    );
  });
})();
