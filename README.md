# Viviendas UB – Sitio inmobiliario Full Stack (PHP + MySQL)

Proyecto de Prácticas de Laboratorio – Tecnicatura en Programación de Computadoras (Universidad de Belgrano).

Sitio web de una inmobiliaria con un **sitio público** para buscar propiedades y contactar a la empresa, y un
**panel de administración** para gestionar propiedades, noticias, personal y las consultas que llegan desde el sitio.

## Funcionalidades

**Sitio público**
- Inicio con carrusel, buscador por barrio y tipo, propiedades destacadas, servicios y últimas noticias.
- Listado de propiedades con filtros y botón **"Cargar más"** (8 por carga, desde una API JSON).
- Ficha de cada propiedad con galería, características y formulario de consulta al agente asignado.
- Solicitud de tasación, postulación para sumarse al equipo y formulario de contacto.
- Blog de noticias, Nosotros, Preguntas frecuentes, Política de privacidad y Términos (con índice lateral).
- Botón flotante de WhatsApp.

**Panel de administración** (`/admin/`)
- Inicio con tarjetas de resumen que abren cada lista filtrada por lo nuevo.
- Consultas, tasaciones y postulaciones con buscador, pestañas por estado y cambio de estado.
- Alta, modificación y baja de propiedades (con varias imágenes) y de noticias.
- Alta y modificación del personal, activación/desactivación y asignación de rol.

## Stack

| Capa | Tecnología |
|---|---|
| Frontend | HTML5, CSS3 (variables), JavaScript, Swiper, Font Awesome |
| Backend | PHP 8.x (PDO, sesiones, `password_hash`) |
| Base de datos | MySQL 8 / MariaDB 10.4+ (InnoDB, claves foráneas, transacciones) |
| Tests | Jest + jsdom (cliente) y script PHP sin dependencias (servidor) |
| Entorno local | Docker Compose o XAMPP |

## Cómo levantarlo

Hay dos formas. **Docker** es la más simple porque no requiere instalar PHP ni MySQL.

### Opción A – Docker (recomendada)

