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
require __DIR__ . '/includes/header.php';
$otras = noticias_publicadas(6, $id);
?>
<div class="heading" style="background:url(imagenes/heading4.jpg) no-repeat">
    <h1>Blog</h1>
</div>

<section class="noticia">
<?php if ($noticia === null): ?>
    <h1>Noticia no encontrada</h1>
    <p>La noticia no existe o no está publicada. <a href="blog.php">Volver al blog</a>.</p>
<?php else: ?>
    <h1><?= e($noticia['titulo']) ?></h1>
    <p><?= e(fecha_formato($noticia['fecha_publicacion'])) ?></p>
    <?php if (!empty($noticia['imagen'])): ?>
        <div class="image"><img src="<?= e($noticia['imagen']) ?>" alt=""></div>
    <?php endif; ?>
    <div class="text"><?= render_contenido($noticia['contenido']) ?></div>
<?php endif; ?>
</section>

<?php if ($otras): ?>
<section class="blog">
    <h1 class="heading-title">Otras noticias</h1>
    <div class="container swiper">
        <div class="card-wrapper">
            <ul class="card-list swiper-wrapper">
                <?php foreach ($otras as $n): ?>
                    <li class="card-item swiper-slide">
                        <a href="noticia.php?id=<?= (int) $n['id'] ?>" class="card-link">
                            <img src="<?= e(img_src($n['imagen'])) ?>" alt="" class="card-image">
                            <p class="badge"><?= e(fecha_formato($n['fecha_publicacion'])) ?></p>
                            <h2 class="card-title"><?= e($n['titulo']) ?></h2>
                            <p class="card-text"><?= e($n['resumen']) ?></p>
                        </a>
                    </li>
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
