<?php
/** ABM Propiedades - alta y modificacion (con imagenes, en una transaccion). */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/repositorio.php';
require_once __DIR__ . '/../includes/upload.php';
$usuario = requiere_rol('ADMIN', 'VENDEDOR');

$id = get_int('id');
$existente = null;
if ($id > 0) {
    $existente = propiedad_obtener($id);
    if ($existente === null) {
        flash('error', 'La propiedad no existe.');
        redirigir('propiedades.php');
    }
    if (!puede_editar_propiedad($usuario, $existente)) {
        http_response_code(403);
        exit('No puede modificar propiedades de otro agente.');
    }
}

$tipos   = tipos_propiedad();
$agentes = agentes_activos();
$campos  = ['titulo', 'descripcion', 'tipo_propiedad_id', 'operacion', 'precio', 'moneda', 'direccion', 'barrio',
            'ciudad', 'ambientes', 'dormitorios', 'banos', 'superficie_m2', 'estado', 'agente_id'];
$datos = [];
foreach ($campos as $c) {
    $datos[$c] = $existente !== null ? (string) $existente[$c] : '';
}
$datos['destacada'] = $existente !== null ? (string) $existente['destacada'] : '0';
if ($existente === null) {
    $datos = array_merge($datos, ['operacion' => 'VENTA', 'moneda' => 'USD', 'ciudad' => 'CABA', 'estado' => 'DISPONIBLE',
                                  'ambientes' => '1', 'dormitorios' => '0', 'banos' => '1', 'agente_id' => (string) $usuario['id']]);
}
$imagenes = $id > 0 ? propiedad_imagenes($id) : [];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    foreach ($campos as $c) {
        $datos[$c] = post($c);
    }
    $datos['destacada'] = isset($_POST['destacada']) ? '1' : '0';
    if ($usuario['rol'] === 'VENDEDOR') {
        $datos['agente_id'] = (string) $usuario['id'];        // el vendedor no puede asignar a otro agente
    }

    $v = new Validador();
    $v->texto('titulo', $datos['titulo'], 'Título', 5, 120);
    $v->texto('descripcion', $datos['descripcion'], 'Descripción', 0, 5000);
    $v->enLista('tipo_propiedad_id', $datos['tipo_propiedad_id'], array_map(fn($t) => (string) $t['id'], $tipos), 'Tipo');
    $v->enLista('operacion', $datos['operacion'], OPERACIONES, 'Operación');
    $precio = $v->decimal('precio', $datos['precio'], 'Precio', 1, 999999999999);
    $v->enLista('moneda', $datos['moneda'], ['USD', 'ARS'], 'Moneda');
    $v->texto('direccion', $datos['direccion'], 'Dirección', 5, 150);
    $v->texto('barrio', $datos['barrio'], 'Barrio', 2, 60);
    $v->texto('ciudad', $datos['ciudad'], 'Ciudad', 2, 60);
    $ambientes   = $v->entero('ambientes', $datos['ambientes'], 'Ambientes', 1, 50);
    $dormitorios = $v->entero('dormitorios', $datos['dormitorios'], 'Dormitorios', 0, 50);
    $banos       = $v->entero('banos', $datos['banos'], 'Baños', 0, 20);
    $superficie  = $v->decimal('superficie_m2', $datos['superficie_m2'], 'Superficie', 1, 100000);
    $v->enLista('estado', $datos['estado'], ESTADOS_PROPIEDAD, 'Estado');
    $v->enLista('agente_id', $datos['agente_id'], array_map(fn($a) => (string) $a['id'], $agentes), 'Agente');
    if ($ambientes !== null && $dormitorios !== null && $dormitorios > $ambientes) {
        $v->agregar('dormitorios', 'Los dormitorios no pueden superar la cantidad de ambientes.');
    }
    $nuevasImagenes = normalizar_archivos($_FILES['imagenes'] ?? null);
    if ($existente === null && !$nuevasImagenes) {
        $v->agregar('imagenes', 'Cargue al menos una imagen de la propiedad.');
    }

    if ($v->ok()) {
        $pdo = db();
        $guardadas = [];
        try {
            $pdo->beginTransaction();
            $valores = [
                ':tipo' => (int) $datos['tipo_propiedad_id'], ':agente' => (int) $datos['agente_id'],
                ':titulo' => $datos['titulo'], ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
                ':operacion' => $datos['operacion'], ':precio' => $precio, ':moneda' => $datos['moneda'],
                ':direccion' => $datos['direccion'], ':barrio' => $datos['barrio'], ':ciudad' => $datos['ciudad'],
                ':ambientes' => $ambientes, ':dormitorios' => $dormitorios, ':banos' => $banos,
                ':superficie' => $superficie, ':destacada' => (int) $datos['destacada'], ':estado' => $datos['estado'],
            ];
            if ($existente === null) {
                $sql = 'INSERT INTO propiedades (tipo_propiedad_id, agente_id, creado_por, titulo, descripcion, operacion, precio, moneda,
                            direccion, barrio, ciudad, ambientes, dormitorios, banos, superficie_m2, destacada, estado)
                        VALUES (:tipo, :agente, :creador, :titulo, :descripcion, :operacion, :precio, :moneda,
                            :direccion, :barrio, :ciudad, :ambientes, :dormitorios, :banos, :superficie, :destacada, :estado)';
                $valores[':creador'] = $usuario['id'];
                $pdo->prepare($sql)->execute($valores);
                $id = (int) $pdo->lastInsertId();
            } else {
                $sql = 'UPDATE propiedades SET tipo_propiedad_id = :tipo, agente_id = :agente, titulo = :titulo, descripcion = :descripcion,
                            operacion = :operacion, precio = :precio, moneda = :moneda, direccion = :direccion, barrio = :barrio,
                            ciudad = :ciudad, ambientes = :ambientes, dormitorios = :dormitorios, banos = :banos,
                            superficie_m2 = :superficie, destacada = :destacada, estado = :estado
                        WHERE id = :id';
                $valores[':id'] = $id;
                $pdo->prepare($sql)->execute($valores);
            }

            // Baja de imagenes marcadas
            $borrar = array_map('intval', (array) ($_POST['borrar_imagen'] ?? []));
            $aBorrarDelDisco = [];
            foreach ($imagenes as $img) {
                if (in_array((int) $img['id'], $borrar, true)) {
                    $pdo->prepare('DELETE FROM propiedad_imagenes WHERE id = ? AND propiedad_id = ?')->execute([$img['id'], $id]);
                    $aBorrarDelDisco[] = $img['ruta'];
                }
            }
            // Alta de imagenes nuevas
            $orden = count($imagenes);
            $ins = $pdo->prepare('INSERT INTO propiedad_imagenes (propiedad_id, ruta, es_portada, orden) VALUES (?, ?, 0, ?)');
            foreach ($nuevasImagenes as $archivo) {
                $ruta = subir_imagen($archivo, 'propiedades');
                $guardadas[] = $ruta;
                $ins->execute([$id, $ruta, ++$orden]);
            }
            // Portada: la elegida o, si no hay ninguna, la primera
            $portada = post_int('portada');
            $pdo->prepare('UPDATE propiedad_imagenes SET es_portada = (id = ?) WHERE propiedad_id = ?')->execute([$portada, $id]);
            $hayPortada = $pdo->prepare('SELECT COUNT(*) FROM propiedad_imagenes WHERE propiedad_id = ? AND es_portada = 1');
            $hayPortada->execute([$id]);
            if ((int) $hayPortada->fetchColumn() === 0) {
                $pdo->prepare('UPDATE propiedad_imagenes SET es_portada = 1 WHERE propiedad_id = ? ORDER BY orden, id LIMIT 1')->execute([$id]);
            }
            $restantes = $pdo->prepare('SELECT COUNT(*) FROM propiedad_imagenes WHERE propiedad_id = ?');
            $restantes->execute([$id]);
            if ((int) $restantes->fetchColumn() === 0) {
                throw new RuntimeException('La propiedad debe conservar al menos una imagen.');
            }

            $pdo->commit();
            foreach ($aBorrarDelDisco as $ruta) {
                borrar_archivo_subido($ruta);
            }
            flash('exito', $existente === null ? "Propiedad #$id creada correctamente." : "Propiedad #$id actualizada.");
            redirigir('propiedades.php');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();          // ACID: si algo falla no queda nada a medias
            }
            foreach ($guardadas as $ruta) {
                borrar_archivo_subido($ruta);
            }
            if ($existente === null) {
                $id = 0;
            }
            error_log('[ABM propiedad] ' . $ex->getMessage());
            $v->agregar('imagenes', ($ex instanceof RuntimeException && !$ex instanceof PDOException) ? $ex->getMessage() : 'No se pudo guardar la propiedad. Intente nuevamente.');
        }
    }
    $errores = $v->errores();
}

