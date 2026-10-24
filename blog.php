<?php
declare(strict_types=1);
$titulo = 'Blog';
$paginaActiva = 'blog';
require __DIR__ . '/includes/header.php';
$noticias = noticias_publicadas(50);
?>
<div class="heading" style="background:url(imagenes/heading4.jpg) no-repeat">
    <h1>Blog</h1>
</div>
<section class="info">
    <p>El sector inmobiliario evoluciona constantemente, y nuestro blog es tu conexión directa con las tendencias del mercado: noticias relevantes, análisis y consejos prácticos de nuestros expertos.</p>
</section>
<section class="blog">
    <h1 class="heading-title">Últimas noticias</h1>
    <?php if (!$noticias): ?>
        <p class="vacio">Todavía no hay noticias publicadas.</p>
    <?php else: ?>
        <ul class="card-list grilla-noticias">
            <?php foreach ($noticias as $n): ?>
                <li class="card-item">
                    <a href="noticia.php?id=<?= (int) $n['id'] ?>" class="card-link">
                        <img src="<?= e(img_src($n['imagen'])) ?>" alt="" class="card-image">
                        <p class="badge"><?= e(fecha_formato($n['fecha_publicacion'])) ?></p>
                        <h2 class="card-title"><?= e($n['titulo']) ?></h2>
                        <p class="card-text"><?= e($n['resumen']) ?></p>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
