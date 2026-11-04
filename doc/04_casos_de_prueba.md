# Casos de prueba – resultados de ejecución

Especificación: casos CP-01…CP-24 del documento [casosDePrueba.docx](casosDePrueba.docx). Ejecución automatizada
con Chrome headless (Puppeteer) sobre una base recién creada con `01_schema.sql` + `02_datos_iniciales.sql`
+ `03_noticias_contenido.sql`, un ADMIN y un VENDEDOR habilitado. Cada caso guarda su captura en
[evidencias/](evidencias/).

Se hicieron tres ejecuciones completas. La primera (24/10/2026) encontró los defectos D-01 y D-02. La segunda
(25/10/2026) se hizo después de los ajustes de diseño del sitio y encontró el defecto D-03. La tercera
(01/11/2026) se hizo después del rediseño del panel de administración y de la ampliación de los datos iniciales
a 12 propiedades (el listado muestra 8 por carga); aprobó los 24 casos sin defectos nuevos y es la que registran
los resultados y las capturas de este documento.

## Entorno

| | |
|---|---|
| Fecha | 1.ª ejecución: 24/10/2026 · 2.ª ejecución: 25/10/2026 · 3.ª ejecución: 01/11/2026 |
| Servidor | PHP 8 (servidor embebido `php -S localhost:8000`) |
| Base de datos | MariaDB 10.11 (Docker), InnoDB, utf8mb4 |
| Navegador | Google Chrome (headless) 1280×860 |
| Usuarios | ADMIN `admin@viviendasub.com` · VENDEDOR `viviendasub+bpaez@gmail.com` |
| Automatizados previos | Jest 37/37 · `tests/php/validador_test.php` 15/15 |

## Resumen: 24/24 aprobados

