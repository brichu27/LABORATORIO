-- =====================================================================
-- Viviendas UB - Datos iniciales (migrados del sitio estatico)
-- Archivo: database/02_datos_iniciales.sql  (ejecutar despues de 01_schema.sql)
-- Los agentes se cargan SIN clave ('!'): no pueden ingresar al panel
-- hasta que el Administrador les asigne una desde "Personal".
-- Emails con alias "+" para que todos lleguen a la casilla real del proyecto.
-- =====================================================================
USE viviendas_ub;
SET NAMES utf8mb4;

START TRANSACTION;

INSERT INTO roles (id, nombre, descripcion) VALUES
 (1, 'ADMIN',    'Administrador: gestiona personal, noticias, propiedades y todas las solicitudes'),
 (2, 'VENDEDOR', 'Vendedor/Agente: gestiona sus propiedades y las consultas asignadas');

INSERT INTO tipos_propiedad (id, nombre) VALUES
 (1, 'Departamento'), (2, 'Casa'), (3, 'PH'), (4, 'Terreno'), (5, 'Oficina'), (6, 'Local comercial');

-- Agentes publicados en nosotros.html (telefono = el que se mostraba en pantalla)
INSERT INTO usuarios (id, rol_id, nombre, apellido, email, telefono, cargo, foto, password_hash, activo, visible_web) VALUES
 (1,  2, 'Tomas',    'Herrera',   'viviendasub+therrera@gmail.com',   '1130662123', 'Agente inmobiliario', 'imagenes/agente1-modified.jpg',  '!', 1, 1),
 (2,  2, 'Nicolas',  'Fernandez', 'viviendasub+nfernandez@gmail.com', '1133770817', 'Agente inmobiliario', 'imagenes/agente2-modified.jpeg', '!', 1, 1),
 (3,  2, 'Mia',      'Luccia',    'viviendasub+mluccia@gmail.com',    '1134032543', 'Agente inmobiliario', 'imagenes/agente3-modified.jpg',  '!', 1, 1),
 (4,  2, 'Brisa',    'Paez',      'viviendasub+bpaez@gmail.com',      '1152591813', 'Agente inmobiliario', 'imagenes/agente4-modified.jpg',  '!', 1, 1),
 (5,  2, 'Patricio', 'Monzon',    'viviendasub+pmonzon@gmail.com',    '1166572558', 'Agente inmobiliario', 'imagenes/agente5-modified.jpg',  '!', 1, 1),
 (6,  2, 'Marisa',   'Pepe',      'viviendasub+mpepe@gmail.com',      '1165381394', 'Agente inmobiliario', 'imagenes/agente6-modified.png',  '!', 1, 1),
 (7,  2, 'Diego',    'Montt',     'viviendasub+dmontt@gmail.com',     '1163548456', 'Agente inmobiliario', 'imagenes/agente7-modified.png',  '!', 1, 1),
 (8,  2, 'Zoe',      'Bota',      'viviendasub+zbota@gmail.com',      '1165828644', 'Agente inmobiliario', 'imagenes/agente8-modified.jpg',  '!', 1, 1),
 (9,  2, 'Ciro',     'Sirio',     'viviendasub+csirio@gmail.com',     '1189015632', 'Agente inmobiliario', 'imagenes/agente9-modified.png',  '!', 1, 1),
 (10, 2, 'Alicia',   'Montari',   'viviendasub+amontari@gmail.com',   '1160559335', 'Agente inmobiliario', 'imagenes/agente10-modified.jpg', '!', 1, 1);

