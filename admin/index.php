<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
$usuario = requiere_login();
$titulo = 'Inicio';
$subtitulo = 'Resumen de lo que hay para atender hoy.';
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
    <a class="tarjeta" href="propiedades.php">
        <span class="tarjeta__etiqueta"><?= $usuario['rol'] === 'ADMIN' ? 'Propiedades publicadas' : 'Mis propiedades' ?></span>
        <span class="tarjeta__icono"><i class="fa-solid fa-house" aria-hidden="true"></i></span>
        <span class="tarjeta__numero"><?= $propiedades ?></span>
    </a>
    <a class="tarjeta" href="consultas.php?estado=NUEVA">
        <span class="tarjeta__etiqueta">Consultas nuevas</span>
        <span class="tarjeta__icono"><i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i></span>
        <span class="tarjeta__numero"><?= $consultas ?></span>
        <?php if ($consultas > 0): ?><span class="tarjeta__nota">Requieren respuesta</span><?php endif; ?>
    </a>
    <?php if ($tasaciones !== null): ?>
        <a class="tarjeta" href="consultas.php?tipo=tasaciones&amp;estado=NUEVA">
            <span class="tarjeta__etiqueta">Tasaciones nuevas</span>
            <span class="tarjeta__icono"><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i></span>
            <span class="tarjeta__numero"><?= $tasaciones ?></span>
            <?php if ($tasaciones > 0): ?><span class="tarjeta__nota">Requieren respuesta</span><?php endif; ?>
        </a>
        <a class="tarjeta" href="consultas.php?tipo=postulaciones&amp;estado=RECIBIDA">
            <span class="tarjeta__etiqueta">Postulaciones recibidas</span>
            <span class="tarjeta__icono"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></span>
            <span class="tarjeta__numero"><?= $postulaciones ?></span>
        </a>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
