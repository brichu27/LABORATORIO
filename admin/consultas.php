<?php
/**
 * Gestion de solicitudes recibidas.
 *  - consultas (por propiedad): ADMIN todas / VENDEDOR solo las asignadas
 *  - tasaciones y postulaciones: solo ADMIN
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_rol('ADMIN', 'VENDEDOR');

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
$pdo = db();

if ($tipo === 'consultas') {
    $sql = 'SELECT c.id, c.creado_en, c.nombre, c.email, c.telefono, c.mensaje, c.preferencia_contacto, c.estado,
                   p.id AS propiedad_id, p.titulo AS propiedad, u.nombre AS agente
              FROM consultas c
              JOIN propiedades p ON p.id = c.propiedad_id
              LEFT JOIN usuarios u ON u.id = c.agente_id'
         . ($usuario['rol'] === 'VENDEDOR' ? ' WHERE c.agente_id = :agente' : '')
         . ' ORDER BY FIELD(c.estado, \'NUEVA\', \'EN_PROCESO\', \'RESPONDIDA\', \'CERRADA\'), c.creado_en DESC';
    $st = $pdo->prepare($sql);
    $st->execute($usuario['rol'] === 'VENDEDOR' ? [':agente' => $usuario['id']] : []);
} elseif ($tipo === 'tasaciones') {
    $st = $pdo->query('SELECT s.id, s.creado_en, s.nombre, s.email, s.telefono, s.mensaje, s.estado,
                              s.direccion_inmueble, s.superficie_aprox, t.nombre AS tipo
                         FROM solicitudes_tasacion s JOIN tipos_propiedad t ON t.id = s.tipo_propiedad_id
                        ORDER BY s.creado_en DESC');
} else {
    $st = $pdo->query('SELECT id, creado_en, CONCAT(nombre, \' \', apellido) AS nombre, email, telefono, mensaje, estado,
                              anios_experiencia, zona_interes, linkedin_url
                         FROM postulaciones ORDER BY creado_en DESC');
}
$filas = $st->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>
<nav class="pestanas">
    <?php foreach (TIPOS_SOLICITUD as $clave => [$texto]): ?>
        <?php if ($clave === 'consultas' || $usuario['rol'] === 'ADMIN'): ?>
            <a href="?tipo=<?= $clave ?>" class="<?= $tipo === $clave ? 'activo' : '' ?>"><?= e($texto) ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<table class="tabla">
    <thead>
        <tr><th>Fecha</th><th>Contacto</th><th>Detalle</th><th>Mensaje</th><th>Estado</th></tr>
    </thead>
    <tbody>
    <?php if (!$filas): ?><tr><td colspan="5">No hay registros.</td></tr><?php endif; ?>
    <?php foreach ($filas as $f): ?>
        <tr>
            <td><?= e(date('d/m/Y H:i', strtotime($f['creado_en']))) ?></td>
            <td>
                <strong><?= e($f['nombre']) ?></strong><br>
                <a href="mailto:<?= e($f['email']) ?>"><?= e($f['email']) ?></a><br>
                <a href="<?= e(wa_link($f['telefono'])) ?>" target="_blank" rel="noopener"><?= e($f['telefono']) ?></a>
            </td>
            <td>
                <?php if ($tipo === 'consultas'): ?>
                    <a href="../propiedad.php?id=<?= (int) $f['propiedad_id'] ?>" target="_blank">#<?= (int) $f['propiedad_id'] ?> <?= e($f['propiedad']) ?></a><br>
                    Prefiere: <?= e($f['preferencia_contacto']) ?><?= $usuario['rol'] === 'ADMIN' ? '<br>Agente: ' . e($f['agente'] ?? '-') : '' ?>
                <?php elseif ($tipo === 'tasaciones'): ?>
                    <?= e($f['tipo']) ?> - <?= e($f['direccion_inmueble']) ?><?= $f['superficie_aprox'] ? '<br>' . e((string) $f['superficie_aprox']) . ' m²' : '' ?>
                <?php else: ?>
                    <?= (int) $f['anios_experiencia'] ?> años de experiencia<br>Zona: <?= e($f['zona_interes']) ?>
                    <?php if ($f['linkedin_url']): ?><br><a href="<?= e($f['linkedin_url']) ?>" target="_blank" rel="noopener">LinkedIn</a><?php endif; ?>
                <?php endif; ?>
            </td>
            <td class="mensaje"><?= nl2br(e($f['mensaje'] ?? '')) ?></td>
            <td>
                <form method="post" action="consulta_estado.php" class="form-estado">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                    <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                    <select name="estado">
                        <?php foreach ($estados as $e): ?><option <?= $f['estado'] === $e ? 'selected' : '' ?>><?= $e ?></option><?php endforeach; ?>
                    </select>
                    <button type="submit" title="Guardar estado"><i class="fa-solid fa-check"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
