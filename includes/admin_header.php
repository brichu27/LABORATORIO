<?php
/** Cabecera del panel. Requiere $usuario (de requiere_login/requiere_rol) y $titulo. */
declare(strict_types=1);
$titulo = $titulo ?? 'Panel';
$itemsMenu = [
    ['index.php', 'Inicio', 'fa-gauge', ['ADMIN', 'VENDEDOR']],
    ['propiedades.php', 'Propiedades', 'fa-house', ['ADMIN', 'VENDEDOR']],
    ['consultas.php', 'Consultas', 'fa-envelope-open-text', ['ADMIN', 'VENDEDOR']],
    ['noticias.php', 'Noticias', 'fa-newspaper', ['ADMIN']],
    ['usuarios.php', 'Personal', 'fa-users', ['ADMIN']],
];
$actual = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($titulo) ?> | Panel Viviendas UB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin">
<aside class="admin-menu">
    <a href="../index.php" class="admin-logo"><img src="../imagenes/Vivienda.png" alt="Viviendas UB"></a>
    <p class="admin-usuario"><i class="fa-solid fa-user"></i> <?= e($usuario['nombre']) ?><br><small><?= e($usuario['rol'] === 'ADMIN' ? 'Administrador' : 'Vendedor') ?></small></p>
    <nav>
        <?php foreach ($itemsMenu as [$url, $texto, $icono, $roles]): ?>
            <?php if (in_array($usuario['rol'], $roles, true)): ?>
                <a href="<?= e($url) ?>" class="<?= strpos($actual, basename($url, '.php')) === 0 ? 'activo' : '' ?>"><i class="fa-solid <?= e($icono) ?>"></i> <?= e($texto) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <form method="post" action="logout.php">
        <?= csrf_campo() ?>
        <button type="submit" class="btn-salir"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
    </form>
</aside>
<main class="admin-contenido">
    <h1><?= e($titulo) ?></h1>
    <?= flash_render() ?>
