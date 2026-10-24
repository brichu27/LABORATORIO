<?php
/**
 * Alta / reseteo de un usuario desde la linea de comandos (CLI).
 * Uso:
 *   php database/crear_usuario.php "Nombre" "Apellido" email@dominio.com "ClaveSegura1" ADMIN
 *   php database/crear_usuario.php "Nombre" "Apellido" email@dominio.com "ClaveSegura1" VENDEDOR
 * Si el email ya existe, actualiza clave y rol y lo deja activo.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo se ejecuta por linea de comandos.');
}

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/validador.php';

[, $nombre, $apellido, $email, $clave, $rol] = array_pad($argv, 6, '');
$rol = strtoupper((string) $rol);

$errores = [];
if (!Validador::nombreValido((string) $nombre))   { $errores[] = 'Nombre invalido.'; }
if (!Validador::nombreValido((string) $apellido)) { $errores[] = 'Apellido invalido.'; }
if (!Validador::emailValido((string) $email))     { $errores[] = 'Email invalido.'; }
if (!Validador::claveValida((string) $clave))     { $errores[] = 'Clave invalida: minimo 8 caracteres, con letras y numeros.'; }
if (!in_array($rol, ['ADMIN', 'VENDEDOR'], true)) { $errores[] = 'Rol invalido: use ADMIN o VENDEDOR.'; }

if ($errores) {
    fwrite(STDERR, implode(PHP_EOL, $errores) . PHP_EOL);
    exit(1);
}

$pdo = db();
$rolId = (int) $pdo->query("SELECT id FROM roles WHERE nombre = " . $pdo->quote($rol))->fetchColumn();
if ($rolId === 0) {
    fwrite(STDERR, "No existe el rol $rol. Ejecute primero 02_datos_iniciales.sql" . PHP_EOL);
    exit(1);
}

$sql = 'INSERT INTO usuarios (rol_id, nombre, apellido, email, cargo, password_hash, activo, visible_web)
        VALUES (:rol, :nombre, :apellido, :email, :cargo, :hash, 1, 0)
        ON DUPLICATE KEY UPDATE rol_id = VALUES(rol_id), password_hash = VALUES(password_hash), activo = 1';
$st = $pdo->prepare($sql);
$st->execute([
    ':rol'      => $rolId,
    ':nombre'   => trim($nombre),
    ':apellido' => trim($apellido),
    ':email'    => strtolower(trim($email)),
    ':cargo'    => $rol === 'ADMIN' ? 'Administrador' : 'Agente inmobiliario',
    ':hash'     => password_hash($clave, PASSWORD_DEFAULT),
]);

echo "[OK] Usuario $email ($rol) creado/actualizado." . PHP_EOL;
