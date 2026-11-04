<?php
/**
 * Copiar este archivo como config/config.php y completar los datos.
 * config.php NO se versiona (ver .gitignore): contiene credenciales.
 *
 * Los datos de conexion se pueden pasar por variables de entorno (DB_HOST, DB_PORT, ...).
 * Docker las define en docker-compose.yml; sin ellas se usan los valores de XAMPP.
 */
return [
    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => (int) (getenv('DB_PORT') ?: 3306),
        'nombre'  => getenv('DB_NAME') ?: 'viviendas_ub',
        'usuario' => getenv('DB_USER') ?: 'root',   // XAMPP por defecto: root sin clave. En produccion usar un usuario dedicado.
        'clave'   => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'nombre' => 'Viviendas UB',
        'debug'  => getenv('APP_DEBUG') !== '0',   // false al publicar: oculta detalles tecnicos de errores
    ],
    'empresa' => [
        'email'     => 'viviendasub@gmail.com',
        'telefonos' => ['1133770817', '1130662123'],
        'whatsapp'  => '1152591813',   // boton flotante de WhatsApp
        'direccion' => 'CABA, Argentina - Zabala 1837',
        'maps_url'  => 'https://maps.app.goo.gl/ukL3NsWvgiDo5aKh6',
        // Completar SOLO con cuentas reales del proyecto. Las vacias no se muestran en el footer.
        'redes' => [
            'facebook'  => '',
            'instagram' => '',
            'x'         => '',
            'linkedin'  => '',
        ],
    ],
];
