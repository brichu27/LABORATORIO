/**
 * Comportamiento del panel de administracion.
 *  - Modal de confirmacion para acciones destructivas (reemplaza el confirm() nativo).
 *    Uso: <form data-confirmar="¿Mensaje?" data-confirmar-boton="Dar de baja">
 *  - Vista previa de imagenes antes de subirlas.
 *    Uso: <input type="file" data-preview="#contenedor">
 */
(function () {
  'use strict';

  /* ------------------------- Modal de confirmacion ------------------------- */
  let modal = null;

  function crearModal() {
    const d = document.createElement('dialog');
    d.className = 'modal-confirmar';
    d.setAttribute('aria-labelledby', 'modal-confirmar-titulo');
    d.innerHTML =
      '<h2 id="modal-confirmar-titulo"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar acción</h2>' +
      '<p class="modal-mensaje"></p>' +
      '<div class="botonera">' +
      '  <button type="button" class="boton boton-secundario" data-modal-cancelar>Cancelar</button>' +
      '  <button type="button" class="boton" data-modal-aceptar>Confirmar</button>' +
      '</div>';
    document.body.appendChild(d);
    d.querySelector('[data-modal-cancelar]').addEventListener('click', function () { d.close(); });
    // Click fuera del contenido (sobre el fondo) => cancelar
    d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
    return d;
  }

  function confirmar(form) {
    modal = modal || crearModal();
    modal.querySelector('.modal-mensaje').textContent = form.dataset.confirmar;   // textContent: sin HTML
    const viejo = modal.querySelector('[data-modal-aceptar]');
    const aceptar = viejo.cloneNode(false);              // clon sin listeners de usos anteriores
    aceptar.textContent = form.dataset.confirmarBoton || 'Confirmar';
    viejo.replaceWith(aceptar);
    aceptar.addEventListener('click', function () {
      aceptar.disabled = true;
      form.submit();                                     // submit() no vuelve a disparar el evento
    });
    modal.showModal();
    modal.querySelector('[data-modal-cancelar]').focus(); // foco en la opcion segura
  }

  document.querySelectorAll('form[data-confirmar]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      confirmar(f);
    });
  });

  /* ------------------------ Vista previa de imagenes ----------------------- */
  const MAX_BYTES = 2 * 1024 * 1024;
  const TIPOS = ['image/jpeg', 'image/png', 'image/webp'];

  function tamanio(bytes) {
    return bytes >= 1024 * 1024 ? (bytes / 1024 / 1024).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
  }

  document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
    const contenedor = document.querySelector(input.dataset.preview);
    if (!contenedor) return;
    let urls = [];

    input.addEventListener('change', function () {
      urls.forEach(URL.revokeObjectURL);
      urls = [];
      contenedor.textContent = '';
      Array.from(input.files).forEach(function (archivo) {
        const item = document.createElement('div');
        item.className = 'imagen-item';
        let problema = '';
        if (!TIPOS.includes(archivo.type)) problema = 'Formato no permitido';
        else if (archivo.size > MAX_BYTES) problema = 'Supera 2 MB';

        if (TIPOS.includes(archivo.type)) {
          const img = document.createElement('img');
          const url = URL.createObjectURL(archivo);
          urls.push(url);
          img.src = url;
          img.alt = '';
          item.appendChild(img);
        }
        const nombre = document.createElement('span');
        nombre.textContent = archivo.name + ' (' + tamanio(archivo.size) + ')';
        item.appendChild(nombre);
        if (problema) {
          item.classList.add('imagen-invalida');
          const err = document.createElement('span');
          err.className = 'error-msg';
          err.textContent = problema;
          item.appendChild(err);
        }
        contenedor.appendChild(item);
      });
    });
  });
})();
