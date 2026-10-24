# Viviendas UB - Sitio inmobiliario Full Stack (PHP + MySQL)

Proyecto de Prácticas de Laboratorio - Tecnicatura en Programación de Computadoras (UB).

## Stack
- Frontend: HTML5, CSS3, JavaScript (Swiper, Font Awesome)
- Backend: PHP 8.x (PDO, sesiones, CSRF, password_hash)
- Base de datos: MySQL 8 / MariaDB 10.4+ (InnoDB, claves foráneas, transacciones ACID)
- Tests: Jest + jsdom (cliente) y script PHP sin dependencias (servidor)

## Instalación local (XAMPP)
1. Iniciar Apache y MySQL desde el panel de XAMPP.
2. Clonar el repositorio en `C:\xampp\htdocs\LABORATORIO`.
3. Crear la base: importar `database/01_schema.sql` y luego `database/02_datos_iniciales.sql` (phpMyAdmin > Importar).
4. `python tools/extraer_noticias.py` y luego importar `database/03_noticias_contenido.sql`.
5. Copiar `config/config.example.php` como `config/config.php` y completar credenciales.
6. Crear el administrador:
   `C:\xampp\php\php.exe database\crear_usuario.php "Nombre" "Apellido" admin@viviendasub.com "Clave2026" ADMIN`
7. Abrir http://localhost/LABORATORIO/ (sitio) y http://localhost/LABORATORIO/admin/ (panel).

## Roles
| Funcionalidad | ADMIN | VENDEDOR |
|---|---|---|
| ABM propiedades | Todas | Solo propias |
| Consultas por propiedad | Todas | Solo asignadas |
| Solicitudes de tasación / postulaciones | Sí | No |
| ABM noticias | Sí | No |
| ABM personal (usuarios) | Sí | No |

## Tests
- `npm install` y `npm test`
- `C:\xampp\php\php.exe tests\php\validador_test.php`

## Estructura
```
admin/      Panel (login, ABM propiedades, noticias, personal, consultas)
api/        Endpoint JSON (cargar más propiedades)
config/     Configuración (config.php NO se versiona)
css/        Estilos del sitio y del panel
database/   Scripts SQL y alta de usuarios por CLI
includes/   Conexión, seguridad, validaciones, repositorio y plantillas
js/         Validaciones, cargar más y comportamiento general
tests/      Pruebas Jest y PHP
tools/      Scripts Python de migración del sitio estático
uploads/    Imágenes subidas desde el panel (no se versionan)
```
