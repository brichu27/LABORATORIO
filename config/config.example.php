<?php
/**
 * Copiar este archivo como config/config.php y completar los datos.
 * config.php NO se versiona (ver .gitignore): contiene credenciales.
 */
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'nombre'  => 'viviendas_ub',
        'usuario' => 'root',   // XAMPP por defecto: root sin clave. En produccion usar un usuario dedicado.
        'clave'   => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'nombre' => 'Viviendas UB',
        'debug'  => true,      // false al publicar: oculta detalles tecnicos de errores
    ],
    'empresa' => [
        'email'     => 'viviendasub@gmail.com',
        'telefonos' => ['1133770817', '1130662123'],
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
