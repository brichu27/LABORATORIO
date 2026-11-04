<?php
/** ABM Propiedades - listado. ADMIN ve todas; VENDEDOR solo las propias. */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/repositorio.php';
$usuario = requiere_rol('ADMIN', 'VENDEDOR');
$titulo = 'Propiedades';
$subtitulo = $usuario['rol'] === 'ADMIN' ? 'Alta, modificación y baja de las propiedades publicadas en el sitio.' : 'Tus propiedades publicadas en el sitio.';

$estado = get_str('estado');
if (!in_array($estado, ESTADOS_PROPIEDAD, true)) {
    $estado = '';
}
$q = get_str('q');
$where = [];
$params = [];
if ($usuario['rol'] === 'VENDEDOR') {
    $where[] = 'p.agente_id = :agente';
    $params[':agente'] = $usuario['id'];
}
if ($q !== '') {
    $where[] = sql_busqueda($q, ['p.titulo', 'p.direccion', 'p.barrio', 'u.nombre', 'u.apellido'], [], $params);
}
$desde = 'propiedades p JOIN usuarios u ON u.id = p.agente_id';

// Cantidad por estado para las pestañas (con la búsqueda aplicada, sin el filtro de estado)
$st = db()->prepare("SELECT p.estado, COUNT(*) FROM $desde" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' GROUP BY p.estado');
$st->execute($params);
$porEstado = $st->fetchAll(PDO::FETCH_KEY_PAIR);
$cantidades = [];
foreach (ESTADOS_PROPIEDAD as $e) {
    $cantidades[$e] = (int) ($porEstado[$e] ?? 0);
}

if ($estado !== '') {
    $where[] = 'p.estado = :estado';
    $params[':estado'] = $estado;
}
$sql = 'SELECT p.id, p.titulo, p.operacion, p.precio, p.moneda, p.barrio, p.estado, p.destacada,
               u.nombre, u.apellido, ' . SQL_PORTADA . " AS imagen
          FROM $desde"
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . ' ORDER BY p.actualizado_en DESC';
$st = db()->prepare($sql);
$st->execute($params);
$propiedades = $st->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>
<div class="barra-acciones">
    <?= admin_filtros($cantidades, $estado, $q, 'Título, dirección, barrio o agente', 'Todas') ?>
    <a href="propiedad_form.php" class="boton"><i class="fa-solid fa-plus"></i> Nueva propiedad</a>
</div>

<div class="tabla-contenedor">
<table class="tabla">
    <thead><tr><th>Foto</th><th>ID</th><th>Título</th><th>Operación</th><th>Precio</th><th>Barrio</th><th>Agente</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php if (!$propiedades): ?>
        <tr><td colspan="9" class="tabla-vacia"><?= $q !== '' || $estado !== '' ? 'No hay propiedades con esos filtros.' : 'No hay propiedades para mostrar.' ?></td></tr>
    <?php endif; ?>
    <?php foreach ($propiedades as $p): ?>
        <tr>
            <td><img src="<?= e(img_src($p['imagen'], '../')) ?>" alt="" class="miniatura"></td>
            <td class="celda-id">#<?= (int) $p['id'] ?></td>
            <td class="celda-titulo"><?= e($p['titulo']) ?><?= $p['destacada'] ? ' <i class="fa-solid fa-star destacada" title="Destacada"></i>' : '' ?></td>
            <td><?= e($p['operacion']) ?></td>
            <td class="celda-precio"><?= e(precio_formato($p['precio'], $p['moneda'], $p['operacion'])) ?></td>
            <td><?= e($p['barrio']) ?></td>
            <td><?= e($p['nombre'] . ' ' . $p['apellido']) ?></td>
            <td><span class="estado estado-<?= e(strtolower($p['estado'])) ?>"><?= e($p['estado']) ?></span></td>
            <td class="acciones">
                <a href="propiedad_form.php?id=<?= (int) $p['id'] ?>" title="Modificar"><i class="fa-solid fa-pen"></i></a>
                <a href="../propiedad.php?id=<?= (int) $p['id'] ?>" target="_blank" title="Ver en el sitio"><i class="fa-solid fa-eye"></i></a>
                <?php if ($p['estado'] !== 'INACTIVA'): ?>
                <form method="post" action="propiedad_baja.php" data-confirmar="¿Dar de baja la propiedad #<?= (int) $p['id'] ?> (<?= e($p['titulo']) ?>)? Dejará de verse en el sitio; sus consultas se conservan." data-confirmar-boton="Dar de baja">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <button type="submit" title="Dar de baja"><i class="fa-solid fa-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
