-- =====================================================================
-- Viviendas UB - Datos de demostración (OPCIONAL)
-- Llena el panel con propiedades en todos los estados y con consultas, tasaciones y postulaciones
-- en cada estado, para probar el buscador y las pestañas de filtro.
-- Ejecutar después de 01_schema.sql, 02_datos_iniciales.sql y 03_noticias_contenido.sql.
-- No forma parte de la base de los casos de prueba (CP-01 a CP-24 se ejecutan sin este script).
-- Agentes de 02_datos_iniciales: 1 Herrera, 2 Fernandez, 3 Luccia, 4 Paez, 5 Monzon, 6 Pepe, 7 Montt, 8 Bota.
-- Para probar el panel de vendedor, Mia Luccia (3) y Brisa Paez (4) tienen propiedades y consultas en varios estados.
-- =====================================================================
USE viviendas_ub;

START TRANSACTION;

-- ---------------------------------------------------------------------
-- Propiedades en estados distintos de DISPONIBLE (y dos disponibles más)
-- ---------------------------------------------------------------------
INSERT INTO propiedades (tipo_propiedad_id, agente_id, titulo, descripcion, operacion, precio, moneda, direccion, barrio, ciudad, ambientes, dormitorios, banos, superficie_m2, destacada, estado) VALUES
 (1, 4, 'Departamento luminoso en Núñez',      'Tres ambientes con balcón al frente.',             'VENTA',    148000, 'USD', 'Av. del Libertador 7650', 'Nuñez',        'CABA', 3, 2, 1,  72, 0, 'RESERVADA');
SET @p_reservada = LAST_INSERT_ID();
INSERT INTO propiedades (tipo_propiedad_id, agente_id, titulo, descripcion, operacion, precio, moneda, direccion, barrio, ciudad, ambientes, dormitorios, banos, superficie_m2, destacada, estado) VALUES
 (3, 3, 'PH reciclado en Coghlan',             'PH de tres ambientes con patio y terraza propia.', 'VENTA',    165000, 'USD', 'Estomba 2840',            'Coghlan',      'CABA', 3, 2, 1,  95, 0, 'VENDIDA');
SET @p_vendida = LAST_INSERT_ID();
INSERT INTO propiedades (tipo_propiedad_id, agente_id, titulo, descripcion, operacion, precio, moneda, direccion, barrio, ciudad, ambientes, dormitorios, banos, superficie_m2, destacada, estado) VALUES
 (1, 3, 'Monoambiente en Recoleta',            'Monoambiente amueblado cerca de Plaza Francia.',   'ALQUILER', 420000, 'ARS', 'Ayacucho 1720',           'Recoleta',     'CABA', 1, 0, 1,  32, 0, 'ALQUILADA');
SET @p_alquilada = LAST_INSERT_ID();
INSERT INTO propiedades (tipo_propiedad_id, agente_id, titulo, descripcion, operacion, precio, moneda, direccion, barrio, ciudad, ambientes, dormitorios, banos, superficie_m2, destacada, estado) VALUES
 (2, 1, 'Casa con jardín en Villa Urquiza',    'Casa de cuatro ambientes con jardín y parrilla.',  'VENTA',    255000, 'USD', 'Triunvirato 4910',        'Villa Urquiza','CABA', 4, 3, 2, 190, 0, 'DISPONIBLE');
SET @p_casa = LAST_INSERT_ID();
INSERT INTO propiedades (tipo_propiedad_id, agente_id, titulo, descripcion, operacion, precio, moneda, direccion, barrio, ciudad, ambientes, dormitorios, banos, superficie_m2, destacada, estado) VALUES
 (1, 4, 'Dos ambientes en Belgrano R',         'Dos ambientes a estrenar con amenities.',          'ALQUILER', 560000, 'ARS', 'Echeverría 3350',         'Belgrano',     'CABA', 2, 1, 1,  48, 0, 'DISPONIBLE');
SET @p_belgrano = LAST_INSERT_ID();

INSERT INTO propiedad_imagenes (propiedad_id, ruta, es_portada, orden) VALUES
 (@p_reservada, 'imagenes/propiedad-2.jpg', 1, 0),
 (@p_vendida,   'imagenes/propiedad-5.jpg', 1, 0),
 (@p_alquilada, 'imagenes/propiedad-7.jpg', 1, 0),
 (@p_casa,      'imagenes/propiedad-1.jpg', 1, 0),
 (@p_belgrano,  'imagenes/propiedad-3.jpg', 1, 0);

