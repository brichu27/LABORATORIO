<?php
declare(strict_types=1);
$titulo = 'Inicio';
$paginaActiva = 'index';
require __DIR__ . '/includes/header.php';

$destacadas = propiedades_listar(['destacada' => true], 0, 4);   // una fila de 4 en escritorio
$noticias   = noticias_publicadas(4);
$barrios    = barrios_disponibles();
$tipos      = tipos_propiedad();
?>
<section class="hero" style="background-image: url(imagenes/slide-1.jpg)">
    <div class="hero__contenido">
        <p class="hero__bajada">Agencia inmobiliaria en la Ciudad de Buenos Aires</p>
        <h1>Tu próximo hogar, a un click de distancia</h1>
        <form class="buscador" action="propiedades.php" method="get" role="search" aria-label="Buscar propiedades">
            <label class="buscador__campo">
                <span>Operación</span>
                <select name="operacion">
                    <option value="">Comprar o alquilar</option>
                    <option value="VENTA">Comprar</option>
                    <option value="ALQUILER">Alquilar</option>
                </select>
            </label>
            <label class="buscador__campo">
                <span>Barrio</span>
                <select name="barrio">
                    <option value="">Todos los barrios</option>
                    <?php foreach ($barrios as $b): ?>
                        <option value="<?= e($b) ?>"><?= e($b) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="buscador__campo">
                <span>Tipo</span>
                <select name="tipo">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"><?= e($t['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="btn"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Buscar</button>
        </form>
    </div>
</section>

<section class="home-property">
    <h2 class="heading-title">Propiedades destacadas</h2>
    <?php if (!$destacadas): ?>
        <p class="vacio">Por el momento no hay propiedades destacadas.</p>
    <?php else: ?>
        <div class="box-container">
            <?php foreach ($destacadas as $p): ?>
                <?= render_tarjeta_propiedad($p) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="load-more"><a href="propiedades.php" class="btn btn--secundario">Ver todas las propiedades</a></div>
</section>

<section class="services">
    <h2 class="heading-title">¿Qué estás buscando?</h2>
    <p>Te acompañamos en la compra, el alquiler o la venta de tu propiedad, con agentes especializados en cada barrio.</p>
    <div class="servicios">
        <a class="servicio" href="propiedades.php?operacion=VENTA">
            <i class="fa-solid fa-key" aria-hidden="true"></i>
            <h3>Comprar</h3>
            <p>Departamentos, casas y PH en venta, con la ficha completa de cada uno.</p>
            <span class="servicio__accion">Ver propiedades en venta <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
        </a>
        <a class="servicio" href="propiedades.php?operacion=ALQUILER">
            <i class="fa-solid fa-house-user" aria-hidden="true"></i>
            <h3>Alquilar</h3>
            <p>Alquileres con el valor mensual publicado y contacto directo con el agente.</p>
            <span class="servicio__accion">Ver alquileres <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
        </a>
        <a class="servicio" href="tasaciones.php">
            <i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i>
            <h3>Vender</h3>
            <p>Tasamos tu propiedad sin cargo y la publicamos con un agente responsable.</p>
            <span class="servicio__accion">Pedir una tasación <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
        </a>
    </div>
</section>

<section class="home-about">
    <div class="image"><img src="imagenes/nosotros2.jpg" alt="Equipo de Viviendas UB" width="1024" height="1024" loading="lazy"></div>
    <div class="content">
        <h3>¿Quiénes somos?</h3>
        <p>Nuestra razón de ser es guiar a las personas en la importante decisión de comprar, vender o alquilar una propiedad, y construir relaciones basadas en la confianza para que nos vuelvan a elegir.</p>
        <a href="nosotros.php" class="btn--texto">Conocé al equipo <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
</section>

<section class="join2">
    <div class="content">
        <span class="join2__etiqueta"><i class="fas fa-house-circle-check" aria-hidden="true"></i> Tasaciones</span>
        <h3>¿Querés vender tu propiedad?</h3>
        <p>Pedí una tasación sin cargo: un tasador visita el inmueble y te entrega un informe con el valor de mercado.</p>
        <a href="tasaciones.php#form-tasacion" class="btn">Solicitar tasación</a>
    </div>
</section>

<section class="blog">
    <h2 class="heading-title">Nuestro blog</h2>
    <div class="container swiper">
        <div class="card-wrapper">
            <ul class="card-list swiper-wrapper">
                <?php foreach ($noticias as $n): ?>
                    <?= render_tarjeta_noticia($n, true) ?>
                <?php endforeach; ?>
            </ul>
            <div class="swiper-pagination"></div>
            <div class="swiper-slide-button swiper-button-prev"></div>
            <div class="swiper-slide-button swiper-button-next"></div>
        </div>
    </div>
    <div class="load-more"><a href="blog.php" class="btn btn--secundario">Ver más noticias</a></div>
</section>

<section class="sumate">
    <div class="sumate__caja">
        <img src="imagenes/unirse2.jpg" alt="Agentes de Viviendas UB trabajando" width="1024" height="1024" loading="lazy">
        <div class="sumate__texto">
            <h3>Sumate como agente</h3>
            <p>Liderá tu propio negocio inmobiliario con el respaldo de una marca sólida. Buscamos personas proactivas, con pasión por el sector y vocación de servicio.</p>
            <a href="contacto.php" class="btn">Postularme</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
