<?php
/** ABM Personal - alta y modificacion (solo ADMIN). */
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
$usuario = requiere_rol('ADMIN');
$pdo = db();

$roles = $pdo->query('SELECT id, nombre FROM roles ORDER BY id')->fetchAll();
$id = get_int('id');
$existente = null;
if ($id > 0) {
    $st = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
    $st->execute([$id]);
    $existente = $st->fetch() ?: null;
    if ($existente === null) {
        flash('error', 'El usuario no existe.');
        redirigir('usuarios.php');
    }
}
$campos = ['nombre', 'apellido', 'email', 'telefono', 'cargo', 'rol_id'];
$datos = [];
foreach ($campos as $c) {
    $datos[$c] = (string) ($existente[$c] ?? '');
}
$datos['cargo'] = $datos['cargo'] ?: 'Agente inmobiliario';
$datos['visible_web'] = (string) ($existente['visible_web'] ?? '1');
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    foreach ($campos as $c) {
        $datos[$c] = post($c);
    }
    $datos['email'] = strtolower($datos['email']);
    $datos['visible_web'] = isset($_POST['visible_web']) ? '1' : '0';
    $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';

    $v = new Validador();
    $v->nombre('nombre', $datos['nombre'], 'Nombre');
    $v->nombre('apellido', $datos['apellido'], 'Apellido');
    $v->email('email', $datos['email']);
    if ($datos['telefono'] !== '') {
        $v->telefono('telefono', $datos['telefono']);
    }
    $v->texto('cargo', $datos['cargo'], 'Cargo', 3, 60);
    $v->enLista('rol_id', $datos['rol_id'], array_map(fn($r) => (string) $r['id'], $roles), 'Rol');
    if ($existente === null || $clave !== '') {
        $v->clave('clave', $clave);
    }
    if ($existente !== null && $id === $usuario['id'] && $datos['rol_id'] !== (string) $existente['rol_id']) {
        $v->agregar('rol_id', 'No puede cambiar su propio rol.');
    }
    $dup = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ? AND id <> ?');
    $dup->execute([$datos['email'], $id]);
    if ((int) $dup->fetchColumn() > 0) {
        $v->agregar('email', 'Ya existe otro integrante con ese email.');
    }
    $archivo = $_FILES['foto'] ?? null;
    $hayFoto = $archivo && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if ($v->ok()) {
        $nuevaFoto = null;
        try {
            if ($hayFoto) {
                $nuevaFoto = subir_imagen($archivo, 'personal');
            }
            $p = [
                ':rol' => (int) $datos['rol_id'], ':nombre' => $datos['nombre'], ':apellido' => $datos['apellido'],
                ':email' => $datos['email'], ':telefono' => $datos['telefono'] !== '' ? preg_replace('/\D/', '', $datos['telefono']) : null,
                ':cargo' => $datos['cargo'], ':visible' => (int) $datos['visible_web'],
                ':foto' => $nuevaFoto ?? ($existente['foto'] ?? null),
            ];
            if ($existente === null) {
                $p[':hash'] = password_hash($clave, PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO usuarios (rol_id, nombre, apellido, email, telefono, cargo, visible_web, foto, password_hash)
                               VALUES (:rol, :nombre, :apellido, :email, :telefono, :cargo, :visible, :foto, :hash)')->execute($p);
                flash('exito', 'Integrante creado.');
            } else {
                $sqlClave = '';
                if ($clave !== '') {
                    $sqlClave = ', password_hash = :hash';
                    $p[':hash'] = password_hash($clave, PASSWORD_DEFAULT);
                }
                $p[':id'] = $id;
                $pdo->prepare("UPDATE usuarios SET rol_id = :rol, nombre = :nombre, apellido = :apellido, email = :email,
                               telefono = :telefono, cargo = :cargo, visible_web = :visible, foto = :foto $sqlClave
                               WHERE id = :id")->execute($p);
                if ($nuevaFoto !== null) {
                    borrar_archivo_subido($existente['foto']);
                }
                flash('exito', 'Integrante actualizado.');
            }
            redirigir('usuarios.php');
        } catch (RuntimeException $ex) {
            borrar_archivo_subido($nuevaFoto);
            $v->agregar('foto', $ex instanceof PDOException ? 'No se pudo guardar el integrante.' : $ex->getMessage());
        }
    }
    $errores = $v->errores();
}

$titulo = $existente === null ? 'Nuevo integrante' : 'Modificar integrante';
require __DIR__ . '/../includes/admin_header.php';
?>
<form method="post" enctype="multipart/form-data" class="form-admin" data-validar-form novalidate>
    <?= csrf_campo() ?>
    <div class="grilla">
        <div class="campo"><label for="nombre">Nombre *</label><input id="nombre" name="nombre" maxlength="60" required data-validar="nombre" value="<?= viejo($datos, 'nombre') ?>"><?= error_campo($errores, 'nombre') ?></div>
        <div class="campo"><label for="apellido">Apellido *</label><input id="apellido" name="apellido" maxlength="60" required data-validar="nombre" value="<?= viejo($datos, 'apellido') ?>"><?= error_campo($errores, 'apellido') ?></div>
        <div class="campo"><label for="email">Email (usuario de ingreso) *</label><input id="email" type="email" name="email" maxlength="120" required data-validar="email" value="<?= viejo($datos, 'email') ?>"><?= error_campo($errores, 'email') ?></div>
        <div class="campo"><label for="telefono">Teléfono / WhatsApp</label><input id="telefono" type="tel" name="telefono" maxlength="20" data-validar="telefono:opcional" value="<?= viejo($datos, 'telefono') ?>" placeholder="Ej: 11 2345-6789"><?= error_campo($errores, 'telefono') ?></div>
        <div class="campo"><label for="cargo">Cargo *</label><input id="cargo" name="cargo" maxlength="60" required data-validar="texto:3:60" value="<?= viejo($datos, 'cargo') ?>"><?= error_campo($errores, 'cargo') ?></div>
        <div class="campo"><label for="rol_id">Rol *</label>
            <select id="rol_id" name="rol_id" required data-validar="requerido">
                <option value="">Seleccione...</option>
                <?php foreach ($roles as $r): ?><option value="<?= (int) $r['id'] ?>" <?= $datos['rol_id'] === (string) $r['id'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option><?php endforeach; ?>
            </select><?= error_campo($errores, 'rol_id') ?></div>
        <div class="campo"><label for="clave"><?= $existente === null ? 'Clave *' : 'Nueva clave (dejar vacío para no cambiar)' ?></label>
            <input id="clave" type="password" name="clave" autocomplete="new-password" <?= $existente === null ? 'required data-validar="clave"' : 'data-validar="clave:opcional"' ?>>
            <small>Mínimo 8 caracteres, con letras y números.</small><?= error_campo($errores, 'clave') ?></div>
        <div class="campo"><label for="foto">Foto (JPG/PNG/WEBP, máx. 2 MB)</label>
            <?php if (!empty($existente['foto'])): ?><img src="<?= e(img_src($existente['foto'], '../')) ?>" alt="" class="miniatura redonda"><?php endif; ?>
            <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp"><?= error_campo($errores, 'foto') ?></div>
        <div class="campo campo-check"><label><input type="checkbox" name="visible_web" value="1" <?= $datos['visible_web'] === '1' ? 'checked' : '' ?>> Mostrar en "Nuestros agentes"</label></div>
    </div>
    <div class="botonera">
        <button type="submit" class="boton"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        <a href="usuarios.php" class="boton boton-secundario">Cancelar</a>
    </div>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
