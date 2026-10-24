<?php
/** Baja/alta LOGICA de personal (activo 0/1). No se borra: tiene propiedades y consultas asociadas. */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_rol('ADMIN');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}
csrf_verificar();
$id = post_int('id');
if ($id === $usuario['id']) {
    flash('error', 'No puede desactivar su propio usuario.');
    redirigir('usuarios.php');
}
$st = db()->prepare('SELECT activo FROM usuarios WHERE id = ?');
$st->execute([$id]);
$activo = $st->fetchColumn();
if ($activo === false) {
    flash('error', 'El usuario no existe.');
} else {
    $nuevo = (int) $activo === 1 ? 0 : 1;
    db()->prepare('UPDATE usuarios SET activo = ?, visible_web = IF(? = 0, 0, visible_web) WHERE id = ?')->execute([$nuevo, $nuevo, $id]);
    flash('exito', $nuevo ? 'Usuario reactivado.' : 'Usuario desactivado: ya no puede ingresar ni se muestra en el sitio.');
}
redirigir('usuarios.php');
