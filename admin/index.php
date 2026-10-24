<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_login();
$titulo = 'Inicio';
$pdo = db();

if ($usuario['rol'] === 'ADMIN') {
    $propiedades = (int) $pdo->query("SELECT COUNT(*) FROM propiedades WHERE estado <> 'INACTIVA'")->fetchColumn();
    $consultas   = (int) $pdo->query("SELECT COUNT(*) FROM consultas WHERE estado = 'NUEVA'")->fetchColumn();
} else {
    $st = $pdo->prepare("SELECT COUNT(*) FROM propiedades WHERE estado <> 'INACTIVA' AND agente_id = ?");
    $st->execute([$usuario['id']]);
    $propiedades = (int) $st->fetchColumn();
    $st = $pdo->prepare("SELECT COUNT(*) FROM consultas WHERE estado = 'NUEVA' AND agente_id = ?");
    $st->execute([$usuario['id']]);
    $consultas = (int) $st->fetchColumn();
}
$tasaciones    = $usuario['rol'] === 'ADMIN' ? (int) $pdo->query("SELECT COUNT(*) FROM solicitudes_tasacion WHERE estado = 'NUEVA'")->fetchColumn() : null;
$postulaciones = $usuario['rol'] === 'ADMIN' ? (int) $pdo->query("SELECT COUNT(*) FROM postulaciones WHERE estado = 'RECIBIDA'")->fetchColumn() : null;

require __DIR__ . '/../includes/admin_header.php';
?>
<div class="tarjetas">
    <a class="tarjeta" href="propiedades.php"><span><?= $propiedades ?></span><?= $usuario['rol'] === 'ADMIN' ? 'Propiedades publicadas' : 'Mis propiedades' ?></a>
    <a class="tarjeta" href="consultas.php?tipo=consultas"><span><?= $consultas ?></span>Consultas nuevas</a>
    <?php if ($tasaciones !== null): ?>
        <a class="tarjeta" href="consultas.php?tipo=tasaciones"><span><?= $tasaciones ?></span>Tasaciones nuevas</a>
        <a class="tarjeta" href="consultas.php?tipo=postulaciones"><span><?= $postulaciones ?></span>Postulaciones recibidas</a>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
