<?php
declare(strict_types=1);

require_once __DIR__ . '/funciones.php';

const MAX_INTENTOS_LOGIN = 5;
const BLOQUEO_SEGUNDOS   = 300;

function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function es_admin(): bool
{
    return (usuario_actual()['rol'] ?? '') === 'ADMIN';
}

function login_bloqueado(): bool
{
    $intentos = $_SESSION['login_intentos'] ?? 0;
    $ultimo   = $_SESSION['login_ultimo'] ?? 0;
    if ($intentos >= MAX_INTENTOS_LOGIN && (time() - $ultimo) < BLOQUEO_SEGUNDOS) {
        return true;
    }
    if ((time() - $ultimo) >= BLOQUEO_SEGUNDOS) {
        $_SESSION['login_intentos'] = 0;
    }
    return false;
}

function login(string $email, string $clave): bool
{
    $st = db()->prepare(
        'SELECT u.id, u.nombre, u.apellido, u.email, u.password_hash, u.activo, r.nombre AS rol
           FROM usuarios u
           JOIN roles r ON r.id = u.rol_id
          WHERE u.email = ?'
    );
    $st->execute([strtolower(trim($email))]);
    $u = $st->fetch();

    if (!$u || (int) $u['activo'] !== 1 || !password_verify($clave, $u['password_hash'])) {
        $_SESSION['login_intentos'] = ($_SESSION['login_intentos'] ?? 0) + 1;
        $_SESSION['login_ultimo']   = time();
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['login_intentos'] = 0;
    $_SESSION['usuario'] = [
        'id'     => (int) $u['id'],
        'nombre' => $u['nombre'] . ' ' . $u['apellido'],
        'email'  => $u['email'],
        'rol'    => $u['rol'],
    ];
    db()->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?')->execute([$u['id']]);
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Exige sesion iniciada (paginas del panel). */
function requiere_login(): array
{
    $u = usuario_actual();
    if ($u === null) {
        flash('error', 'Debe iniciar sesión para acceder al panel.');
        redirigir('login.php');
    }
    return $u;
}

/** Exige uno de los roles indicados. Ej: requiere_rol('ADMIN') */
function requiere_rol(string ...$roles): array
{
    $u = requiere_login();
    if (!in_array($u['rol'], $roles, true)) {
        http_response_code(403);
        exit('Acceso denegado: su rol no tiene permiso para esta sección.');
    }
    return $u;
}

/** Un VENDEDOR solo puede operar sobre sus propias propiedades. */
function puede_editar_propiedad(array $usuario, array $propiedad): bool
{
    return $usuario['rol'] === 'ADMIN' || (int) $propiedad['agente_id'] === (int) $usuario['id'];
}
