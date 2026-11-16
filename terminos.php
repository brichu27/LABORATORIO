<?php
declare(strict_types=1);
$titulo = 'Términos y condiciones';
$paginaActiva = '';
require __DIR__ . '/includes/header.php';
$empresa = config()['empresa'];
?>
<div class="heading" style="background-image: url(imagenes/heading4.jpg)">
    <h1>Términos y condiciones</h1>
</div>

<section class="legal legal--con-indice">
    <aside class="legal__indice">
        <details open>
            <summary>Contenido</summary>
            <nav aria-label="Índice de la página">
                <ol>
                    <li><a href="#objeto"><span class="legal__indice-num">1</span> Objeto del sitio</a></li>
                    <li><a href="#publicaciones"><span class="legal__indice-num">2</span> Publicaciones y precios</a></li>
                    <li><a href="#formularios"><span class="legal__indice-num">3</span> Consultas, tasaciones y postulaciones</a></li>
                    <li><a href="#uso"><span class="legal__indice-num">4</span> Uso adecuado</a></li>
                    <li><a href="#propiedad-intelectual"><span class="legal__indice-num">5</span> Propiedad intelectual</a></li>
                    <li><a href="#responsabilidad"><span class="legal__indice-num">6</span> Responsabilidad</a></li>
                    <li><a href="#modificaciones"><span class="legal__indice-num">7</span> Modificaciones</a></li>
                    <li><a href="#ley"><span class="legal__indice-num">8</span> Ley aplicable</a></li>
                    <li><a href="#contacto"><span class="legal__indice-num">9</span> Contacto</a></li>
                </ol>
            </nav>
        </details>
    </aside>
    <div class="legal__contenido">
        <p class="legal__intro">Estos términos regulan el uso del sitio web de Viviendas UB. Al navegarlo o enviar cualquiera de sus formularios, el usuario acepta las condiciones que se detallan a continuación.</p>

        <article class="legal__articulo" id="objeto">
            <h2><span class="legal__articulo-num">1</span> Objeto del sitio</h2>
            <p>El sitio presenta las propiedades en venta y en alquiler que comercializa Viviendas UB, el equipo de agentes, noticias del sector y formularios para consultar por una propiedad, solicitar una tasación o postularse como agente.</p>
        </article>

        <article class="legal__articulo" id="publicaciones">
            <h2><span class="legal__articulo-num">2</span> Publicaciones y precios</h2>
            <p>La información de cada propiedad (precio, superficie, ambientes, fotos y ubicación) es orientativa y puede cambiar sin previo aviso. La disponibilidad y las condiciones definitivas de cada operación se confirman con el agente responsable de la publicación. En los alquileres, el precio publicado corresponde al valor mensual.</p>
        </article>

        <article class="legal__articulo" id="formularios">
            <h2><span class="legal__articulo-num">3</span> Consultas, tasaciones y postulaciones</h2>
            <p>Los datos que el usuario ingresa en los formularios se utilizan únicamente para responder a su consulta, coordinar la tasación o evaluar su postulación, según corresponda, de acuerdo con la <a href="politica.php">Política de privacidad</a>. El usuario se compromete a ingresar información verdadera y propia.</p>
            <p>La tasación que se coordina desde el sitio es una estimación profesional del valor de mercado y no constituye una oferta de compra.</p>
        </article>

        <article class="legal__articulo" id="uso">
            <h2><span class="legal__articulo-num">4</span> Uso adecuado</h2>
            <p>No está permitido utilizar el sitio para enviar información falsa, publicidad no solicitada o contenido ofensivo, ni intentar acceder a áreas restringidas como el panel de administración del personal.</p>
        </article>

        <article class="legal__articulo" id="propiedad-intelectual">
            <h2><span class="legal__articulo-num">5</span> Propiedad intelectual</h2>
            <p>Los textos, fotografías, logotipos y diseño del sitio pertenecen a Viviendas UB o se utilizan con autorización. No pueden reproducirse sin consentimiento previo y por escrito.</p>
        </article>

        <article class="legal__articulo" id="responsabilidad">
            <h2><span class="legal__articulo-num">6</span> Responsabilidad</h2>
            <p>Viviendas UB procura que la información publicada sea correcta y esté actualizada, pero no garantiza la ausencia de errores ni la disponibilidad permanente del sitio. Los enlaces a sitios de terceros, como mapas o redes sociales, se ofrecen como referencia.</p>
        </article>

        <article class="legal__articulo" id="modificaciones">
            <h2><span class="legal__articulo-num">7</span> Modificaciones</h2>
            <p>Viviendas UB puede actualizar estos términos en cualquier momento. La versión vigente es la publicada en esta página.</p>
        </article>

        <article class="legal__articulo" id="ley">
            <h2><span class="legal__articulo-num">8</span> Ley aplicable</h2>
            <p>Estos términos se rigen por las leyes de la República Argentina. Ante cualquier controversia, serán competentes los tribunales ordinarios de la Ciudad Autónoma de Buenos Aires.</p>
        </article>

        <article class="legal__articulo" id="contacto">
            <h2><span class="legal__articulo-num">9</span> Contacto</h2>
            <p>Por dudas sobre estos términos, escribinos a <?= boton_copiar_email($empresa['email'], 'copiar-email--enlace', true, 'far fa-copy') ?>.</p>
        </article>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
