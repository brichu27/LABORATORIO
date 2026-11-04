<?php
declare(strict_types=1);
$titulo = 'Nosotros';
$paginaActiva = 'nosotros';
require __DIR__ . '/includes/header.php';
$agentes = agentes_visibles();
?>
<div class="heading" style="background-image: url(imagenes/heading.jpg)">
    <h1>Sobre nosotros</h1>
</div>

<section class="about">
    <div class="image"><img src="imagenes/nosotros1.jpg" alt="" width="1024" height="1024"></div>
    <div class="content">
        <h3>¿Por qué elegir Viviendas UB?</h3>
        <p>En Viviendas UB creamos un espacio donde agentes inmobiliarios talentosos elevan los estándares del servicio. Brindamos una atención personalizada, marcada por la dedicación y la experiencia, asegurando un proceso de venta impecable.</p>
        <div class="icons-container">
            <div class="icons"><i class="fas fa-earth-americas"></i><span>Alcance</span></div>
            <div class="icons"><i class="fas fa-hand-holding-usd"></i><span>Precios accesibles</span></div>
            <div class="icons"><i class="fas fa-lock"></i><span>Seguridad</span></div>
        </div>
    </div>
</section>

<section class="agent">
    <div class="wrapper container">
        <div class="container1">
            <h2 class="heading-title">Nuestros agentes</h2>
            <div class="items">
                <?php foreach ($agentes as $a): ?>
                    <article class="agente">
                        <img class="agente__foto" src="<?= e(img_src($a['foto'])) ?>" alt="Foto de <?= e($a['nombre'] . ' ' . $a['apellido']) ?>" width="120" height="120" loading="lazy">
                        <h3 class="agente__nombre"><?= e($a['nombre'] . ' ' . $a['apellido']) ?></h3>
                        <p class="agente__cargo"><?= e($a['cargo']) ?></p>
                        <div class="agente__contacto">
                            <a class="agente__boton" href="mailto:<?= e($a['email']) ?>" title="<?= e($a['email']) ?>" aria-label="Enviar un email a <?= e($a['nombre']) ?> (<?= e($a['email']) ?>)"><i class="fas fa-envelope" aria-hidden="true"></i></a>
                            <?php if (!empty($a['telefono'])): ?>
                                <a class="agente__boton agente__boton--wa" href="<?= e(wa_link($a['telefono'], 'Hola ' . $a['nombre'] . ', quisiera hacer una consulta.')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp de <?= e($a['nombre']) ?>: <?= e(tel_formato($a['telefono'])) ?>"><i class="fab fa-whatsapp" aria-hidden="true"></i> <?= e(tel_formato($a['telefono'])) ?></a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
