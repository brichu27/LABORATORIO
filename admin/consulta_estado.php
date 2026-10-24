<?php
/** Cambio de estado de una solicitud (lista blanca de tablas y estados: sin SQL dinamico inseguro). */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_rol('ADMIN', 'VENDEDOR');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}
csrf_verificar();

$mapa = [
    'consultas'     => ['consultas', ['NUEVA', 'EN_PROCESO', 'RESPONDIDA', 'CERRADA']],
    'tasaciones'    => ['solicitudes_tasacion', ['NUEVA', 'EN_PROCESO', 'RESPONDIDA', 'CERRADA']],
    'postulaciones' => ['postulaciones', ['RECIBIDA', 'EN_REVISION', 'ENTREVISTA', 'DESCARTADA', 'INCORPORADA']],
];
$tipo   = post('tipo');
$id     = post_int('id');
$estado = post('estado');

if (!isset($mapa[$tipo]) || !in_array($estado, $mapa[$tipo][1], true)) {
    flash('error', 'Datos inválidos.');
    redirigir('consultas.php');
}
if ($tipo !== 'consultas' && $usuario['rol'] !== 'ADMIN') {
    http_response_code(403);
    exit('Acceso denegado.');
}
$tabla = $mapa[$tipo][0];
$sql = "UPDATE $tabla SET estado = ? WHERE id = ?";
$params = [$estado, $id];
if ($tipo === 'consultas' && $usuario['rol'] === 'VENDEDOR') {
    $sql .= ' AND agente_id = ?';        // el vendedor solo modifica consultas asignadas
    $params[] = $usuario['id'];
}
$st = db()->prepare($sql);
$st->execute($params);
flash($st->rowCount() > 0 ? 'exito' : 'error', $st->rowCount() > 0 ? 'Estado actualizado.' : 'No se modificó el registro (sin permiso o sin cambios).');
redirigir('consultas.php?tipo=' . $tipo);
