<?php
/** Cabecera del panel. Requiere $usuario (de requiere_login/requiere_rol) y $titulo; $subtitulo es opcional. */
declare(strict_types=1);
/** @var array $usuario Lo define la pagina que incluye este archivo (requiere_login/requiere_rol). */
$titulo = $titulo ?? 'Panel';
$subtitulo = $subtitulo ?? '';
$itemsMenu = [
    ['index.php', 'Inicio', 'fa-gauge', ['ADMIN', 'VENDEDOR']],
    ['propiedades.php', 'Propiedades', 'fa-house', ['ADMIN', 'VENDEDOR']],
    ['consultas.php', 'Consultas', 'fa-envelope-open-text', ['ADMIN', 'VENDEDOR']],
    ['noticias.php', 'Noticias', 'fa-newspaper', ['ADMIN']],
    ['usuarios.php', 'Personal', 'fa-users', ['ADMIN']],
];
$actual = basename($_SERVER['SCRIPT_NAME']);

// Consultas por propiedad sin atender: se muestran como contador junto a "Consultas" (el vendedor ve solo las suyas)
if ($usuario['rol'] === 'ADMIN') {
    $consultasNuevas = (int) db()->query("SELECT COUNT(*) FROM consultas WHERE estado = 'NUEVA'")->fetchColumn();
} else {
    $stMenu = db()->prepare("SELECT COUNT(*) FROM consultas WHERE estado = 'NUEVA' AND agente_id = ?");
    $stMenu->execute([$usuario['id']]);
    $consultasNuevas = (int) $stMenu->fetchColumn();
}
// En la sesion, 'nombre' trae nombre y apellido: las iniciales salen de las dos primeras palabras
$palabras = preg_split('/\s+/', trim($usuario['nombre'])) ?: [''];
$iniciales = mb_strtoupper(mb_substr($palabras[0], 0, 1) . mb_substr($palabras[1] ?? '', 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($titulo) ?> | Panel Viviendas UB</title>
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin">
<aside class="admin-menu">
    <a href="../index.php" class="admin-marca" title="Ver el sitio público">
        <span class="admin-marca__logo"><img src="../imagenes/logo-header.png" alt="" width="237" height="129"></span>
        <span class="admin-marca__texto"><strong>Viviendas UB</strong><small>Panel de gestión</small></span>
    </a>
    <div class="admin-usuario">
        <span class="admin-usuario__avatar" aria-hidden="true"><?= e($iniciales) ?></span>
        <span class="admin-usuario__datos">
            <strong><?= e($usuario['nombre']) ?></strong>
            <small><?= e($usuario['rol'] === 'ADMIN' ? 'Administrador' : 'Vendedor') ?></small>
        </span>
    </div>
    <nav aria-label="Menú del panel">
        <?php foreach ($itemsMenu as [$url, $texto, $icono, $rolesItem]): ?>
            <?php if (in_array($usuario['rol'], $rolesItem, true)): ?>
                <?php $activo = strpos($actual, basename($url, '.php')) === 0; ?>
                <a href="<?= e($url) ?>" class="<?= $activo ? 'activo' : '' ?>"<?= $activo ? ' aria-current="page"' : '' ?>>
                    <i class="fa-solid <?= e($icono) ?>" aria-hidden="true"></i>
                    <span><?= e($texto) ?></span>
                    <?php if ($url === 'consultas.php' && $consultasNuevas > 0): ?>
                        <span class="admin-menu__contador" title="Consultas nuevas"><?= $consultasNuevas ?> <?= $consultasNuevas === 1 ? 'nueva' : 'nuevas' ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <form method="post" action="logout.php">
        <?= csrf_campo() ?>
        <button type="submit" class="btn-salir"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Cerrar sesión</button>
    </form>
</aside>
<main class="admin-contenido">
    <header class="admin-encabezado">
        <h1><?= e($titulo) ?></h1>
        <?php if ($subtitulo !== ''): ?><p><?= e($subtitulo) ?></p><?php endif; ?>
    </header>
    <div class="admin-cuerpo">
    <?= flash_render() ?>
