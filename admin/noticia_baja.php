<?php
/** Baja FISICA de noticia (no tiene dependencias). Borra tambien la imagen subida. */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
requiere_rol('ADMIN');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}
csrf_verificar();
$id = post_int('id');
$st = db()->prepare('SELECT imagen FROM noticias WHERE id = ?');
$st->execute([$id]);
$imagen = $st->fetchColumn();
if ($imagen === false) {
    flash('error', 'La noticia no existe.');
} else {
    db()->prepare('DELETE FROM noticias WHERE id = ?')->execute([$id]);
    borrar_archivo_subido($imagen ?: null);
    flash('exito', "Noticia #$id eliminada.");
}
redirigir('noticias.php');
