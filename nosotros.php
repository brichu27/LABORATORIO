<?php
declare(strict_types=1);
$titulo = 'Nosotros';
$paginaActiva = 'nosotros';
require __DIR__ . '/includes/header.php';
$agentes = agentes_visibles();
?>
<div class="heading" style="background:url(imagenes/heading.jpg) no-repeat">
    <h1>Sobre nosotros</h1>
</div>

<section class="about">
    <div class="image"><img src="imagenes/nosotros1.jpg" alt=""></div>
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
            <div class="heading-title"><h1>Nuestros agentes</h1></div>
            <div class="items">
                <?php foreach ($agentes as $a): ?>
                    <div class="card">
                        <div class="image"><img src="<?= e(img_src($a['foto'])) ?>" alt="Foto de <?= e($a['nombre'] . ' ' . $a['apellido']) ?>"></div>
                        <div class="info"><h1 class="name"><?= e($a['nombre'] . ' ' . $a['apellido']) ?></h1></div>
                        <div class="desc">
                            <p><?= e($a['cargo']) ?></p>
                            <p><a href="mailto:<?= e($a['email']) ?>" class="sin-capitalizar"><i class="fas fa-envelope"></i> <?= e($a['email']) ?></a></p>
                            <?php if (!empty($a['telefono'])): ?>
                                <p><a href="<?= e(wa_link($a['telefono'], 'Hola ' . $a['nombre'] . ', quisiera hacer una consulta.')) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> <?= e(tel_formato($a['telefono'])) ?></a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
