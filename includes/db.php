<?php
declare(strict_types=1);

/** Devuelve la configuracion (config/config.php). */
function config(): array
{
    static $config = null;
    if ($config === null) {
        $ruta = __DIR__ . '/../config/config.php';
        if (!is_file($ruta)) {
            http_response_code(500);
            exit('Falta config/config.php. Copie config/config.example.php como config/config.php y complete los datos.');
        }
        $config = require $ruta;
    }
    return $config;
}

/** Conexion PDO unica (patron singleton simple). Consultas SIEMPRE preparadas. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = config()['db'];
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $c['host'], (int) $c['port'], $c['nombre'], $c['charset']);
        try {
            $pdo = new PDO($dsn, $c['usuario'], $c['clave'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $ex) {
            error_log('[DB] ' . $ex->getMessage());
            http_response_code(500);
            $detalle = !empty(config()['app']['debug']) ? ' Detalle: ' . $ex->getMessage() : '';
            exit('Error de conexion a la base de datos.' . $detalle);
        }
    }
    return $pdo;
}
