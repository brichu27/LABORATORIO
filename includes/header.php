<?php
/**
 * Cabecera publica comun. Variables opcionales antes del require:
 *   $titulo        string  Titulo de la pestaña
 *   $paginaActiva  string  index|nosotros|propiedades|tasaciones|blog|contacto
 */
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/repositorio.php';

$titulo       = $titulo ?? 'Inicio';
$paginaActiva = $paginaActiva ?? '';
$menu = [
    'index'       => ['index.php', 'Home'],
    'nosotros'    => ['nosotros.php', 'Nosotros'],
    'propiedades' => ['propiedades.php', 'Propiedades'],
    'tasaciones'  => ['tasaciones.php', 'Tasaciones'],
    'blog'        => ['blog.php', 'Blog'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | Viviendas UB</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style-correcciones.css">
</head>
<body>
<section class="header">
    <a href="index.php" class="logo"><img src="imagenes/Vivienda.png" alt="Viviendas UB" width="190" height="70"></a>
    <nav class="navbar" aria-label="Menú principal">
        <?php foreach ($menu as $clave => [$url, $texto]): ?>
            <a href="<?= e($url) ?>" class="<?= $paginaActiva === $clave ? 'activo' : '' ?>"><?= e($texto) ?></a>
        <?php endforeach; ?>
        <a href="contacto.php" class="join-b <?= $paginaActiva === 'contacto' ? 'activo' : '' ?>">UNIRSE</a>
    </nav>
    <div id="menu-btn" class="fas fa-bars" role="button" aria-label="Abrir menú" tabindex="0"></div>
</section>
<?= flash_render() ?>
