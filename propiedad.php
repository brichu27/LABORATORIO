<?php
/**
 * Detalle de propiedad + formulario ESPECIFICO de consulta por propiedad.
 * La consulta queda asignada al agente responsable de la propiedad.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/repositorio.php';

$id = get_int('id');
$propiedad = $id > 0 ? propiedad_obtener($id) : null;
if ($propiedad === null || $propiedad['estado'] === 'INACTIVA') {
    http_response_code(404);
    $titulo = 'Propiedad no encontrada';
    require __DIR__ . '/includes/header.php';
    echo '<section class="info"><h1 class="heading-title">Propiedad no encontrada</h1><p>La propiedad no existe o ya no está publicada. <a href="propiedades.php">Volver al listado</a>.</p></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$errores = [];
$datos = [
    'nombre' => '', 'email' => '', 'telefono' => '', 'preferencia_contacto' => 'EMAIL',
    'mensaje' => 'Hola, quisiera recibir más información sobre la propiedad de ' . $propiedad['direccion'] . '.',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    foreach (array_keys($datos) as $campo) {
        $datos[$campo] = post($campo);
    }
    if (es_spam()) {
        redirigir('propiedad.php?id=' . $id);
    }
    $v = new Validador();
    $v->nombre('nombre', $datos['nombre'], 'Nombre y apellido');
    $v->email('email', $datos['email']);
    $v->telefono('telefono', $datos['telefono']);
    $v->enLista('preferencia_contacto', $datos['preferencia_contacto'], ['EMAIL', 'TELEFONO', 'WHATSAPP'], 'Preferencia de contacto');
    $v->texto('mensaje', $datos['mensaje'], 'Mensaje', 10, 1000);
    if ($propiedad['estado'] !== 'DISPONIBLE') {
        $v->agregar('general', 'La propiedad ya no se encuentra disponible para consultas.');
    }

    if ($v->ok()) {
        $st = db()->prepare(
            'INSERT INTO consultas (propiedad_id, agente_id, nombre, email, telefono, mensaje, preferencia_contacto)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$id, $propiedad['agente_id'], $datos['nombre'], strtolower($datos['email']),
                      $datos['telefono'], $datos['mensaje'], $datos['preferencia_contacto']]);
        flash('exito', '¡Gracias! Tu consulta fue enviada. ' . $propiedad['agente_nombre'] . ' se comunicará a la brevedad.');
        redirigir('propiedad.php?id=' . $id . '#aviso');   // patron PRG: evita reenvio con F5; el ancla lleva al mensaje de exito
    }
    $errores = $v->errores();
}

$imagenes = propiedad_imagenes($id);
$titulo = $propiedad['titulo'];
$paginaActiva = 'propiedades';
require __DIR__ . '/includes/header.php';
$direccionCompleta = $propiedad['direccion'] . ', ' . $propiedad['barrio'] . ', ' . $propiedad['ciudad'];
?>
<section class="detalle-propiedad">
    <a href="propiedades.php" class="volver"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al listado</a>

    <div class="detalle">
        <div class="detalle__principal">
            <header class="detalle__encabezado">
                <h1 class="detalle__titulo"><?= e($propiedad['titulo']) ?></h1>
                <p class="detalle__ubicacion"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?= e($direccionCompleta) ?></p>
            </header>

            <div class="galeria">
                <?php if ($imagenes): ?>
                    <img src="<?= e($imagenes[0]['ruta']) ?>" alt="<?= e($propiedad['titulo']) ?>" class="galeria-principal" id="galeria-principal" width="960" height="720">
                    <?php if (count($imagenes) > 1): ?>
                        <div class="galeria-miniaturas">
                            <?php foreach ($imagenes as $n => $img): ?>
                                <button type="button" class="galeria-mini<?= $n === 0 ? ' activa' : '' ?>" data-galeria-mini="<?= e($img['ruta']) ?>"
                                        aria-label="Ver foto <?= $n + 1 ?> de <?= count($imagenes) ?>"<?= $n === 0 ? ' aria-current="true"' : '' ?>>
                                    <img src="<?= e($img['ruta']) ?>" alt="" width="90" height="68" loading="lazy">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="<?= e(img_src(null)) ?>" alt="Sin imagen" class="galeria-principal" width="960" height="720">
                <?php endif; ?>
            </div>

            <div class="detalle__info">
                <h2>Características</h2>
                <ul class="caracteristicas">
                    <li><i class="fa-solid fa-door-open" aria-hidden="true"></i> <?= e(cantidad((int) $propiedad['ambientes'], 'ambiente', 'ambientes')) ?></li>
                    <li><i class="fa-solid fa-bed" aria-hidden="true"></i> <?= e(cantidad((int) $propiedad['dormitorios'], 'dormitorio', 'dormitorios')) ?></li>
                    <li><i class="fa-solid fa-bath" aria-hidden="true"></i> <?= e(cantidad((int) $propiedad['banos'], 'baño', 'baños')) ?></li>
                    <li><i class="fa-solid fa-ruler-combined" aria-hidden="true"></i> <?= e((string) round((float) $propiedad['superficie_m2'])) ?> m²</li>
                </ul>
                <?php if (!empty($propiedad['descripcion'])): ?>
                    <h2>Descripción</h2>
                    <p class="descripcion"><?= nl2br(e($propiedad['descripcion'])) ?></p>
                <?php endif; ?>
            </div>

            <div class="mapa">
                <h2>Ubicación</h2>
                <iframe title="Ubicación de la propiedad" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q=<?= e(rawurlencode($direccionCompleta . ', Argentina')) ?>&output=embed"></iframe>
            </div>
        </div>

        <aside class="detalle__lateral">
            <div class="ficha detalle__resumen">
                <p class="etiquetas">
                    <span><?= e(ucfirst(strtolower($propiedad['operacion']))) ?></span>
                    <span><?= e($propiedad['tipo']) ?></span>
                    <span><?= e(ucfirst(strtolower($propiedad['estado']))) ?></span>
                </p>
                <p class="precio"><?= e(precio_formato($propiedad['precio'], $propiedad['moneda'], $propiedad['operacion'])) ?></p>
                <p class="detalle__datos"><?= (int) $propiedad['ambientes'] ?> amb. · <?= (int) $propiedad['dormitorios'] ?> dorm. · <?= e((string) round((float) $propiedad['superficie_m2'])) ?> m²</p>

                <div class="agente-mini">
                    <img src="<?= e(img_src($propiedad['agente_foto'])) ?>" alt="" width="64" height="64">
                    <div>
                        <span class="agente-mini__rol">Agente responsable</span>
                        <strong><?= e($propiedad['agente_nombre'] . ' ' . $propiedad['agente_apellido']) ?></strong>
                        <a href="mailto:<?= e($propiedad['agente_email']) ?>"><i class="fas fa-envelope" aria-hidden="true"></i> <?= e($propiedad['agente_email']) ?></a>
                        <?php if (!empty($propiedad['agente_telefono'])): ?>
                            <a href="<?= e(wa_link($propiedad['agente_telefono'], 'Hola, me interesa la propiedad de ' . $propiedad['direccion'])) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp" aria-hidden="true"></i> <?= e(tel_formato($propiedad['agente_telefono'])) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="booking detalle__consulta" id="consulta">
                <h2>Consultar por esta propiedad</h2>
                <p>Tu consulta llega directamente al agente responsable.</p>
        <?php if (isset($errores['general'])): ?><div class="alerta alerta-error"><?= e($errores['general']) ?></div><?php endif; ?>
        <form method="post" action="propiedad.php?id=<?= (int) $id ?>#consulta" class="book-form" data-validar-form novalidate>
            <?= csrf_campo() ?>
            <?php include __DIR__ . '/includes/form_honeypot.php'; ?>
            <div class="flex">
                <div class="inputBox">
                    <label for="nombre">Nombre y apellido *</label>
                    <input id="nombre" type="text" name="nombre" maxlength="80" required autocomplete="name"
                           data-validar="nombre" value="<?= viejo($datos, 'nombre') ?>" placeholder="Ej: Juan Pérez">
                    <?= error_campo($errores, 'nombre') ?>
                </div>
                <div class="inputBox">
                    <label for="email">Email *</label>
                    <input id="email" type="email" name="email" maxlength="120" required autocomplete="email"
                           data-validar="email" value="<?= viejo($datos, 'email') ?>" placeholder="nombre@dominio.com">
                    <?= error_campo($errores, 'email') ?>
                </div>
                <div class="inputBox">
                    <label for="telefono">Teléfono *</label>
                    <input id="telefono" type="tel" name="telefono" maxlength="20" required autocomplete="tel" inputmode="tel"
                           data-validar="telefono" value="<?= viejo($datos, 'telefono') ?>" placeholder="Ej: 11 2345-6789">
                    <?= error_campo($errores, 'telefono') ?>
                </div>
                <div class="inputBox">
                    <label for="preferencia_contacto">¿Cómo preferís que te contactemos? *</label>
                    <select id="preferencia_contacto" name="preferencia_contacto" required>
                        <?php foreach (['EMAIL' => 'Email', 'TELEFONO' => 'Llamada telefónica', 'WHATSAPP' => 'WhatsApp'] as $val => $txt): ?>
                            <option value="<?= $val ?>" <?= $datos['preferencia_contacto'] === $val ? 'selected' : '' ?>><?= $txt ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= error_campo($errores, 'preferencia_contacto') ?>
                </div>
                <div class="inputBox">
                    <label for="mensaje">Mensaje *</label>
                    <textarea id="mensaje" name="mensaje" rows="4" maxlength="1000" required data-validar="texto:10:1000"><?= viejo($datos, 'mensaje') ?></textarea>
                    <?= error_campo($errores, 'mensaje') ?>
                </div>
            </div>
            <button type="submit" class="btn">Enviar consulta</button>
        </form>
            </div>
        </aside>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