-- ---------------------------------------------------------------------
-- Consultas por propiedad: los cuatro estados, con el agente de cada propiedad
-- (Mia Luccia y Brisa Paez tienen consultas en varios estados para probar la vista del vendedor)
-- ---------------------------------------------------------------------
INSERT INTO consultas (propiedad_id, agente_id, nombre, email, telefono, mensaje, preferencia_contacto, estado, creado_en) VALUES
 (4,            4, 'Lucía Fernández',  'lucia.fernandez@example.com',  '1145678912', '¿La propiedad tiene cochera? Me interesa coordinar una visita.',            'WHATSAPP', 'NUEVA',      NOW() - INTERVAL 2 HOUR),
 (@p_belgrano,  4, 'Martín Gómez',     'mgomez.arq@example.com',       '1144029912', '¿Aceptan mascotas? Somos una pareja con un perro chico.',                   'EMAIL',    'NUEVA',      NOW() - INTERVAL 5 HOUR),
 (3,            3, 'Sofía Navarro',    'sofia.navarro@example.com',    '1163218840', '¿Es apta crédito hipotecario? Quisiéramos visitarla el sábado.',            'TELEFONO', 'NUEVA',      NOW() - INTERVAL 1 DAY),
 (2,            2, 'Julián Ríos',      'julian.rios@example.com',      '1152347788', 'Hola, ¿cuánto pagan de expensas?',                                           'EMAIL',    'EN_PROCESO', NOW() - INTERVAL 2 DAY),
 (@p_reservada, 4, 'Carla Benítez',    'carla.benitez@example.com',    '1139981204', 'Quiero saber si la reserva sigue vigente o si se puede ofertar.',            'WHATSAPP', 'EN_PROCESO', NOW() - INTERVAL 3 DAY),
 (1,            1, 'Ramiro Ortega',    'ramiro.ortega@example.com',    '1167452210', '¿Se puede ver el fin de semana? Busco algo amplio para la familia.',         'TELEFONO', 'RESPONDIDA', NOW() - INTERVAL 4 DAY),
 (6,            6, 'Valentina Paz',    'valentina.paz@example.com',    '1150127733', 'Me interesa, ¿el precio es negociable?',                                     'EMAIL',    'RESPONDIDA', NOW() - INTERVAL 6 DAY),
 (@p_vendida,   3, 'Hernán Suárez',    'hernan.suarez@example.com',    '1148810056', '¿Sigue disponible? Puedo pagar de contado.',                                  'EMAIL',    'CERRADA',    NOW() - INTERVAL 9 DAY),
 (8,            8, 'Paula Medina',     'paula.medina@example.com',     '1158823390', 'Consulto por la superficie del balcón.',                                      'WHATSAPP', 'CERRADA',    NOW() - INTERVAL 12 DAY),
 (9,            3, 'Diego Salvatierra','diego.salvatierra@example.com','1153348820', '¿El alquiler incluye expensas? Busco mudarme el mes que viene.',             'WHATSAPP', 'EN_PROCESO', NOW() - INTERVAL 2 DAY),
 (@p_alquilada, 3, 'Natalia Correa',   'natalia.correa@example.com',   '1146617730', '¿Cuándo vuelve a estar disponible el monoambiente?',                         'EMAIL',    'RESPONDIDA', NOW() - INTERVAL 7 DAY);

-- ---------------------------------------------------------------------
-- Solicitudes de tasación: los cuatro estados
-- ---------------------------------------------------------------------
INSERT INTO solicitudes_tasacion (tipo_propiedad_id, nombre, email, telefono, direccion_inmueble, superficie_aprox, mensaje, estado, creado_en) VALUES
 (1, 'Andrea López',     'andrea.lopez@example.com',     '1146623301', 'Av. Cabildo 1850, Belgrano',     64,  'Quiero vender en los próximos meses.',              'NUEVA',      NOW() - INTERVAL 3 HOUR),
 (2, 'Gustavo Pereyra',  'gustavo.pereyra@example.com',  '1155093318', 'Habana 3240, Villa Devoto',      210, 'Casa heredada, necesitamos saber el valor.',        'EN_PROCESO', NOW() - INTERVAL 2 DAY),
 (3, 'Romina Castro',    'romina.castro@example.com',    '1143320975', 'Gorriti 4410, Palermo',          88,  NULL,                                                'RESPONDIDA', NOW() - INTERVAL 5 DAY),
 (5, 'Federico Álvarez', 'federico.alvarez@example.com', '1149987412', 'Av. Corrientes 1250, San Nicolás', 120, 'Oficina en planta alta, evaluamos alquilar o vender.', 'CERRADA', NOW() - INTERVAL 10 DAY);

-- ---------------------------------------------------------------------
-- Postulaciones de agentes: los cinco estados
-- ---------------------------------------------------------------------
INSERT INTO postulaciones (nombre, apellido, email, telefono, anios_experiencia, zona_interes, linkedin_url, mensaje, estado, creado_en) VALUES
 ('Camila',  'Ruiz',     'camila.ruiz@example.com',     '1151234098', 2, 'Palermo',       NULL,                                  'Trabajé dos años en atención al cliente en una inmobiliaria.', 'EN_REVISION', NOW() - INTERVAL 1 DAY),
 ('Tomás',   'Acosta',   'tomas.acosta@example.com',    '1162238845', 6, 'Belgrano',      'https://www.linkedin.com/in/ejemplo', 'Corredor inmobiliario matriculado, busco sumarme a un equipo.', 'ENTREVISTA',  NOW() - INTERVAL 3 DAY),
 ('Agustina','Molina',   'agustina.molina@example.com', '1147756621', 0, 'Caballito',     NULL,                                  'Estudio martillero y corredor público, quiero empezar.',       'DESCARTADA',  NOW() - INTERVAL 8 DAY),
 ('Bruno',   'Herrera',  'bruno.herrera@example.com',   '1154408712', 4, 'Villa Urquiza', NULL,                                  'Experiencia en ventas de usados en zona norte.',               'INCORPORADA', NOW() - INTERVAL 15 DAY);

-- ---------------------------------------------------------------------
-- Noticia en borrador, con autor (para las pestañas y la búsqueda por autor de Noticias).
-- El autor es el administrador creado con crear_usuario.php; si no existe, queda sin autor.
-- ---------------------------------------------------------------------
INSERT INTO noticias (autor_id, titulo, resumen, contenido, imagen, fecha_publicacion, publicada) VALUES
 ((SELECT id FROM usuarios WHERE email = 'admin@viviendasub.com'),
  'Cómo preparar tu propiedad para la venta',
  'Pequeños arreglos y una buena presentación ayudan a vender más rápido y a mejor precio.',
  '## Antes de publicar\n\nOrdená los ambientes, reparé lo pendiente y dejá entrar la luz natural para las fotos.\n\n## Documentación\n\n- Escritura\n- Planos aprobados\n- Libre deuda de expensas e impuestos',
  'imagenes/blog2.jpg', CURDATE(), 0);

COMMIT;