Requisito: [Docker Desktop](https://www.docker.com/products/docker-desktop/).

```bash
# 1. Levantar la base y el servidor web (la primera vez construye la imagen y carga la base)
docker compose up -d --build

# 2. Crear el administrador (clave: 8+ caracteres con letras y números)
docker compose exec web php database/crear_usuario.php "Nombre" "Apellido" admin@viviendasub.com "Clave2026" ADMIN

# 3. (Opcional) Cargar datos de demostración: consultas, tasaciones y postulaciones en todos los estados
docker compose exec db sh -c "mariadb -uroot -proot --default-character-set=utf8mb4 < /sql/04_datos_demo.sql"
```

| Qué | URL |
|---|---|
| Sitio público | http://localhost:8080/ |
| Panel | http://localhost:8080/admin/ |
| Visor de la base (Adminer) | http://localhost:8081/ |

**Ver la base de datos:** en http://localhost:8081/ ingresar con Sistema **MySQL**, Servidor **db**, Usuario
**viviendas**, Contraseña **viviendas** y Base de datos **viviendas_ub**. Se ven las 9 tablas con sus datos y la
estructura; "Esquema de base de datos" muestra el diagrama de relaciones. Sirve para comprobar que lo que se
agrega, modifica o da de baja desde el sitio o el panel queda guardado (recargar la tabla con F5). Usa un tema con
los colores del proyecto (`docker/adminer/adminer.css`) y el diagrama se abre ordenado según las relaciones
(`docker/adminer/esquema.php`); las tablas del diagrama se pueden arrastrar.

La base se crea sola con `01_schema.sql`, `02_datos_iniciales.sql` y `03_noticias_contenido.sql` la primera vez.
Docker usa `config/config.example.php` con los datos de conexión de `docker-compose.yml`, así que **no hace falta
crear `config/config.php`**.

Comandos útiles:

```bash
docker compose stop              # apagar (los datos se conservan)
docker compose start             # volver a encender
docker compose logs -f web       # ver errores de PHP/Apache
docker compose down -v           # borrar todo, incluida la base (al volver a levantar se recrea desde cero)
```

Si los puertos 8080, 8081 o 3308 están ocupados, se pueden cambiar al levantar:
`WEB_PUERTO=8090 ADMINER_PUERTO=8091 DB_PUERTO=3309 docker compose up -d` (en PowerShell: `$env:WEB_PUERTO=8090; docker compose up -d`).
El puerto de la base (por defecto `127.0.0.1:3308`, usuario `viviendas` / clave `viviendas`) sirve para conectarse
con DBeaver o MySQL Workbench.

### Opción B – XAMPP

1. Iniciar **Apache** y **MySQL** desde el panel de XAMPP.
2. Clonar el repositorio en `C:\xampp\htdocs\LABORATORIO`.
3. Crear la base: en phpMyAdmin > Importar, cargar en este orden `database/01_schema.sql`,
   `database/02_datos_iniciales.sql` y `database/03_noticias_contenido.sql` (codificación UTF-8).
   Opcional: `database/04_datos_demo.sql`.
4. Copiar `config/config.example.php` como `config/config.php` y completar la clave de MySQL si tiene.
5. Verificar que `extension=fileinfo` esté habilitada en `C:\xampp\php\php.ini` (para subir imágenes).
6. Crear el administrador:
   `C:\xampp\php\php.exe database\crear_usuario.php "Nombre" "Apellido" admin@viviendasub.com "Clave2026" ADMIN`
7. Abrir http://localhost/LABORATORIO/ (sitio) y http://localhost/LABORATORIO/admin/ (panel).

> `01_schema.sql` hace `DROP DATABASE IF EXISTS viviendas_ub`: borra la base completa si ya existía.

### Probar el rol Vendedor

Panel → Personal → editar un agente → asignarle una clave → cerrar sesión e ingresar con ese agente.
Debe ver solo sus propiedades y consultas, sin los menús Noticias ni Personal.

## Roles

| Funcionalidad | ADMIN | VENDEDOR |
|---|---|---|
| ABM propiedades | Todas | Solo propias |
| Consultas por propiedad | Todas | Solo asignadas |
| Solicitudes de tasación / postulaciones | Sí | No |
| ABM noticias | Sí | No |
| ABM personal (usuarios) | Sí | No |

## Seguridad

- Consultas SQL siempre preparadas (PDO) e integridad referencial con claves foráneas.
- Claves con `password_hash`; la sesión se valida contra la base en cada pedido (un usuario desactivado pierde el acceso al instante).
- Token CSRF en todos los formularios y campo trampa (honeypot) contra envíos automáticos.
- Validación de datos en el cliente y en el servidor; salida escapada contra XSS.
- Imágenes validadas por contenido real (no por extensión), con nombre aleatorio y sin ejecución de scripts en `uploads/`.
- `.htaccess` que bloquea el acceso por URL a `config/`, `includes/`, `database/`, `tests/` y `docker/`.

## Tests

```bash
npm install
npm test                                              # Jest: 37 tests
docker compose exec web php tests/php/validador_test.php   # PHP: 15 OK
```

Con XAMPP, la prueba PHP se corre con `C:\xampp\php\php.exe tests\php\validador_test.php`.

## Estructura

```
admin/          Panel de administración (login, consultas, ABM de propiedades, noticias y personal)
api/            Endpoint JSON para "Cargar más" propiedades
config/         Configuración (config.php NO se versiona; se parte de config.example.php)
css/            Variables de diseño, estilos del sitio y del panel
database/       Scripts SQL (esquema, datos iniciales, noticias, demo) y alta de usuarios por CLI
doc/            Documentación del proyecto y evidencias de pruebas
docker/         Imagen de PHP/Apache, carga de la base y tema del visor Adminer para Docker
imagenes/       Imágenes fijas del sitio
includes/       Conexión, autenticación, validaciones, repositorio de datos y plantillas comunes
js/             Validaciones, "Cargar más", panel y comportamiento general
tests/          Pruebas Jest (cliente) y PHP (servidor)
uploads/        Imágenes subidas desde el panel (no se versionan)
*.php           Páginas del sitio público
```

## Documentación

| Documento | Contenido |
|---|---|
| [doc/casosDeUso.docx](doc/casosDeUso.docx) | Casos de uso y diagrama |
| [doc/diagramasDeFlujo.docx](doc/diagramasDeFlujo.docx) | Diagramas de flujo de los procesos principales |
| [doc/modeloDeDatos.docx](doc/modeloDeDatos.docx) | Modelo de datos y diccionario de tablas |
| [doc/casosDePrueba.docx](doc/casosDePrueba.docx) | Casos de prueba y resultados |
| [doc/04_casos_de_prueba.md](doc/04_casos_de_prueba.md) | Resultados de ejecución con enlaces a las [evidencias](doc/evidencias/) |
| [doc/manualDeUsuario.docx](doc/manualDeUsuario.docx) | Manual de usuario del sitio y del panel |

## Problemas frecuentes

| Síntoma | Causa / solución |
|---|---|
| "Falta config/config.php" | (XAMPP) No se copió `config.example.php` como `config.php`. |
| "Error de conexión a la base de datos" | MySQL apagado, base no creada o clave incorrecta en `config.php`. En Docker: `docker compose ps` y `docker compose logs db`. |
| Acentos como `Ã³` | La importación no fue en UTF-8: repetir con `--default-character-set=utf8mb4`. |
| Error al subir imágenes | (XAMPP) `fileinfo` deshabilitada en `php.ini`. |
| Docker: "port is already allocated" | Cambiar `WEB_PUERTO`, `ADMINER_PUERTO` o `DB_PUERTO` (ver Opción A). |
| Adminer no muestra un cambio recién hecho | Recargar la página (F5). En la lista de tablas, la columna "Registros" es aproximada (`~`): para el número exacto, abrir la tabla. |
| Docker: la base no tiene los cambios de los `.sql` | Los scripts solo corren con la base vacía: `docker compose down -v` y volver a levantar. |
