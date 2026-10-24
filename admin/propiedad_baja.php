<?php
/** Baja LOGICA de propiedad (estado = INACTIVA). Se conserva el historial de consultas (FK). */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/repositorio.php';
$usuario = requiere_rol('ADMIN', 'VENDEDOR');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}
csrf_verificar();
$id = post_int('id');
$propiedad = propiedad_obtener($id);
if ($propiedad === null) {
    flash('error', 'La propiedad no existe.');
} elseif (!puede_editar_propiedad($usuario, $propiedad)) {
    http_response_code(403);
    exit('No puede dar de baja propiedades de otro agente.');
} else {
    db()->prepare("UPDATE propiedades SET estado = 'INACTIVA', destacada = 0 WHERE id = ?")->execute([$id]);
    flash('exito', "Propiedad #$id dada de baja.");
}
redirigir('propiedades.php');
