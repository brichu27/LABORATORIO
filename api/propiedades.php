<?php
/**
 * API JSON para "Cargar más" propiedades.
 * GET api/propiedades.php?offset=6&limite=6[&operacion=VENTA][&barrio=Palermo]
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/repositorio.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    $offset = max(0, get_int('offset', 0));
    $limite = max(1, min(24, get_int('limite', 6)));
    $filtros = [
        'operacion' => get_str('operacion'),
        'barrio'    => get_str('barrio'),
    ];
    $total = propiedades_contar($filtros);
    $items = array_map('propiedad_publica', propiedades_listar($filtros, $offset, $limite));

    echo json_encode([
        'items'  => $items,
        'total'  => $total,
        'hayMas' => ($offset + count($items)) < $total,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $ex) {
    error_log('[API propiedades] ' . $ex->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudieron obtener las propiedades.']);
}
