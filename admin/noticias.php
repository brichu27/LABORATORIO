<?php
/** ABM Noticias - listado (solo ADMIN). */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_rol('ADMIN');
$titulo = 'Noticias';
$noticias = db()->query('SELECT n.id, n.titulo, n.fecha_publicacion, n.publicada, n.imagen, u.nombre, u.apellido
                           FROM noticias n LEFT JOIN usuarios u ON u.id = n.autor_id
                          ORDER BY n.fecha_publicacion DESC, n.id DESC')->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="barra-acciones"><a href="noticia_form.php" class="boton"><i class="fa-solid fa-plus"></i> Nueva noticia</a></div>
<table class="tabla">
    <thead><tr><th>Imagen</th><th>ID</th><th>Título</th><th>Fecha</th><th>Autor</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php if (!$noticias): ?><tr><td colspan="7">No hay noticias cargadas.</td></tr><?php endif; ?>
    <?php foreach ($noticias as $n): ?>
        <tr>
            <td><img src="<?= e(img_src($n['imagen'], '../')) ?>" alt="" class="miniatura"></td>
            <td><?= (int) $n['id'] ?></td>
            <td><?= e($n['titulo']) ?></td>
            <td><?= e(fecha_formato($n['fecha_publicacion'])) ?></td>
            <td><?= e(trim(($n['nombre'] ?? '') . ' ' . ($n['apellido'] ?? '')) ?: '-') ?></td>
            <td><span class="estado <?= $n['publicada'] ? 'estado-disponible' : 'estado-inactiva' ?>"><?= $n['publicada'] ? 'Publicada' : 'Borrador' ?></span></td>
            <td class="acciones">
                <a href="noticia_form.php?id=<?= (int) $n['id'] ?>" title="Modificar"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="noticia_baja.php" data-confirmar="¿Eliminar definitivamente la noticia #<?= (int) $n['id'] ?>?">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                    <button type="submit" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
