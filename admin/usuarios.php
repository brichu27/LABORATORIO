<?php
/** ABM Personal (usuarios) - listado (solo ADMIN). */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_rol('ADMIN');
$titulo = 'Personal';
$subtitulo = 'Agentes y administradores: acceso al panel y visibilidad en la página Nosotros.';
$q = get_str('q');
$params = [];
$where = $q !== '' ? ' WHERE ' . sql_busqueda($q, ['u.nombre', 'u.apellido', 'u.email', "CONCAT(u.nombre, ' ', u.apellido)"], ['u.telefono'], $params) : '';
$st = db()->prepare('SELECT u.id, u.nombre, u.apellido, u.email, u.telefono, u.foto, u.activo, u.visible_web,
                            u.ultimo_acceso, (u.password_hash <> \'!\') AS tiene_clave, r.nombre AS rol
                       FROM usuarios u JOIN roles r ON r.id = u.rol_id' . $where . '
                      ORDER BY u.activo DESC, u.apellido, u.nombre');
$st->execute($params);
$lista = $st->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="barra-acciones">
    <?= admin_filtros([], '', $q, 'Nombre, email o teléfono') ?>
    <a href="usuario_form.php" class="boton"><i class="fa-solid fa-user-plus"></i> Nuevo integrante</a>
</div>
<div class="tabla-contenedor">
<table class="tabla">
    <thead><tr><th>Foto</th><th>Nombre</th><th>Contacto</th><th>Rol</th><th>Web</th><th>Acceso panel</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php if (!$lista): ?><tr><td colspan="8" class="tabla-vacia">No hay integrantes que coincidan con la búsqueda.</td></tr><?php endif; ?>
    <?php foreach ($lista as $u): ?>
        <tr class="<?= $u['activo'] ? '' : 'fila-inactiva' ?>">
            <td><img src="<?= e(img_src($u['foto'], '../')) ?>" alt="" class="miniatura redonda"></td>
            <td class="celda-titulo"><?= e($u['apellido'] . ', ' . $u['nombre']) ?></td>
            <td class="celda-contacto">
                <a href="mailto:<?= e($u['email']) ?>"><i class="fa-regular fa-envelope" aria-hidden="true"></i> <?= e($u['email']) ?></a>
                <?php if ($u['telefono']): ?><span class="dato-secundario"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?= e(tel_formato($u['telefono'])) ?></span><?php endif; ?>
            </td>
            <td><?= e($u['rol']) ?></td>
            <td><?= $u['visible_web'] ? 'Visible' : 'Oculto' ?></td>
            <td><?= $u['tiene_clave'] ? 'Habilitado' : 'Sin clave' ?></td>
            <td><span class="estado <?= $u['activo'] ? 'estado-disponible' : 'estado-inactiva' ?>"><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
            <td class="acciones">
                <a href="usuario_form.php?id=<?= (int) $u['id'] ?>" title="Modificar"><i class="fa-solid fa-pen"></i></a>
                <?php if ((int) $u['id'] !== $usuario['id']): ?>
                <form method="post" action="usuario_estado.php" data-confirmar="¿<?= $u['activo'] ? 'Desactivar' : 'Reactivar' ?> a <?= e($u['nombre']) ?>?<?= $u['activo'] ? ' No podrá ingresar al panel ni se mostrará en Nosotros.' : '' ?>" data-confirmar-boton="<?= $u['activo'] ? 'Desactivar' : 'Reactivar' ?>">
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
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
