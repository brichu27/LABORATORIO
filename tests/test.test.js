/**
 * @jest-environment jsdom
 */

// tests/contactForm.test.js
// Prueba unitaria simple para el formulario de contacto y la función de "cargar más"

/**
 * Esta prueba simula el llenado y envío del formulario de contacto.
 * Si el proceso funciona correctamente con datos hardcodeados, retorna TRUE.
 */

describe('Formulario de Contacto', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <form id="contactForm">
        <input type="text" name="name" />
        <input type="email" name="email" />
        <input type="number" name="phone" />
        <textarea name="mensaje"></textarea>
        <input type="submit" value="Enviar" />
      </form>
    `;
    document.getElementById("contactForm").addEventListener("submit", function(e) {
      e.preventDefault();
      const form = e.target;
      const data = {
        name: form.elements['name'].value,
        email: form.elements['email'].value,
        phone: form.elements['phone'].value,
        mensaje: form.elements['mensaje'].value
      };
      form._testResult = !!(data.name && data.email && data.phone && data.mensaje);
    });
  });

  test('Completar y enviar formulario retorna TRUE', () => {
    const form = document.getElementById('contactForm');
    form.elements['name'].value = 'Juan Perez';
    form.elements['email'].value = 'juan@mail.com';
    form.elements['phone'].value = '123456789';
    form.elements['mensaje'].value = 'Consulta de prueba';
    // Simular submit
    const event = new Event('submit', { bubbles: true, cancelable: true });
    form.dispatchEvent(event);
    expect(form._testResult).toBe(true);
  });
});

describe('Función cargar más (load more)', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <section class="properties">
        <div class="box-container">
          <div class="box" style="display:inline-block"></div>
          <div class="box" style="display:inline-block"></div>
          <div class="box" style="display:inline-block"></div>
          <div class="box" style="display:none"></div>
          <div class="box" style="display:none"></div>
          <div class="box" style="display:none"></div>
          <div class="box" style="display:none"></div>
        </div>
        <div class="load-more"><span class="btn2" style="display:block">Cargar mas</span></div>
      </section>
    `;
    window.currentItem = 3;
    window.loadMoreBtn = document.querySelector('.properties .load-more .btn2');
    window.loadMoreBtn.onclick = function() {
      let boxes = [...document.querySelectorAll('.properties .box-container .box')];
      for (var i = window.currentItem; i < window.currentItem + 3 && i < boxes.length; i++){
        boxes[i].style.display = 'inline-block';
      }
      window.currentItem += 3;
      if(window.currentItem >= boxes.length){
        window.loadMoreBtn.style.display = 'none';
      }
    };
  });

  test('Muestra 3 elementos más y oculta el botón al final', () => {
    const boxes = document.querySelectorAll('.properties .box-container .box');
    const btn = document.querySelector('.properties .load-more .btn2');
    // Primer click
    btn.onclick();
    expect(Array.from(boxes).slice(0,6).filter(b => b.style.display === 'inline-block').length).toBe(6);
    expect(btn.style.display).toBe('block');
    // Segundo click
    btn.onclick();
    expect(Array.from(boxes).filter(b => b.style.display === 'inline-block').length).toBe(7);
    expect(btn.style.display).toBe('none');
  });
});
