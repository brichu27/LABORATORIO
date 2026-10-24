<?php
declare(strict_types=1);
$titulo = 'Propiedades';
$paginaActiva = 'propiedades';
require __DIR__ . '/includes/header.php';

const POR_PAGINA = 6;
$filtros = [
    'operacion' => in_array(get_str('operacion'), OPERACIONES, true) ? get_str('operacion') : '',
    'barrio'    => get_str('barrio'),
];
$barrios = barrios_disponibles();
if ($filtros['barrio'] !== '' && !in_array($filtros['barrio'], $barrios, true)) {
    $filtros['barrio'] = '';
}
$total       = propiedades_contar($filtros);
$propiedades = propiedades_listar($filtros, 0, POR_PAGINA);
?>
<div class="heading" style="background:url(imagenes/heading3.jpg) no-repeat">
    <h1>Nuestras propiedades</h1>
</div>

<section class="property">
    <div class="image"><img src="imagenes/propiedades1.jpg" alt=""></div>
    <div class="content">
        <h3>¿Por qué elegir Viviendas UB?</h3>
        <p>En Viviendas UB simplificamos tu búsqueda para que encuentres tu nuevo hogar de manera rápida y sencilla. Nuestro equipo de profesionales está preparado para guiarte en cada paso, haciendo que la experiencia de compra o alquiler sea ágil y sin estrés.</p>
    </div>
</section>

<section class="properties">
    <h1 class="heading-title">Propiedades disponibles</h1>

    <form class="filtros" method="get" action="propiedades.php">
        <label>Operación
            <select name="operacion">
                <option value="">Todas</option>
                <?php foreach (OPERACIONES as $op): ?>
                    <option value="<?= e($op) ?>" <?= $filtros['operacion'] === $op ? 'selected' : '' ?>><?= e(ucfirst(strtolower($op))) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Barrio
            <select name="barrio">
                <option value="">Todos</option>
                <?php foreach ($barrios as $b): ?>
                    <option value="<?= e($b) ?>" <?= $filtros['barrio'] === $b ? 'selected' : '' ?>><?= e($b) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="btn2">Filtrar</button>
        <a href="propiedades.php" class="btn3-link">Limpiar</a>
    </form>

    <p class="resultado-total"><?= $total ?> propiedad(es) encontradas</p>

    <?php if (!$propiedades): ?>
        <p class="vacio">No hay propiedades que coincidan con la búsqueda.</p>
    <?php endif; ?>

    <div class="box-container" id="lista-propiedades">
        <?php foreach ($propiedades as $p): ?>
            <?= render_tarjeta_propiedad($p) ?>
        <?php endforeach; ?>
    </div>

    <?php if ($total > POR_PAGINA): ?>
        <div class="load-more">
            <button type="button" class="btn2"
                    data-cargar-mas
                    data-contenedor="#lista-propiedades"
                    data-offset="<?= POR_PAGINA ?>"
                    data-limite="<?= POR_PAGINA ?>"
                    data-filtros="<?= e(http_build_query(array_filter($filtros))) ?>">Cargar más</button>
            <p class="error-msg" data-cargar-mas-error></p>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
