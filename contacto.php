<?php
/** "UNIRSE": formulario ESPECIFICO de postulacion para ser agente (distinto al de propiedades). */
declare(strict_types=1);
require_once __DIR__ . '/includes/repositorio.php';

$errores = [];
$datos = ['nombre' => '', 'apellido' => '', 'email' => '', 'telefono' => '', 'anios_experiencia' => '0',
          'zona_interes' => '', 'linkedin_url' => '', 'mensaje' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    foreach (array_keys($datos) as $campo) {
        $datos[$campo] = post($campo);
    }
    if (es_spam()) {
        redirigir('contacto.php');
    }
    $v = new Validador();
    $v->nombre('nombre', $datos['nombre'], 'Nombre');
    $v->nombre('apellido', $datos['apellido'], 'Apellido');
    $v->email('email', $datos['email']);
    $v->telefono('telefono', $datos['telefono']);
    $anios = $v->entero('anios_experiencia', $datos['anios_experiencia'], 'Años de experiencia', 0, 60);
    $v->texto('zona_interes', $datos['zona_interes'], 'Zona de interés', 3, 60);
    $v->url('linkedin_url', $datos['linkedin_url'], 'Perfil de LinkedIn');
    $v->texto('mensaje', $datos['mensaje'], 'Motivación', 20, 1500);

    if ($v->ok()) {
        $st = db()->prepare('INSERT INTO postulaciones
            (nombre, apellido, email, telefono, anios_experiencia, zona_interes, linkedin_url, mensaje)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $st->execute([$datos['nombre'], $datos['apellido'], strtolower($datos['email']), $datos['telefono'], $anios,
                      $datos['zona_interes'], $datos['linkedin_url'] !== '' ? $datos['linkedin_url'] : null, $datos['mensaje']]);
        flash('exito', '¡Gracias por postularte! El equipo de RR.HH. revisará tu perfil y te contactará.');
        redirigir('contacto.php#form-postulacion');
    }
    $errores = $v->errores();
}

$titulo = 'Unirse al equipo';
$paginaActiva = 'contacto';
require __DIR__ . '/includes/header.php';
?>
<div class="heading" style="background:url(imagenes/heading2.jpg) no-repeat">
    <h1>Sumate al equipo</h1>
</div>

<section class="booking" id="form-postulacion">
    <h1 class="heading-title">Postulación de agentes</h1>
    <p>¿Querés ser agente Viviendas UB? Dejanos tus datos y tu experiencia. Si buscás una propiedad, consultá desde la <a href="propiedades.php">publicación correspondiente</a>; si querés vender, pedí una <a href="tasaciones.php">tasación</a>.</p>
    <form method="post" action="contacto.php#form-postulacion" class="book-form" data-validar-form novalidate>
        <?= csrf_campo() ?>
        <?php include __DIR__ . '/includes/form_honeypot.php'; ?>
        <div class="flex">
            <div class="inputBox">
                <label for="nombre">Nombre *</label>
                <input id="nombre" type="text" name="nombre" maxlength="60" required data-validar="nombre" value="<?= viejo($datos, 'nombre') ?>">
                <?= error_campo($errores, 'nombre') ?>
            </div>
            <div class="inputBox">
                <label for="apellido">Apellido *</label>
                <input id="apellido" type="text" name="apellido" maxlength="60" required data-validar="nombre" value="<?= viejo($datos, 'apellido') ?>">
                <?= error_campo($errores, 'apellido') ?>
            </div>
            <div class="inputBox">
                <label for="email">Email *</label>
                <input id="email" type="email" name="email" maxlength="120" required data-validar="email" value="<?= viejo($datos, 'email') ?>">
                <?= error_campo($errores, 'email') ?>
            </div>
            <div class="inputBox">
                <label for="telefono">Teléfono *</label>
                <input id="telefono" type="tel" name="telefono" maxlength="20" required inputmode="tel" data-validar="telefono" value="<?= viejo($datos, 'telefono') ?>" placeholder="Ej: 11 2345-6789">
                <?= error_campo($errores, 'telefono') ?>
            </div>
            <div class="inputBox">
                <label for="anios_experiencia">Años de experiencia en el rubro *</label>
                <input id="anios_experiencia" type="number" name="anios_experiencia" min="0" max="60" step="1" required data-validar="entero:0:60" value="<?= viejo($datos, 'anios_experiencia') ?>">
                <?= error_campo($errores, 'anios_experiencia') ?>
            </div>
            <div class="inputBox">
                <label for="zona_interes">Zona en la que te gustaría trabajar *</label>
                <input id="zona_interes" type="text" name="zona_interes" maxlength="60" required data-validar="texto:3:60" value="<?= viejo($datos, 'zona_interes') ?>" placeholder="Ej: Belgrano / Zona Norte">
                <?= error_campo($errores, 'zona_interes') ?>
            </div>
            <div class="inputBox">
                <label for="linkedin_url">Perfil de LinkedIn (opcional)</label>
                <input id="linkedin_url" type="url" name="linkedin_url" maxlength="255" data-validar="url" value="<?= viejo($datos, 'linkedin_url') ?>" placeholder="https://www.linkedin.com/in/...">
                <?= error_campo($errores, 'linkedin_url') ?>
            </div>
            <div class="inputBox">
                <label for="mensaje">¿Por qué querés sumarte? *</label>
                <textarea id="mensaje" name="mensaje" rows="5" maxlength="1500" required data-validar="texto:20:1500"><?= viejo($datos, 'mensaje') ?></textarea>
                <?= error_campo($errores, 'mensaje') ?>
            </div>
        </div>
        <button type="submit" class="btn">Enviar postulación</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