| ID | CU | Tipo | Precondición | Pasos | Datos | Resultado esperado | Resultado obtenido | Estado | Evidencia |
|---|---|---|---|---|---|---|---|---|---|
| CP-01 | CU-01 | M | Hay ≥ 9 propiedades DISPONIBLES | Abrir propiedades.php | — | Se muestran 8 tarjetas y el botón "Cargar más" | 8 tarjetas visibles; botón "Cargar más" visible | ✅ Aprobado | [CP-01](evidencias/CP-01.png) |
| CP-02 | CU-01 | M | Propiedades en ≥ 2 barrios | Elegir barrio "Palermo" > Filtrar | barrio=Palermo | Solo propiedades de Palermo; contador actualizado | 2 tarjetas, todas de Palermo: true; contador: "2 propiedades encontradas" | ✅ Aprobado | [CP-02](evidencias/CP-02.png) |
| CP-03 | CU-01 | A | API disponible | Clic en "Cargar más" hasta el final | — | Se agregan las siguientes; el botón se oculta al no haber más | 1 clic(s): 12 tarjetas en total; botón oculto al final | ✅ Aprobado | [CP-03](evidencias/CP-03.png) |
| CP-04 | CU-02 | M/A | Propiedad DISPONIBLE | Completar formulario de consulta > Enviar | Juan Pérez, juan@mail.com, 11 2345-6789 | Mensaje de éxito; registro en consultas con el agente de la propiedad | Mensaje: "¡Gracias! Tu consulta fue enviada. Nicolas se comunicará a la brevedad.". Registro (propiedad, agente, nombre): 2,2,Juan Pérez | ✅ Aprobado | [CP-04](evidencias/CP-04.png) |
| CP-05 | CU-02 | A | — | Ingresar teléfono válido | 11 2345-6789 / +54 9 11 2345-6789 | Campo válido | 11 2345-6789 válido: true; +54 9 11 2345-6789 válido: true | ✅ Aprobado | [CP-05](evidencias/CP-05.png) |
| CP-06 | CU-02 | A | — | Ingresar teléfono inválido > Enviar | -1123456789 / abc / 123 | Error "Teléfono inválido"; no se envía | -1123456789 → "Teléfono inválido", no se envía; abc → "Teléfono inválido", no se envía; 123 → "Teléfono inválido", no se envía | ✅ Aprobado | [CP-06](evidencias/CP-06.png) |
| CP-07 | CU-02/03/04 | A | — | Ingresar email inválido | juan@ / juan@mail | Error de formato de email | 6/6 marcados inválidos (3 formularios × 2 valores). Mensaje: "Ingrese un email válido (ej: nombre@dominio.com)." | ✅ Aprobado | [CP-07](evidencias/CP-07.png) |
| CP-08 | CU-02/03/04 | A | — | Ingresar nombre con números | Juan123 | Error de formato de nombre | Juan123 rechazado en los 3 formularios. Mensaje: "Solo letras, espacios, apóstrofe o guion (2 a 80 caracteres)." | ✅ Aprobado | [CP-08](evidencias/CP-08.png) |
| CP-09 | CU-02 | M | JavaScript deshabilitado | Enviar con teléfono -5 | -5 | El servidor rechaza y muestra el error; no se guarda | JavaScript deshabilitado: el servidor responde "Teléfono inválido"; registros en consultas sin cambios (1) | ✅ Aprobado | [CP-09](evidencias/CP-09.png) |
| CP-10 | CU-03 | M | — | Completar solicitud de tasación > Enviar | Tipo Casa, dirección válida | Mensaje de éxito; registro en solicitudes_tasacion | Mensaje: "¡Solicitud recibida! Un tasador te contactará para coordinar la visita.". Registro: Casa - Cabildo 2502, Belgrano | ✅ Aprobado | [CP-10](evidencias/CP-10.png) |
| CP-11 | CU-04 | M | — | Postularse con LinkedIn inválido; luego válido | linkedin: "abc" / https://… | Primero error de URL; luego éxito y registro en postulaciones | "abc" → "Ingrese una URL válida que comience con https://". Con URL válida: "¡Gracias por postularte! El equipo de RR.HH. revisará tu perfil y te contactará."; registro: https://www.linkedin.com/in/juan-perez | ✅ Aprobado | [CP-11a](evidencias/CP-11a.png) [CP-11](evidencias/CP-11.png) |
| CP-12 | CU-05 | M | Admin creado | Login con credenciales correctas | email + clave | Acceso al tablero | Redirige a admin/index.php; título "Inicio" | ✅ Aprobado | [CP-12](evidencias/CP-12.png) |
| CP-13 | CU-05 | M | — | Login con clave errónea 5 veces | clave incorrecta | Mensaje genérico; al 6.º intento, bloqueo de 5 minutos | Intentos 1-5: "Email o clave incorrectos, o usuario inactivo.". 6.º intento (con la clave correcta): "Demasiados intentos fallidos. Espere 5 minutos e intente nuevamente." | ✅ Aprobado | [CP-13](evidencias/CP-13.png) |
| CP-14 | CU-08 | M | Sesión de VENDEDOR | Abrir admin/usuarios.php por URL | — | "Acceso denegado" (HTTP 403) | HTTP 403: "Acceso denegado: su rol no tiene permiso para esta sección." | ✅ Aprobado | [CP-14](evidencias/CP-14.png) |
| CP-15 | CU-06 | M | Sesión iniciada | Nueva propiedad con 2 imágenes > Guardar | Datos válidos | Aparece en el panel y en el sitio con su portada | "Propiedad #13 creada correctamente."; 2 imágenes guardadas; en el sitio con portada uploads/propiedades/c3b5655a504efe3c1a43004fa89a2e6c.jpg | ✅ Aprobado | [CP-15](evidencias/CP-15.png) [CP-15b](evidencias/CP-15b.png) |
| CP-16 | CU-06 | M | Sesión de VENDEDOR | Abrir propiedad_form.php?id= de otro agente | id ajeno | "No puede modificar propiedades de otro agente" (403) | HTTP 403: "No puede modificar propiedades de otro agente." | ✅ Aprobado | [CP-16](evidencias/CP-16.png) |
| CP-17 | CU-01/09 | A/M | — | Cargar título con <script>alert(1)</script> | texto con HTML | Se muestra como texto; no se ejecuta | Título guardado tal cual; en la ficha se ve como texto: "<script>alert(1)</script> Casa"; alert ejecutado: false | ✅ Aprobado | [CP-17](evidencias/CP-17.png) |
| CP-18 | CU-06 | M | Propiedad con consultas | Dar de baja la propiedad | — | Desaparece del sitio; queda INACTIVA; consultas conservadas | Estado: INACTIVA; consultas conservadas: 1/1; en el listado público: no; ficha HTTP 404 | ✅ Aprobado | [CP-18a](evidencias/CP-18a.png) [CP-18](evidencias/CP-18.png) |
| CP-19 | CU-09 | M | Sesión ADMIN | Crear noticia como borrador; luego publicarla | — | Borrador no visible en blog; publicada sí | Noticia #5: como borrador visible en el blog: no; publicada visible: sí | ✅ Aprobado | [CP-19a](evidencias/CP-19a.png) [CP-19](evidencias/CP-19.png) |
| CP-20 | CU-08 | M | Sesión ADMIN | Alta de vendedor con clave; luego desactivarlo | — | Puede ingresar; desactivado no ingresa ni aparece en Nosotros | Activo: ingresa sí, en Nosotros sí. Desactivado: sesión abierta cerrada ("Su usuario fue desactivado. Consulte con un administrador."), nuevo login rechazado ("Email o clave incorrectos, o usuario inactivo."), en Nosotros no | ✅ Aprobado | [CP-20a](evidencias/CP-20a.png) [CP-20](evidencias/CP-20.png) |
| CP-21 | Seguridad | M | — | Enviar POST sin token CSRF (herramienta tipo Postman) | — | HTTP 400 "Solicitud inválida" | HTTP 400: "Solicitud inválida (token CSRF). Recargue la página e intente nuevamente."; la propiedad #1 sigue DISPONIBLE | ✅ Aprobado | [CP-21](evidencias/CP-21.png) |
| CP-22 | CU-01 | M | — | Revisar enlaces de WhatsApp | — | Número del enlace = número visible en todos los casos | 26 enlaces revisados en 5 páginas; 0 no coinciden  | ✅ Aprobado | [CP-22](evidencias/CP-22.png) |
| CP-23 | CU-06 | M | Sesión iniciada | Subir un PDF renombrado a .jpg | archivo.pdf → .jpg | "Formato no permitido"; no se guarda nada | Mensaje: "Formato no permitido. Use JPG, PNG o WEBP."; propiedades/imágenes antes 13/14, después 13/14 | ✅ Aprobado | [CP-23](evidencias/CP-23.png) |
| CP-24 | Datos | M | Base creada | INSERT de consulta con propiedad inexistente | propiedad_id 999 | ERROR 1452 (FK) | ERROR 1452 (23000) at line 1: Cannot add or update a child row: a foreign key constraint fails (`viviendas_ub`.`consultas`, CONSTRAINT `fk_consultas_propiedad` FOREIGN KEY (`propiedad_id`) REFERENCES `propiedades` (`id`)) | ✅ Aprobado | [CP-24](evidencias/CP-24.png) |