-- Propiedades publicadas en index.html / propiedades.html
-- (tipo = Departamento y operacion = VENTA por defecto: revisar y ajustar desde el panel)
INSERT INTO propiedades (id, tipo_propiedad_id, agente_id, titulo, descripcion, operacion, precio, moneda, direccion, barrio, ciudad, ambientes, dormitorios, banos, superficie_m2, destacada, estado) VALUES
 (1, 1, 1, 'Amplia propiedad en Caballito',     'Unidad de 5 habitaciones y 3 baños sobre Av. Rivadavia.', 'VENTA', 220000, 'USD', 'Rivadavia 5954',  'Caballito',    'CABA', 6, 5, 3, 280, 1, 'DISPONIBLE'),
 (2, 1, 2, 'Departamento en Palermo',           'Departamento de 2 habitaciones en Palermo.',             'VENTA',  90000, 'USD', 'Gurruchaga 2172', 'Palermo',      'CABA', 3, 2, 1,  78, 1, 'DISPONIBLE'),
 (3, 1, 3, 'Departamento en Belgrano',          'Departamento de 2 habitaciones y 2 baños sobre Cabildo.', 'VENTA', 175000, 'USD', 'Cabildo 2502',    'Belgrano',     'CABA', 3, 2, 2, 123, 1, 'DISPONIBLE'),
 (4, 1, 4, 'Propiedad de 4 habitaciones en Nuñez', 'Propiedad de 4 habitaciones y 2 baños.',               'VENTA', 205000, 'USD', 'Guayra 1792',     'Nuñez',        'CABA', 5, 4, 2, 195, 1, 'DISPONIBLE'),
 (5, 1, 5, 'Departamento en Saavedra',          'Departamento de 2 habitaciones.',                        'VENTA',  75000, 'USD', 'Besares 3701',    'Saavedra',     'CABA', 3, 2, 1, 113, 0, 'DISPONIBLE'),
 (6, 1, 6, 'Departamento en Palermo',           'Departamento de 3 habitaciones.',                        'VENTA', 190000, 'USD', 'Malabia 3433',    'Palermo',      'CABA', 4, 3, 1, 117, 0, 'DISPONIBLE'),
 (7, 1, 7, 'Departamento de 2 ambientes en Villa Crespo', 'Unidad de 1 habitación.',                      'VENTA',  80000, 'USD', 'Darwin 416',      'Villa Crespo', 'CABA', 2, 1, 1,  53, 0, 'DISPONIBLE'),
 (8, 1, 8, 'Propiedad de 4 habitaciones en Belgrano', 'Propiedad de 4 habitaciones y 2 baños.',           'VENTA', 125000, 'USD', 'Moldes 2545',     'Belgrano',     'CABA', 5, 4, 2, 180, 0, 'DISPONIBLE');

INSERT INTO propiedad_imagenes (propiedad_id, ruta, es_portada, orden) VALUES
 (1, 'imagenes/propiedad-1.jpg', 1, 0), (2, 'imagenes/propiedad-2.jpg', 1, 0),
 (3, 'imagenes/propiedad-3.jpg', 1, 0), (4, 'imagenes/propiedad-4.jpg', 1, 0),
 (5, 'imagenes/propiedad-5.jpg', 1, 0), (6, 'imagenes/propiedad-6.jpg', 1, 0),
 (7, 'imagenes/propiedad-7.jpg', 1, 0), (8, 'imagenes/propiedad-8.jpg', 1, 0);

-- Noticias del blog. El contenido completo se migra con tools/extraer_noticias.py
INSERT INTO noticias (id, autor_id, titulo, resumen, contenido, imagen, fecha_publicacion, publicada) VALUES
 (1, NULL, '5 Técnicas de negociación', 'Te mostramos estrategias y métodos claves para alcanzar acuerdos exitosos en tratos.', 'Contenido pendiente de migración.', 'imagenes/blog1.jpg', '2025-03-21', 1),
 (2, NULL, 'Ser agente inmobiliario', '¿Querés saber qué conlleva ser un agente inmobiliario? Aquí te lo explicamos.', 'Contenido pendiente de migración.', 'imagenes/blog2.jpg', '2025-03-31', 1),
 (3, NULL, '¿Cuál es el valor de mi casa?', 'Brindamos 7 métodos y factores para determinar el precio justo de una propiedad.', 'Contenido pendiente de migración.', 'imagenes/blog3.jpg', '2025-04-18', 1),
 (4, NULL, 'Un buen ambiente laboral', '¿Cómo conseguir un entorno de trabajo positivo, colaborativo y que fomente la productividad?', 'Contenido pendiente de migración.', 'imagenes/blog4.jpg', '2025-04-21', 1);

COMMIT;
