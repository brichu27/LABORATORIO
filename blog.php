<?php
declare(strict_types=1);
$titulo = 'Blog';
$paginaActiva = 'blog';
require __DIR__ . '/includes/header.php';

// Arriba, las más recientes en el carrusel; abajo, el resto en grilla con "Cargar más" (api/noticias.php)
const NOTICIAS_CARRUSEL  = 4;
const NOTICIAS_POR_CARGA = 12;   // divisible por 1, 2, 3 y 4: filas completas en cualquier ancho
$recientes = noticias_publicadas(NOTICIAS_CARRUSEL);
$total     = noticias_contar();
$mas       = noticias_publicadas(NOTICIAS_POR_CARGA, 0, NOTICIAS_CARRUSEL);
?>
<div class="heading" style="background-image: url(imagenes/heading4.jpg)">
    <h1>Blog</h1>
</div>
<section class="info">
    <p>El sector inmobiliario evoluciona constantemente, y nuestro blog es tu conexión directa con las tendencias del mercado: noticias relevantes, análisis y consejos prácticos de nuestros expertos.</p>
</section>
<section class="blog">
    <h2 class="heading-title">Últimas noticias</h2>
    <?php if (!$recientes): ?>
        <p class="vacio">Todavía no hay noticias publicadas.</p>
    <?php else: ?>
        <!-- Mismo carrusel que el inicio (js/script.js inicia .card-wrapper con Swiper) -->
        <div class="container swiper">
            <div class="card-wrapper">
                <ul class="card-list swiper-wrapper">
                    <?php foreach ($recientes as $n): ?>
                        <?= render_tarjeta_noticia($n, true) ?>
                    <?php endforeach; ?>
                </ul>
                <div class="swiper-pagination"></div>
                <div class="swiper-slide-button swiper-button-prev"></div>
                <div class="swiper-slide-button swiper-button-next"></div>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($mas): ?>
<section class="blog blog--archivo" aria-labelledby="titulo-mas-noticias">
    <h2 class="heading-title" id="titulo-mas-noticias">Más noticias</h2>
    <ul class="card-list grilla-noticias" id="mas-noticias">
        <?php foreach ($mas as $n): ?>
            <?= render_tarjeta_noticia($n) ?>
        <?php endforeach; ?>
    </ul>
    <?php if ($total > NOTICIAS_CARRUSEL + NOTICIAS_POR_CARGA): ?>
        <div class="load-more">
            <button type="button" class="btn btn--secundario"
                    data-cargar-mas
                    data-api="api/noticias.php"
                    data-tarjeta="noticia"
                    data-contenedor="#mas-noticias"
                    data-offset="<?= NOTICIAS_CARRUSEL + NOTICIAS_POR_CARGA ?>"
                    data-limite="<?= NOTICIAS_POR_CARGA ?>">Cargar más</button>
            <p class="error-msg" data-cargar-mas-error></p>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
