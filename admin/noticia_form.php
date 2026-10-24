<?php
/** ABM Noticias - alta y modificacion (solo ADMIN). */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/repositorio.php';
require_once __DIR__ . '/../includes/upload.php';
$usuario = requiere_rol('ADMIN');

$id = get_int('id');
$existente = $id > 0 ? noticia_obtener($id, false) : null;
if ($id > 0 && $existente === null) {
    flash('error', 'La noticia no existe.');
    redirigir('noticias.php');
}
$datos = [
    'titulo'            => $existente['titulo'] ?? '',
    'resumen'           => $existente['resumen'] ?? '',
    'contenido'         => $existente['contenido'] ?? '',
    'fecha_publicacion' => $existente['fecha_publicacion'] ?? date('Y-m-d'),
    'publicada'         => (string) ($existente['publicada'] ?? '1'),
];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    foreach (['titulo', 'resumen', 'contenido', 'fecha_publicacion'] as $c) {
        $datos[$c] = post($c);
    }
    $datos['publicada'] = isset($_POST['publicada']) ? '1' : '0';

    $v = new Validador();
    $v->texto('titulo', $datos['titulo'], 'Título', 5, 150);
    $v->texto('resumen', $datos['resumen'], 'Resumen', 10, 300);
    $v->texto('contenido', $datos['contenido'], 'Contenido', 50, 60000);
    $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha_publicacion']);
    if (!$fecha || $fecha->format('Y-m-d') !== $datos['fecha_publicacion']) {
        $v->agregar('fecha_publicacion', 'Ingrese una fecha válida.');
    }
    $archivo = $_FILES['imagen'] ?? null;
    $hayArchivo = $archivo && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if ($v->ok()) {
        $nuevaImagen = null;
        try {
            if ($hayArchivo) {
                $nuevaImagen = subir_imagen($archivo, 'noticias');
            }
            $params = [
                ':titulo' => $datos['titulo'], ':resumen' => $datos['resumen'], ':contenido' => $datos['contenido'],
                ':fecha' => $datos['fecha_publicacion'], ':publicada' => (int) $datos['publicada'],
                ':imagen' => $nuevaImagen ?? ($existente['imagen'] ?? null),
            ];
            if ($existente === null) {
                $params[':autor'] = $usuario['id'];
                db()->prepare('INSERT INTO noticias (titulo, resumen, contenido, fecha_publicacion, publicada, imagen, autor_id)
                               VALUES (:titulo, :resumen, :contenido, :fecha, :publicada, :imagen, :autor)')->execute($params);
                flash('exito', 'Noticia creada.');
            } else {
                $params[':id'] = $id;
                db()->prepare('UPDATE noticias SET titulo = :titulo, resumen = :resumen, contenido = :contenido,
                               fecha_publicacion = :fecha, publicada = :publicada, imagen = :imagen WHERE id = :id')->execute($params);
                if ($nuevaImagen !== null) {
                    borrar_archivo_subido($existente['imagen']);
                }
                flash('exito', "Noticia #$id actualizada.");
            }
            redirigir('noticias.php');
        } catch (RuntimeException $ex) {
            borrar_archivo_subido($nuevaImagen);
            $v->agregar('imagen', $ex instanceof PDOException ? 'No se pudo guardar la noticia.' : $ex->getMessage());
        }
    }
    $errores = $v->errores();
}

$titulo = $existente === null ? 'Nueva noticia' : 'Modificar noticia #' . $id;
require __DIR__ . '/../includes/admin_header.php';
?>
<form method="post" enctype="multipart/form-data" class="form-admin" data-validar-form novalidate>
    <?= csrf_campo() ?>
    <div class="campo"><label for="titulo">Título *</label>
        <input id="titulo" name="titulo" maxlength="150" required data-validar="texto:5:150" value="<?= viejo($datos, 'titulo') ?>"><?= error_campo($errores, 'titulo') ?></div>
    <div class="campo"><label for="resumen">Resumen (se muestra en la tarjeta) *</label>
        <input id="resumen" name="resumen" maxlength="300" required data-validar="texto:10:300" value="<?= viejo($datos, 'resumen') ?>"><?= error_campo($errores, 'resumen') ?></div>
    <div class="campo"><label for="contenido">Contenido *</label>
        <small>Separe párrafos con una línea en blanco. Use "## " al inicio para subtítulos y "- " para ítems de lista.</small>
        <textarea id="contenido" name="contenido" rows="14" required data-validar="texto:50:60000"><?= viejo($datos, 'contenido') ?></textarea><?= error_campo($errores, 'contenido') ?></div>
    <div class="grilla">
        <div class="campo"><label for="fecha_publicacion">Fecha de publicación *</label>
            <input id="fecha_publicacion" type="date" name="fecha_publicacion" required data-validar="requerido" value="<?= viejo($datos, 'fecha_publicacion') ?>"><?= error_campo($errores, 'fecha_publicacion') ?></div>
        <div class="campo"><label for="imagen">Imagen (JPG/PNG/WEBP, máx. 2 MB)</label>
            <?php if (!empty($existente['imagen'])): ?><img src="<?= e(img_src($existente['imagen'], '../')) ?>" alt="" class="miniatura"><?php endif; ?>
            <input id="imagen" type="file" name="imagen" accept="image/jpeg,image/png,image/webp"><?= error_campo($errores, 'imagen') ?></div>
        <div class="campo campo-check"><label><input type="checkbox" name="publicada" value="1" <?= $datos['publicada'] === '1' ? 'checked' : '' ?>> Publicada (visible en el sitio)</label></div>
    </div>
    <div class="botonera">
        <button type="submit" class="boton"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        <a href="noticias.php" class="boton boton-secundario">Cancelar</a>
    </div>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