$titulo = $existente === null ? 'Nueva propiedad' : 'Modificar propiedad #' . (int) $existente['id'];
require __DIR__ . '/../includes/admin_header.php';

$campoTexto = function (string $campo, string $etiqueta, string $tipo = 'text', string $extra = '') use ($datos, $errores): string {
    return '<div class="campo"><label for="' . $campo . '">' . e($etiqueta) . '</label>'
        . '<input id="' . $campo . '" name="' . $campo . '" type="' . $tipo . '" value="' . viejo($datos, $campo) . '" ' . $extra . '>'
        . error_campo($errores, $campo) . '</div>';
};
?>
<form method="post" enctype="multipart/form-data" class="form-admin" data-validar-form novalidate>
    <?= csrf_campo() ?>
    <div class="grilla">
        <?= $campoTexto('titulo', 'Título *', 'text', 'maxlength="120" required data-validar="texto:5:120"') ?>
        <div class="campo"><label for="tipo_propiedad_id">Tipo *</label>
            <select id="tipo_propiedad_id" name="tipo_propiedad_id" required data-validar="requerido">
                <option value="">Seleccione...</option>
                <?php foreach ($tipos as $t): ?><option value="<?= (int) $t['id'] ?>" <?= $datos['tipo_propiedad_id'] === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['nombre']) ?></option><?php endforeach; ?>
            </select><?= error_campo($errores, 'tipo_propiedad_id') ?></div>
        <div class="campo"><label for="operacion">Operación *</label>
            <select id="operacion" name="operacion"><?php foreach (OPERACIONES as $o): ?><option <?= $datos['operacion'] === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select>
            <?= error_campo($errores, 'operacion') ?></div>
        <?= $campoTexto('precio', 'Precio *', 'number', 'min="1" step="0.01" required data-validar="decimal:1:999999999999"') ?>
        <div class="campo"><label for="moneda">Moneda *</label>
            <select id="moneda" name="moneda"><?php foreach (['USD', 'ARS'] as $m): ?><option <?= $datos['moneda'] === $m ? 'selected' : '' ?>><?= $m ?></option><?php endforeach; ?></select></div>
        <?= $campoTexto('direccion', 'Dirección *', 'text', 'maxlength="150" required data-validar="texto:5:150"') ?>
        <?= $campoTexto('barrio', 'Barrio *', 'text', 'maxlength="60" required data-validar="texto:2:60"') ?>
        <?= $campoTexto('ciudad', 'Ciudad *', 'text', 'maxlength="60" required data-validar="texto:2:60"') ?>
        <?= $campoTexto('ambientes', 'Ambientes *', 'number', 'min="1" max="50" required data-validar="entero:1:50"') ?>
        <?= $campoTexto('dormitorios', 'Dormitorios *', 'number', 'min="0" max="50" required data-validar="entero:0:50"') ?>
        <?= $campoTexto('banos', 'Baños *', 'number', 'min="0" max="20" required data-validar="entero:0:20"') ?>
        <?= $campoTexto('superficie_m2', 'Superficie m² *', 'number', 'min="1" step="0.01" required data-validar="decimal:1:100000"') ?>
        <div class="campo"><label for="estado">Estado *</label>
            <select id="estado" name="estado"><?php foreach (ESTADOS_PROPIEDAD as $e): ?><option <?= $datos['estado'] === $e ? 'selected' : '' ?>><?= $e ?></option><?php endforeach; ?></select></div>
        <?php if ($usuario['rol'] === 'ADMIN'): ?>
        <div class="campo"><label for="agente_id">Agente responsable *</label>
            <select id="agente_id" name="agente_id"><?php foreach ($agentes as $a): ?><option value="<?= (int) $a['id'] ?>" <?= $datos['agente_id'] === (string) $a['id'] ? 'selected' : '' ?>><?= e($a['apellido'] . ', ' . $a['nombre']) ?></option><?php endforeach; ?></select>
            <?= error_campo($errores, 'agente_id') ?></div>
        <?php endif; ?>
        <div class="campo campo-check"><label><input type="checkbox" name="destacada" value="1" <?= $datos['destacada'] === '1' ? 'checked' : '' ?>> Mostrar como destacada en el inicio</label></div>
    </div>
    <div class="campo"><label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="5" maxlength="5000"><?= viejo($datos, 'descripcion') ?></textarea>
        <?= error_campo($errores, 'descripcion') ?></div>

    <fieldset>
        <legend>Imágenes (JPG, PNG o WEBP, máx. 2 MB c/u)</legend>
        <?php if ($imagenes): ?>
            <div class="imagenes-actuales">
                <?php foreach ($imagenes as $img): ?>
                    <div class="imagen-item">
                        <img src="<?= e(img_src($img['ruta'], '../')) ?>" alt="">
                        <label><input type="radio" name="portada" value="<?= (int) $img['id'] ?>" <?= $img['es_portada'] ? 'checked' : '' ?>> Portada</label>
                        <label><input type="checkbox" name="borrar_imagen[]" value="<?= (int) $img['id'] ?>"> Eliminar</label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <input type="file" name="imagenes[]" accept="image/jpeg,image/png,image/webp" multiple>
        <?= error_campo($errores, 'imagenes') ?>
    </fieldset>

    <div class="botonera">
        <button type="submit" class="boton"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        <a href="propiedades.php" class="boton boton-secundario">Cancelar</a>
    </div>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
