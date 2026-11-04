<?php
/**
 * Gestion de solicitudes recibidas.
 *  - consultas (por propiedad): ADMIN todas / VENDEDOR solo las asignadas
 *  - tasaciones y postulaciones: solo ADMIN
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_rol('ADMIN', 'VENDEDOR');

const SUBTITULOS_SOLICITUD = [
    'consultas'     => 'Mensajes enviados desde la ficha de cada propiedad.',
    'tasaciones'    => 'Pedidos de tasación enviados desde el sitio.',
    'postulaciones' => 'Personas que se postularon como agentes desde "Unirse".',
];
const TIPOS_SOLICITUD = [
    'consultas'     => ['Consultas por propiedad', ['NUEVA', 'EN_PROCESO', 'RESPONDIDA', 'CERRADA']],
    'tasaciones'    => ['Solicitudes de tasación', ['NUEVA', 'EN_PROCESO', 'RESPONDIDA', 'CERRADA']],
    'postulaciones' => ['Postulaciones de agentes', ['RECIBIDA', 'EN_REVISION', 'ENTREVISTA', 'DESCARTADA', 'INCORPORADA']],
];
$tipo = get_str('tipo') ?: 'consultas';
if (!isset(TIPOS_SOLICITUD[$tipo]) || ($tipo !== 'consultas' && $usuario['rol'] !== 'ADMIN')) {
    $tipo = 'consultas';
}
[$titulo, $estados] = TIPOS_SOLICITUD[$tipo];
$subtitulo = SUBTITULOS_SOLICITUD[$tipo];
$pdo = db();

// Búsqueda (q) y filtro por estado: se aplican igual a los tres tipos de solicitud
$q = get_str('q');
$estado = get_str('estado');
if (!in_array($estado, $estados, true)) {
    $estado = '';
}
$where = [];
$params = [];
if ($tipo === 'consultas') {
    $campos = 'c.id, c.creado_en, c.nombre, c.email, c.telefono, c.mensaje, c.preferencia_contacto, c.estado,
               p.id AS propiedad_id, p.titulo AS propiedad, u.nombre AS agente';
    $desde = 'consultas c JOIN propiedades p ON p.id = c.propiedad_id LEFT JOIN usuarios u ON u.id = c.agente_id';
    $alias = 'c';
    $orden = 'FIELD(c.estado, \'NUEVA\', \'EN_PROCESO\', \'RESPONDIDA\', \'CERRADA\'), c.creado_en DESC';
    if ($usuario['rol'] === 'VENDEDOR') {
        $where[] = 'c.agente_id = :agente';
        $params[':agente'] = $usuario['id'];
    }
    $buscarEn = [['c.nombre', 'c.email', 'p.titulo', 'p.direccion', 'p.barrio'], ['c.telefono']];
    $placeholder = 'Nombre, email, teléfono, propiedad o barrio';
} elseif ($tipo === 'tasaciones') {
    $campos = 's.id, s.creado_en, s.nombre, s.email, s.telefono, s.mensaje, s.estado,
               s.direccion_inmueble, s.superficie_aprox, t.nombre AS tipo';
    $desde = 'solicitudes_tasacion s JOIN tipos_propiedad t ON t.id = s.tipo_propiedad_id';
    $alias = 's';
    $orden = 's.creado_en DESC';
    $buscarEn = [['s.nombre', 's.email', 's.direccion_inmueble'], ['s.telefono']];
    $placeholder = 'Nombre, email, teléfono o dirección';
} else {
    $campos = 'o.id, o.creado_en, CONCAT(o.nombre, \' \', o.apellido) AS nombre, o.email, o.telefono, o.mensaje, o.estado,
               o.anios_experiencia, o.zona_interes, o.linkedin_url';
    $desde = 'postulaciones o';
    $alias = 'o';
    $orden = 'o.creado_en DESC';
    $buscarEn = [['o.nombre', 'o.apellido', 'o.email', 'o.zona_interes'], ['o.telefono']];
    $placeholder = 'Nombre, email, teléfono o zona';
}
if ($q !== '') {
    $where[] = sql_busqueda($q, $buscarEn[0], $buscarEn[1], $params);
}

// Cantidad por estado para las pestañas (con la búsqueda aplicada, sin el filtro de estado)
$st = $pdo->prepare("SELECT $alias.estado, COUNT(*) FROM $desde" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " GROUP BY $alias.estado");
$st->execute($params);
$porEstado = $st->fetchAll(PDO::FETCH_KEY_PAIR);
$cantidades = [];
foreach ($estados as $e) {
    $cantidades[$e] = (int) ($porEstado[$e] ?? 0);
}

if ($estado !== '') {
    $where[] = "$alias.estado = :estado";
    $params[':estado'] = $estado;
}
$st = $pdo->prepare("SELECT $campos FROM $desde" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY $orden");
$st->execute($params);
$filas = $st->fetchAll();

// Canal de contacto preferido (solo consultas por propiedad): texto, ícono y clase de color
const CANALES = [
    'EMAIL'    => ['Email', 'fa-regular fa-envelope'],
    'WHATSAPP' => ['WhatsApp', 'fa-brands fa-whatsapp'],
    'TELEFONO' => ['Teléfono', 'fa-solid fa-phone'],
];
$columnas = $tipo === 'consultas' ? 6 : 5;

require __DIR__ . '/../includes/admin_header.php';
?>
<?php if ($usuario['rol'] === 'ADMIN'): // el vendedor solo ve consultas: la pestaña repetiría el título ?>
<nav class="pestanas">
    <?php foreach (TIPOS_SOLICITUD as $clave => [$texto]): ?>
        <a href="?tipo=<?= $clave ?>" class="<?= $tipo === $clave ? 'activo' : '' ?>"><?= e($texto) ?></a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>

<div class="barra-acciones">
    <?= admin_filtros($cantidades, $estado, $q, $placeholder, 'Todas', $tipo !== 'consultas' ? ['tipo' => $tipo] : []) ?>
</div>

<div class="tabla-contenedor">
<table class="tabla">
    <thead>
        <tr><th>Fecha</th><th>Contacto</th><th><?= $tipo === 'consultas' ? 'Propiedad' : 'Detalle' ?></th><?php if ($tipo === 'consultas'): ?><th>Canal</th><?php endif; ?><th>Mensaje</th><th>Estado</th></tr>
    </thead>
    <tbody>
    <?php if (!$filas): ?><tr><td colspan="<?= $columnas ?>" class="tabla-vacia"><?= $q !== '' || $estado !== '' ? 'No hay resultados con esos filtros.' : 'No hay registros.' ?></td></tr><?php endif; ?>
    <?php foreach ($filas as $f): ?>
        <?php $nueva = in_array($f['estado'], ['NUEVA', 'RECIBIDA'], true); ?>
        <tr class="<?= $nueva ? 'fila-nueva' : '' ?>">
            <td class="celda-fecha">
                <?= e(date('d/m/Y', strtotime($f['creado_en']))) ?>
                <small><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= e(date('H:i', strtotime($f['creado_en']))) ?> hs</small>
            </td>
            <td class="celda-contacto">
                <strong><?= e($f['nombre']) ?></strong>
                <a href="mailto:<?= e($f['email']) ?>"><i class="fa-regular fa-envelope" aria-hidden="true"></i> <?= e($f['email']) ?></a>
                <a href="<?= e(wa_link($f['telefono'])) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> <?= e(tel_formato($f['telefono'])) ?></a>
            </td>
            <td class="celda-detalle">
                <?php if ($tipo === 'consultas'): ?>
                    <?php
                    // La última palabra va pegada al ícono para que el ícono nunca quede solo en un renglón
                    $palabras = explode(' ', '#' . (int) $f['propiedad_id'] . ' ' . $f['propiedad']);
                    $ultima = array_pop($palabras);
                    ?>
                    <a class="enlace-propiedad" href="../propiedad.php?id=<?= (int) $f['propiedad_id'] ?>" target="_blank"><?= e(implode(' ', $palabras)) ?> <span class="sin-corte"><?= e($ultima) ?> <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span></a>
                    <?php if ($usuario['rol'] === 'ADMIN'): ?><small class="dato-secundario">Agente: <?= e($f['agente'] ?? '-') ?></small><?php endif; ?>
                <?php elseif ($tipo === 'tasaciones'): ?>
                    <?= e($f['tipo']) ?> - <?= e($f['direccion_inmueble']) ?><?= $f['superficie_aprox'] ? '<br>' . e((string) $f['superficie_aprox']) . ' m²' : '' ?>
                <?php else: ?>
                    <?= (int) $f['anios_experiencia'] ?> años de experiencia<br>Zona: <?= e($f['zona_interes']) ?>
                    <?php if ($f['linkedin_url']): ?><br><a href="<?= e($f['linkedin_url']) ?>" target="_blank" rel="noopener">LinkedIn</a><?php endif; ?>
                <?php endif; ?>
            </td>
            <?php if ($tipo === 'consultas'): ?>
                <?php [$canal, $iconoCanal] = CANALES[$f['preferencia_contacto']] ?? [$f['preferencia_contacto'], 'fa-regular fa-envelope']; ?>
                <td><span class="canal canal--<?= e(strtolower($f['preferencia_contacto'])) ?>"><i class="<?= e($iconoCanal) ?>" aria-hidden="true"></i> <?= e($canal) ?></span></td>
            <?php endif; ?>
            <td class="mensaje"><?php if (($f['mensaje'] ?? '') !== ''): ?><div class="mensaje__texto"><?= nl2br(e($f['mensaje'])) ?></div><?php endif; ?></td>
            <td>
                <form method="post" action="consulta_estado.php" class="form-estado">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                    <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                    <input type="hidden" name="q" value="<?= e($q) ?>">
                    <input type="hidden" name="filtro_estado" value="<?= e($estado) ?>">
                    <select name="estado" class="estado-select estado-select--<?= e(strtolower($f['estado'])) ?>" aria-label="Estado">
                        <?php foreach ($estados as $e): ?><option value="<?= e($e) ?>" <?= $f['estado'] === $e ? 'selected' : '' ?>><?= e(estado_texto($e)) ?></option><?php endforeach; ?>
                    </select>
                    <button type="submit" title="Guardar estado"><i class="fa-solid fa-check"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
