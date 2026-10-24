<?php
declare(strict_types=1);
$titulo = 'Inicio';
$paginaActiva = 'index';
require __DIR__ . '/includes/header.php';

$destacadas = propiedades_listar(['destacada' => true], 0, 8);
$noticias   = noticias_publicadas(4);
?>
<section class="home">
    <div class="swiper home-slider">
        <div class="swiper-wrapper">
            <div class="swiper-slide slide" style="background:url(imagenes/slide-1.jpg) no-repeat">
                <div class="content">
                    <span>Agencia líder en el mercado inmobiliario</span>
                    <h3>Tu próximo hogar, a un click de distancia</h3>
                    <a href="propiedades.php" class="btn">Más información</a>
                </div>
            </div>
            <div class="swiper-slide slide" style="background:url(imagenes/slide-2.jpg) no-repeat">
                <div class="content">
                    <span>Agencia líder en el mercado inmobiliario</span>
                    <h3>Diversa selección de propiedades</h3>
                    <a href="propiedades.php" class="btn">Más información</a>
                </div>
            </div>
            <div class="swiper-slide slide" style="background:url(imagenes/slide-3.jpg) no-repeat">
                <div class="content">
                    <span>Agencia líder en el mercado inmobiliario</span>
                    <h3>La compañía más elegida</h3>
                    <a href="propiedades.php" class="btn">Más información</a>
                </div>
            </div>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</section>

<section class="home-about">
    <div class="image"><img src="imagenes/nosotros2.png" alt="Equipo de Viviendas UB"></div>
    <div class="content">
        <h3>¿Quiénes somos?</h3>
        <p>En Viviendas UB, nuestra razón de ser es guiar a las personas en la importante decisión de vender o comprar una propiedad. Nuestro principal objetivo es construir relaciones duraderas con nuestros clientes, basadas en la confianza y la satisfacción, para que nos elijan nuevamente en el futuro.</p>
        <a href="nosotros.php" class="btn2">Leer más</a>
    </div>
</section>

<section class="services">
    <h1 class="heading-title">¿Interesado en propiedades?</h1>
    <p>Ya sea para adquirir, vender o arrendar, te ofrecemos diversas alternativas. Nuestros agentes especializados están listos para guiarte en la búsqueda de tu inmueble ideal, la venta exitosa de tu propiedad o un alquiler seguro y confortable.</p>
    <div class="box-container">
        <div class="box"><a href="tasaciones.php"><img src="imagenes/servicios2.png" alt="" width="100" height="90"><h3>VENTA</h3></a></div>
        <div class="box"><a href="propiedades.php?operacion=VENTA"><img src="imagenes/servicios1.png" alt="" width="100" height="90"><h3>COMPRA</h3></a></div>
        <div class="box"><a href="propiedades.php?operacion=ALQUILER"><img src="imagenes/servicios3.png" alt="" width="100" height="90"><h3>ALQUILER</h3></a></div>
        <div class="box"><a href="contacto.php"><img src="imagenes/servicios4.png" alt="" width="100" height="90"><h3>SER AGENTE</h3></a></div>
    </div>
</section>

<section class="home-property">
    <h1 class="heading-title">Propiedades destacadas</h1>
    <?php if (!$destacadas): ?>
        <p class="vacio">Por el momento no hay propiedades destacadas.</p>
    <?php else: ?>
        <div class="box-container">
            <?php foreach ($destacadas as $p): ?>
                <?= render_tarjeta_propiedad($p) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="load-more"><a href="propiedades.php" class="btn2">Ver más</a></div>
</section>

<section class="join2">
    <div class="content">
        <h3>Contactanos para más información</h3>
        <p>¿Querés vender? Pedí una tasación sin cargo. ¿Buscás propiedad? Consultá directamente desde cada publicación.</p>
        <a href="tasaciones.php#form-tasacion" class="btn">Solicitar tasación</a>
    </div>
</section>

<section class="join-us">
    <div class="content">
        <h3>Ser agente Viviendas UB</h3>
        <p>Ser agente Viviendas UB significa mucho más que una profesión: es la oportunidad de liderar tu propio negocio inmobiliario con el respaldo de una marca sólida.</p>
        <h3>¿Qué esperamos de vos?</h3>
        <p>Buscamos personas con mentalidad emprendedora y proactiva, dispuestas a construir un negocio sólido y rentable, con pasión por el sector inmobiliario y dedicación por el servicio al cliente.</p>
    </div>
    <div class="image1"><img src="imagenes/unirse2.jpg" alt="Agentes trabajando"></div>
</section>

<section class="join">
    <div class="content">
        <h3>Unirse a los agentes Viviendas UB</h3>
        <p>¡Sumate a Viviendas UB y comenzá a construir tu futuro hoy mismo!</p>
        <a href="contacto.php" class="btn2">Postularme</a>
    </div>
</section>

<section class="blog">
    <h1 class="heading-title">Nuestro blog</h1>
    <div class="container swiper">
        <div class="card-wrapper">
            <ul class="card-list swiper-wrapper">
                <?php foreach ($noticias as $n): ?>
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
    <div class="load-more"><a href="blog.php" class="btn2">Ver más noticias</a></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
