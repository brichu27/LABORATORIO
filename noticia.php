<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/repositorio.php';

$id = get_int('id');
$noticia = $id > 0 ? noticia_obtener($id) : null;
if ($noticia === null) {
    http_response_code(404);
}
$titulo = $noticia['titulo'] ?? 'Noticia no encontrada';
$paginaActiva = 'blog';
if ($noticia !== null) {
    $mensajeWhatsapp = 'Hola Viviendas UB, leí la nota "' . $noticia['titulo'] . '" en su blog y quisiera hacer una consulta.';
}
require __DIR__ . '/includes/header.php';
$otras = noticias_publicadas(6, $id);
?>
<div class="heading heading--compacto" style="background-image: url(imagenes/heading4.jpg)">
    <p class="titulo-banner">Blog</p>
</div>

<section class="noticia">
<article class="noticia__articulo">
    <a href="blog.php" class="noticia__volver"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al blog</a>
<?php if ($noticia === null): ?>
    <h1>Noticia no encontrada</h1>
    <p>La noticia no existe o no está publicada.</p>
<?php else: ?>
    <?php
    $autor = trim(($noticia['autor_nombre'] ?? '') . ' ' . ($noticia['autor_apellido'] ?? ''));
    $minutos = minutos_lectura($noticia['contenido']);
    ?>
    <h1><?= e($noticia['titulo']) ?></h1>
    <p class="noticia__meta">
        <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <time datetime="<?= e($noticia['fecha_publicacion']) ?>"><?= e(fecha_formato($noticia['fecha_publicacion'])) ?></time></span>
        <span><i class="fa-regular fa-user" aria-hidden="true"></i> <?= e($autor !== '' ? $autor : 'Equipo Viviendas UB') ?></span>
        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= $minutos ?> min de lectura</span>
    </p>
    <?php if (!empty($noticia['imagen'])): ?>
        <figure class="noticia__imagen"><img src="<?= e(img_src($noticia['imagen'])) ?>" alt="" width="1200" height="675"></figure>
    <?php endif; ?>
    <div class="text"><?= render_contenido($noticia['contenido']) ?></div>
<?php endif; ?>
</article>
</section>

<?php if ($otras): ?>
<section class="blog">
    <h2 class="heading-title">Otras noticias</h2>
    <div class="container swiper">
        <div class="card-wrapper">
            <ul class="card-list swiper-wrapper">
                <?php foreach ($otras as $n): ?>
                    <?= render_tarjeta_noticia($n, true) ?>
                <?php endforeach; ?>
            </ul>
            <div class="swiper-pagination"></div>
            <div class="swiper-slide-button swiper-button-prev"></div>
            <div class="swiper-slide-button swiper-button-next"></div>
        </div>
    </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
