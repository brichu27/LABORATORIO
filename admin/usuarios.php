<?php
/** ABM Personal (usuarios) - listado (solo ADMIN). */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_rol('ADMIN');
$titulo = 'Personal';
$lista = db()->query('SELECT u.id, u.nombre, u.apellido, u.email, u.telefono, u.foto, u.activo, u.visible_web,
                             u.ultimo_acceso, (u.password_hash <> \'!\') AS tiene_clave, r.nombre AS rol
                        FROM usuarios u JOIN roles r ON r.id = u.rol_id
                       ORDER BY u.activo DESC, u.apellido, u.nombre')->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="barra-acciones"><a href="usuario_form.php" class="boton"><i class="fa-solid fa-user-plus"></i> Nuevo integrante</a></div>
<table class="tabla">
    <thead><tr><th>Foto</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Rol</th><th>Web</th><th>Acceso panel</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($lista as $u): ?>
        <tr class="<?= $u['activo'] ? '' : 'fila-inactiva' ?>">
            <td><img src="<?= e(img_src($u['foto'], '../')) ?>" alt="" class="miniatura redonda"></td>
            <td><?= e($u['apellido'] . ', ' . $u['nombre']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e(tel_formato($u['telefono'])) ?></td>
            <td><?= e($u['rol']) ?></td>
            <td><?= $u['visible_web'] ? 'Visible' : 'Oculto' ?></td>
            <td><?= $u['tiene_clave'] ? 'Habilitado' : 'Sin clave' ?></td>
            <td><span class="estado <?= $u['activo'] ? 'estado-disponible' : 'estado-inactiva' ?>"><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
            <td class="acciones">
                <a href="usuario_form.php?id=<?= (int) $u['id'] ?>" title="Modificar"><i class="fa-solid fa-pen"></i></a>
                <?php if ((int) $u['id'] !== $usuario['id']): ?>
                <form method="post" action="usuario_estado.php" data-confirmar="¿<?= $u['activo'] ? 'Desactivar' : 'Reactivar' ?> a <?= e($u['nombre']) ?>?">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" title="<?= $u['activo'] ? 'Desactivar' : 'Reactivar' ?>"><i class="fa-solid <?= $u['activo'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
