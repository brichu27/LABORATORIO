<?php
/** ABM Noticias - listado (solo ADMIN). */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_rol('ADMIN');
$titulo = 'Noticias';
$subtitulo = 'Notas que se publican en el blog del sitio.';
// Búsqueda por título, autor o fecha (dd/mm/aaaa, mm/aaaa o aaaa) y pestañas Publicadas / Borradores
const ESTADOS_NOTICIA = ['PUBLICADA' => 1, 'BORRADOR' => 0];
$q = get_str('q');
$estado = get_str('estado');
if (!isset(ESTADOS_NOTICIA[$estado])) {
    $estado = '';
}
$where = [];
$params = [];
if ($q !== '') {
    $where[] = sql_busqueda($q, ['n.titulo', 'u.nombre', 'u.apellido', "CONCAT(u.nombre, ' ', u.apellido)", "DATE_FORMAT(n.fecha_publicacion, '%d/%m/%Y')"], [], $params);
}
$desde = 'noticias n LEFT JOIN usuarios u ON u.id = n.autor_id';

$st = db()->prepare("SELECT n.publicada, COUNT(*) FROM $desde" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' GROUP BY n.publicada');
$st->execute($params);
$porEstado = $st->fetchAll(PDO::FETCH_KEY_PAIR);
$cantidades = [];
foreach (ESTADOS_NOTICIA as $clave => $valor) {
    $cantidades[$clave] = (int) ($porEstado[$valor] ?? 0);
}

if ($estado !== '') {
    $where[] = 'n.publicada = :publicada';
    $params[':publicada'] = ESTADOS_NOTICIA[$estado];
}
$st = db()->prepare("SELECT n.id, n.titulo, n.fecha_publicacion, n.publicada, n.imagen, u.nombre, u.apellido FROM $desde"
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY n.fecha_publicacion DESC, n.id DESC');
$st->execute($params);
$noticias = $st->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="barra-acciones">
    <?= admin_filtros($cantidades, $estado, $q, 'Título, autor o fecha (dd/mm/aaaa)', 'Todas') ?>
    <a href="noticia_form.php" class="boton"><i class="fa-solid fa-plus"></i> Nueva noticia</a>
</div>
<div class="tabla-contenedor">
<table class="tabla">
    <thead><tr><th>Imagen</th><th>ID</th><th>Título</th><th>Fecha</th><th>Autor</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php if (!$noticias): ?><tr><td colspan="7" class="tabla-vacia"><?= $q !== '' || $estado !== '' ? 'No hay noticias con esos filtros.' : 'No hay noticias cargadas.' ?></td></tr><?php endif; ?>
    <?php foreach ($noticias as $n): ?>
        <tr>
            <td><img src="<?= e(img_src($n['imagen'], '../')) ?>" alt="" class="miniatura"></td>
            <td class="celda-id">#<?= (int) $n['id'] ?></td>
            <td class="celda-titulo"><?= e($n['titulo']) ?></td>
            <td class="celda-fecha"><?= e(date('d/m/Y', strtotime($n['fecha_publicacion']))) ?></td>
            <td><?= e(trim(($n['nombre'] ?? '') . ' ' . ($n['apellido'] ?? '')) ?: '-') ?></td>
            <td><span class="estado <?= $n['publicada'] ? 'estado-disponible' : 'estado-inactiva' ?>"><?= $n['publicada'] ? 'Publicada' : 'Borrador' ?></span></td>
            <td class="acciones">
                <a href="noticia_form.php?id=<?= (int) $n['id'] ?>" title="Modificar"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="noticia_baja.php" data-confirmar="¿Eliminar definitivamente la noticia #<?= (int) $n['id'] ?> (<?= e($n['titulo']) ?>)? Esta acción no se puede deshacer." data-confirmar-boton="Eliminar">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                    <button type="submit" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
