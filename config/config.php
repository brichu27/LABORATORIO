<?php
/**
 * Configuracion del sitio. El proyecto se levanta solo con Docker (docker compose up -d --build).
 *
 * Los datos de conexion llegan como variables de entorno (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS),
 * definidas en docker-compose.yml; este archivo no guarda credenciales. Si faltan, el sitio no arranca.
 */
$entorno = static function (string $nombre): string {
    $valor = getenv($nombre);
    if ($valor === false || $valor === '') {
        http_response_code(500);
        exit("Falta la variable de entorno $nombre. El proyecto se levanta con Docker: docker compose up -d --build");
    }
    return $valor;
};

return [
    'db' => [
        'host'    => $entorno('DB_HOST'),
        'port'    => (int) $entorno('DB_PORT'),
        'nombre'  => $entorno('DB_NAME'),
        'usuario' => $entorno('DB_USER'),
        'clave'   => $entorno('DB_PASS'),
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'nombre' => 'Viviendas UB',
        'debug'  => getenv('APP_DEBUG') !== '0',   // false al publicar: oculta detalles tecnicos de errores
    ],
    // Cotizacion del dolar para ordenar por precio mezclando USD y ARS (no se muestra en pantalla).
    // Se consulta el dolar blue (venta) en dolarapi.com y se guarda 1 hora; si no responde se usa el ultimo valor o 'respaldo'.
    'cotizacion' => [
        'url'      => 'https://dolarapi.com/v1/dolares/blue',
        'respaldo' => 1647.37,   // ARS por 1 USD
        'cache_segundos' => 3600,
    ],
    'empresa' => [
        'email'     => 'viviendasub@gmail.com',
        'telefonos' => ['1133770817', '1166572558'],   // pie: Nicolas Fernandez y Patricio Monzon
        'whatsapp'  => '1152591813',   // boton flotante de WhatsApp (Brisa Paez)
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
