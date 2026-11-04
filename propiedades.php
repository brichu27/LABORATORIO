<?php
declare(strict_types=1);
$titulo = 'Propiedades';
$paginaActiva = 'propiedades';
require __DIR__ . '/includes/header.php';

const POR_PAGINA = 8;
$filtros = [
    'operacion' => in_array(get_str('operacion'), OPERACIONES, true) ? get_str('operacion') : '',
    'barrio'    => get_str('barrio'),
    'tipo'      => get_str('tipo'),
    'orden'     => isset(ORDENES[get_str('orden')]) ? get_str('orden') : '',
];
$barrios = barrios_disponibles();
if ($filtros['barrio'] !== '' && !in_array($filtros['barrio'], $barrios, true)) {
    $filtros['barrio'] = '';
}
$tipos = tipos_propiedad();
if ($filtros['tipo'] !== '' && !in_array($filtros['tipo'], array_map(fn($t) => (string) $t['id'], $tipos), true)) {
    $filtros['tipo'] = '';
}
$total       = propiedades_contar($filtros);
$propiedades = propiedades_listar($filtros, 0, POR_PAGINA);
?>
<div class="heading" style="background-image: url(imagenes/heading3.jpg)">
    <h1>Nuestras propiedades</h1>
</div>

<section class="properties">
    <h2 class="heading-title">Propiedades disponibles</h2>

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
        <label>Tipo
            <select name="tipo">
                <option value="">Todos</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= $filtros['tipo'] === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Ordenar por
            <select name="orden">
                <?php foreach (ORDENES as $clave => [$texto]): ?>
                    <option value="<?= e($clave) ?>" <?= ($filtros['orden'] ?: 'recientes') === $clave ? 'selected' : '' ?>><?= e($texto) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="btn">Filtrar</button>
        <a href="propiedades.php" class="btn--texto">Limpiar filtros</a>
    </form>

    <p class="resultado-total"><?= $total === 1 ? '1 propiedad encontrada' : $total . ' propiedades encontradas' ?></p>

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
            <button type="button" class="btn btn--secundario"
                    data-cargar-mas
                    data-contenedor="#lista-propiedades"
                    data-offset="<?= POR_PAGINA ?>"
                    data-limite="<?= POR_PAGINA ?>"
                    data-filtros="<?= e(http_build_query(array_filter($filtros))) ?>">Cargar más</button>
            <p class="error-msg" data-cargar-mas-error></p>
        </div>
    <?php endif; ?>
</section>

<section class="property">
    <div class="image"><img src="imagenes/propiedades1.jpg" alt="" width="1160" height="625" loading="lazy"></div>
    <div class="content">
        <h3>¿Por qué elegir Viviendas UB?</h3>
        <p>En Viviendas UB simplificamos tu búsqueda para que encuentres tu nuevo hogar de manera rápida y sencilla. Nuestro equipo de profesionales está preparado para guiarte en cada paso, haciendo que la experiencia de compra o alquiler sea ágil y sin estrés.</p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
