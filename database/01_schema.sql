-- =====================================================================
-- Viviendas UB - Esquema relacional (MySQL 8 / MariaDB 10.4+)
-- Archivo: database/01_schema.sql
-- Motor InnoDB: transacciones ACID + integridad referencial (FK).
-- ATENCION: el DROP elimina la base completa. Usar solo en entorno local.
-- =====================================================================
DROP DATABASE IF EXISTS viviendas_ub;
CREATE DATABASE viviendas_ub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE viviendas_ub;

-- ---------------------------------------------------------------------
-- ROLES: Administrador y Vendedor (agente)
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id            TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(20)      NOT NULL,
    descripcion   VARCHAR(150)     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- USUARIOS: personal de la inmobiliaria (ABM de personal)
-- password_hash = '!' => usuario sin acceso al panel (sin clave asignada)
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    rol_id          TINYINT UNSIGNED NOT NULL,
    nombre          VARCHAR(60)      NOT NULL,
    apellido        VARCHAR(60)      NOT NULL,
    email           VARCHAR(120)     NOT NULL,
    telefono        VARCHAR(20)      NULL,
    cargo           VARCHAR(60)      NOT NULL DEFAULT 'Agente inmobiliario',
    foto            VARCHAR(255)     NULL,
    password_hash   VARCHAR(255)     NOT NULL,
    activo          TINYINT(1)       NOT NULL DEFAULT 1,
    visible_web     TINYINT(1)       NOT NULL DEFAULT 1,
    ultimo_acceso   DATETIME         NULL,
    creado_en       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    KEY ix_usuarios_rol (rol_id),
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TIPOS DE PROPIEDAD (catalogo)
-- ---------------------------------------------------------------------
CREATE TABLE tipos_propiedad (
    id      TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre  VARCHAR(40)      NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tipos_propiedad_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- PROPIEDADES (ABM). Baja logica: estado = 'INACTIVA'
-- ---------------------------------------------------------------------
CREATE TABLE propiedades (
    id                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    tipo_propiedad_id  TINYINT UNSIGNED NOT NULL,
    agente_id          INT UNSIGNED     NOT NULL,
    creado_por         INT UNSIGNED     NULL,
    titulo             VARCHAR(120)     NOT NULL,
    descripcion        TEXT             NULL,
    operacion          ENUM('VENTA','ALQUILER') NOT NULL,
    precio             DECIMAL(14,2)    NOT NULL,
    moneda             ENUM('USD','ARS') NOT NULL DEFAULT 'USD',
    direccion          VARCHAR(150)     NOT NULL,
    barrio             VARCHAR(60)      NOT NULL,
    ciudad             VARCHAR(60)      NOT NULL DEFAULT 'CABA',
    ambientes          TINYINT UNSIGNED NOT NULL DEFAULT 1,
    dormitorios        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    banos              TINYINT UNSIGNED NOT NULL DEFAULT 1,
    superficie_m2      DECIMAL(8,2)     NOT NULL,
    destacada          TINYINT(1)       NOT NULL DEFAULT 0,
    estado             ENUM('DISPONIBLE','RESERVADA','VENDIDA','ALQUILADA','INACTIVA') NOT NULL DEFAULT 'DISPONIBLE',
    creado_en          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_propiedades_estado (estado, destacada),
    KEY ix_propiedades_barrio (barrio),
    KEY ix_propiedades_agente (agente_id),
    KEY ix_propiedades_tipo (tipo_propiedad_id),
    CONSTRAINT fk_propiedades_tipo    FOREIGN KEY (tipo_propiedad_id) REFERENCES tipos_propiedad (id) ON DELETE RESTRICT,
    CONSTRAINT fk_propiedades_agente  FOREIGN KEY (agente_id)  REFERENCES usuarios (id) ON DELETE RESTRICT,
    CONSTRAINT fk_propiedades_creador FOREIGN KEY (creado_por) REFERENCES usuarios (id) ON DELETE SET NULL,
    CONSTRAINT ck_propiedades_precio     CHECK (precio > 0),
    CONSTRAINT ck_propiedades_superficie CHECK (superficie_m2 > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- IMAGENES DE PROPIEDAD (1:N). Se borran en cascada con la propiedad.
-- ---------------------------------------------------------------------
CREATE TABLE propiedad_imagenes (
    id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    propiedad_id  INT UNSIGNED      NOT NULL,
    ruta          VARCHAR(255)      NOT NULL,
    es_portada    TINYINT(1)        NOT NULL DEFAULT 0,
    orden         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    creado_en     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_propiedad_imagenes_prop (propiedad_id, es_portada, orden),
    CONSTRAINT fk_propiedad_imagenes_prop FOREIGN KEY (propiedad_id) REFERENCES propiedades (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- NOTICIAS / BLOG (ABM)
-- ---------------------------------------------------------------------
CREATE TABLE noticias (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    autor_id           INT UNSIGNED NULL,
    titulo             VARCHAR(150) NOT NULL,
    resumen            VARCHAR(300) NOT NULL,
    contenido          MEDIUMTEXT   NOT NULL,
    imagen             VARCHAR(255) NULL,
    fecha_publicacion  DATE         NOT NULL,
    publicada          TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_noticias_publicacion (publicada, fecha_publicacion),
    CONSTRAINT fk_noticias_autor FOREIGN KEY (autor_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- CONSULTAS POR PROPIEDAD (formulario especifico de propiedad)
-- ---------------------------------------------------------------------
CREATE TABLE consultas (
    id                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    propiedad_id          INT UNSIGNED NOT NULL,
    agente_id             INT UNSIGNED NULL,
    nombre                VARCHAR(80)  NOT NULL,
    email                 VARCHAR(120) NOT NULL,
    telefono              VARCHAR(20)  NOT NULL,
    mensaje               VARCHAR(1000) NOT NULL,
    preferencia_contacto  ENUM('EMAIL','TELEFONO','WHATSAPP') NOT NULL DEFAULT 'EMAIL',
    estado                ENUM('NUEVA','EN_PROCESO','RESPONDIDA','CERRADA') NOT NULL DEFAULT 'NUEVA',
    creado_en             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_consultas_agente_estado (agente_id, estado),
    KEY ix_consultas_propiedad (propiedad_id),
    CONSTRAINT fk_consultas_propiedad FOREIGN KEY (propiedad_id) REFERENCES propiedades (id) ON DELETE RESTRICT,
    CONSTRAINT fk_consultas_agente    FOREIGN KEY (agente_id)    REFERENCES usuarios (id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- SOLICITUDES DE TASACION (formulario especifico de tasaciones)
-- ---------------------------------------------------------------------
CREATE TABLE solicitudes_tasacion (
    id                  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    tipo_propiedad_id   TINYINT UNSIGNED NOT NULL,
    nombre              VARCHAR(80)      NOT NULL,
    email               VARCHAR(120)     NOT NULL,
    telefono            VARCHAR(20)      NOT NULL,
    direccion_inmueble  VARCHAR(150)     NOT NULL,
    superficie_aprox    DECIMAL(8,2)     NULL,
    mensaje             VARCHAR(1000)    NULL,
    estado              ENUM('NUEVA','EN_PROCESO','RESPONDIDA','CERRADA') NOT NULL DEFAULT 'NUEVA',
    creado_en           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_tasaciones_estado (estado),
    CONSTRAINT fk_tasaciones_tipo FOREIGN KEY (tipo_propiedad_id) REFERENCES tipos_propiedad (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- POSTULACIONES DE AGENTES (formulario especifico "Unirse")
-- ---------------------------------------------------------------------
CREATE TABLE postulaciones (
    id                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre             VARCHAR(60)      NOT NULL,
    apellido           VARCHAR(60)      NOT NULL,
    email              VARCHAR(120)     NOT NULL,
    telefono           VARCHAR(20)      NOT NULL,
    anios_experiencia  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    zona_interes       VARCHAR(60)      NOT NULL,
    linkedin_url       VARCHAR(255)     NULL,
    mensaje            VARCHAR(1500)    NOT NULL,
    estado             ENUM('RECIBIDA','EN_REVISION','ENTREVISTA','DESCARTADA','INCORPORADA') NOT NULL DEFAULT 'RECIBIDA',
    creado_en          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_postulaciones_estado (estado),
    CONSTRAINT ck_postulaciones_experiencia CHECK (anios_experiencia <= 60)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