Tipo: M = manual, A = automatizado (Jest), M/A = ambos. En esta ejecución todos se corrieron con un script de
Puppeteer que reproduce los pasos de cada caso en el navegador; los marcados "A" además están cubiertos por Jest.

## Defectos encontrados y corregidos durante la ejecución

| # | Caso | Defecto | Corrección | Re-ejecución |
|---|---|---|---|---|
| D-01 | CP-21 | El mensaje de rechazo CSRF decía "Solicitud invalida … la pagina" (sin tildes); el resultado esperado es "Solicitud inválida". El comportamiento (HTTP 400, sin cambios en la base) ya era correcto. | Texto corregido en `includes/funciones.php` (y "conexión" en `includes/db.php`). | ✅ Aprobado |
| D-02 | CP-20 | Un vendedor desactivado **con la sesión abierta** seguía operando el panel hasta cerrar sesión; tampoco regía de inmediato un cambio de rol. | `requiere_login()` (`includes/auth.php`) relee `activo` y rol desde la base en cada pedido; si está inactivo cierra la sesión y avisa. | ✅ Aprobado (CP-20 ahora lo verifica) |
| D-03 | CP-11 | 2.ª ejecución. Con el formulario de postulación en dos columnas, al corregir un campo con error y hacer clic en "Enviar", el mensaje de error desaparecía recién al salir del campo; el botón se desplazaba antes de recibir el clic y el envío se perdía. | `js/validaciones.js` vuelve a validar un campo marcado con error mientras se escribe (evento `input`), así el mensaje se borra antes del clic. Prueba nueva en `tests/validaciones.test.js`. | ✅ Aprobado |
| D-04 | CP-04, CP-10, CP-11 | Detectado al preparar el manual de usuario. Después de enviar un formulario la página volvía a la altura del formulario (el navegador conserva el ancla del `action`, p. ej. `#form-tasacion`), pero el mensaje de éxito se mostraba arriba de todo, fuera de la vista. | `flash_render()` envuelve los mensajes en un bloque `id="aviso"` y los tres formularios redirigen a `#aviso`. | ✅ Aprobado (24/24) |

## Anexo – pruebas automatizadas

Pruebas unitarias de `js/validaciones.js` y `js/cargar-mas.js` (Jest + jsdom) y del validador del
servidor (`includes/validador.php`). Ambas prueban el código real del sitio, no una copia.

| Suite | Comando | Resultado | Evidencia |
|---|---|---|---|
| Jest | `npx jest --verbose` | 37/37 | [jest.png](evidencias/jest.png) |
| PHP | `php tests/php/validador_test.php` | 15 OK, 0 fallas | [php.png](evidencias/php.png) |
