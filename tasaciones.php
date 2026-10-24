<?php
/** Tasaciones: contenido informativo + formulario ESPECIFICO de solicitud de tasacion. */
declare(strict_types=1);
require_once __DIR__ . '/includes/repositorio.php';

$tipos = tipos_propiedad();
$idsTipos = array_map(fn($t) => (string) $t['id'], $tipos);
$errores = [];
$datos = ['nombre' => '', 'email' => '', 'telefono' => '', 'direccion_inmueble' => '',
          'tipo_propiedad_id' => '', 'superficie_aprox' => '', 'mensaje' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    foreach (array_keys($datos) as $campo) {
        $datos[$campo] = post($campo);
    }
    if (es_spam()) {
        redirigir('tasaciones.php');
    }
    $v = new Validador();
    $v->nombre('nombre', $datos['nombre'], 'Nombre y apellido');
    $v->email('email', $datos['email']);
    $v->telefono('telefono', $datos['telefono']);
    $v->texto('direccion_inmueble', $datos['direccion_inmueble'], 'Dirección del inmueble', 5, 150);
    $v->enLista('tipo_propiedad_id', $datos['tipo_propiedad_id'], $idsTipos, 'Tipo de propiedad');
    $superficie = $v->decimal('superficie_aprox', $datos['superficie_aprox'], 'Superficie aproximada', 1, 100000, true);
    if ($datos['mensaje'] !== '') {
        $v->texto('mensaje', $datos['mensaje'], 'Comentarios', 0, 1000);
    }
    if ($v->ok()) {
        $st = db()->prepare('INSERT INTO solicitudes_tasacion
            (tipo_propiedad_id, nombre, email, telefono, direccion_inmueble, superficie_aprox, mensaje)
            VALUES (?, ?, ?, ?, ?, ?, ?)');
        $st->execute([(int) $datos['tipo_propiedad_id'], $datos['nombre'], strtolower($datos['email']), $datos['telefono'],
                      $datos['direccion_inmueble'], $superficie, $datos['mensaje'] !== '' ? $datos['mensaje'] : null]);
        flash('exito', '¡Solicitud recibida! Un tasador te contactará para coordinar la visita.');
        redirigir('tasaciones.php#form-tasacion');
    }
    $errores = $v->errores();
}

$titulo = 'Tasaciones';
$paginaActiva = 'tasaciones';
require __DIR__ . '/includes/header.php';
?>
<div class="heading" style="background:url(imagenes/slide-3.jpg) no-repeat">
    <h1>Tasaciones</h1>
</div>

<section class="info">
    <p>En Viviendas UB entendemos que tu inmueble es más que una simple edificación. Es un hogar lleno de recuerdos, una inversión de futuro o un patrimonio familiar. Por eso, cuando llega el momento de preguntarte cuánto vale tu propiedad, queremos que cuentes con la información más precisa y confiable.</p>
</section>

<section class="about">
    <div class="image"><img src="imagenes/propiedades.jpg" alt=""></div>
    <div class="contentt">
        <h3>Tasación de inmuebles</h3>
        <p>La tasación de una propiedad es un trabajo delicado donde intervienen el año de construcción, los metros cuadrados, el tipo de propiedad y su estado, además de la competencia, los comparables y el momento económico del mercado.</p>
        <p>Nuestro objetivo es ayudarte a establecer un precio de venta competitivo que refleje el valor real de tu propiedad.</p>
    </div>
</section>

<section class="tasacion">
    <h1 class="heading-title">Proceso</h1>
    <div class="text">
        <h4>Inspección de la propiedad:</h4>
        <p>El primer paso implica que un tasador visite la propiedad y evalúe su estado general, características y ubicación.</p>
        <h4>Análisis del mercado:</h4>
        <p>El tasador investiga las ventas recientes de propiedades similares en la zona para determinar el valor actual.</p>
        <h4>Métodos de valoración:</h4>
        <p>Se aplica el enfoque comparativo de mercado, el de costo o el de ingresos, según la naturaleza de la propiedad.</p>
        <h4>El informe de tasación:</h4>
        <p>Finalmente se elabora un informe con la descripción de la propiedad, el análisis de mercado y la valoración final.</p>
    </div>
</section>

<section class="booking" id="form-tasacion">
    <h1 class="heading-title">Solicitar tasación</h1>
    <p>Completá los datos del inmueble y coordinamos una visita sin cargo.</p>
    <form method="post" action="tasaciones.php#form-tasacion" class="book-form" data-validar-form novalidate>
        <?= csrf_campo() ?>
        <?php include __DIR__ . '/includes/form_honeypot.php'; ?>
        <div class="flex">
            <div class="inputBox">
                <label for="nombre">Nombre y apellido *</label>
                <input id="nombre" type="text" name="nombre" maxlength="80" required data-validar="nombre" value="<?= viejo($datos, 'nombre') ?>">
                <?= error_campo($errores, 'nombre') ?>
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
                <label for="direccion_inmueble">Dirección del inmueble *</label>
                <input id="direccion_inmueble" type="text" name="direccion_inmueble" maxlength="150" required data-validar="texto:5:150" value="<?= viejo($datos, 'direccion_inmueble') ?>" placeholder="Calle, número, barrio">
                <?= error_campo($errores, 'direccion_inmueble') ?>
            </div>
            <div class="inputBox">
                <label for="tipo_propiedad_id">Tipo de propiedad *</label>
                <select id="tipo_propiedad_id" name="tipo_propiedad_id" required data-validar="requerido">
                    <option value="">Seleccione...</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= (int) $t['id'] ?>" <?= $datos['tipo_propiedad_id'] === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= error_campo($errores, 'tipo_propiedad_id') ?>
            </div>
            <div class="inputBox">
                <label for="superficie_aprox">Superficie aproximada (m²)</label>
                <input id="superficie_aprox" type="number" name="superficie_aprox" min="1" max="100000" step="0.01" data-validar="decimal:1:100000:opcional" value="<?= viejo($datos, 'superficie_aprox') ?>">
                <?= error_campo($errores, 'superficie_aprox') ?>
            </div>
            <div class="inputBox">
                <label for="mensaje">Comentarios</label>
                <textarea id="mensaje" name="mensaje" rows="3" maxlength="1000" data-validar="texto:0:1000"><?= viejo($datos, 'mensaje') ?></textarea>
                <?= error_campo($errores, 'mensaje') ?>
            </div>
        </div>
        <button type="submit" class="btn">Solicitar tasación</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
