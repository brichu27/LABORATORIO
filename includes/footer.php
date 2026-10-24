<?php
declare(strict_types=1);
$empresa = config()['empresa'];
$redes = array_filter($empresa['redes'] ?? [], fn($url) => is_string($url) && $url !== '');
$iconosRedes = [
    'facebook'  => ['fab fa-facebook-f', 'Facebook'],
    'instagram' => ['fa-brands fa-instagram', 'Instagram'],
    'x'         => ['fa-brands fa-x-twitter', 'X'],
    'linkedin'  => ['fa-brands fa-linkedin', 'LinkedIn'],
];
?>
<section class="footer">
    <div class="box-container">
        <div class="box">
            <h3>Atajos</h3>
            <a href="index.php"><i class="fa-solid fa-angle-right"></i> Home</a>
            <a href="tasaciones.php"><i class="fa-solid fa-angle-right"></i> Tasaciones</a>
            <a href="propiedades.php"><i class="fa-solid fa-angle-right"></i> Propiedades</a>
            <a href="contacto.php"><i class="fa-solid fa-angle-right"></i> Unirse</a>
        </div>
        <div class="box">
            <h3>Información</h3>
            <a href="preguntas.php"><i class="fa-solid fa-angle-right"></i> Preguntas frecuentes</a>
            <a href="nosotros.php"><i class="fa-solid fa-angle-right"></i> Sobre nosotros</a>
            <a href="politica.php"><i class="fa-solid fa-angle-right"></i> Política de privacidad</a>
            <a href="blog.php"><i class="fa-solid fa-angle-right"></i> Blog</a>
            <a href="admin/login.php"><i class="fa-solid fa-lock"></i> Acceso personal</a>
        </div>
        <div class="box">
            <h3>Contactos</h3>
            <?php foreach ($empresa['telefonos'] as $tel): ?>
                <a href="<?= e(wa_link($tel)) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> <?= e(tel_formato($tel)) ?></a>
            <?php endforeach; ?>
            <a href="mailto:<?= e($empresa['email']) ?>" class="sin-capitalizar"><i class="fas fa-envelope"></i> <?= e($empresa['email']) ?></a>
            <a href="<?= e($empresa['maps_url']) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot"></i> <?= e($empresa['direccion']) ?></a>
        </div>
        <?php if ($redes): ?>
        <div class="box">
            <h3>Redes sociales</h3>
            <?php foreach ($redes as $red => $url): ?>
                <a href="<?= e($url) ?>" target="_blank" rel="noopener"><i class="<?= e($iconosRedes[$red][0] ?? 'fa-solid fa-link') ?>"></i> <?= e($iconosRedes[$red][1] ?? $red) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="credit">Copyright &copy; <?= date('Y') ?> Viviendas UB | Todos los derechos reservados.</div>
</section>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="js/validaciones.js"></script>
<script src="js/cargar-mas.js"></script>
<script src="js/script.js"></script>
</body>
</html>
