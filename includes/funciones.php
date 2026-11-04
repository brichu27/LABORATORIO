<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/validador.php';

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

/** Escapa texto para HTML (previene XSS). */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ----------------------------- CSRF ----------------------------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verificar(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Solicitud inválida (token CSRF). Recargue la página e intente nuevamente.');
    }
}

/* ------------------------- Mensajes flash ------------------------ */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function flash_render(): string
{
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $html .= '<div class="alerta alerta-' . e($f['tipo']) . '" role="alert">' . e($f['mensaje']) . '</div>';
    }
    unset($_SESSION['flash']);
    // id="aviso": destino del redirect de los formularios, para que el mensaje quede a la vista
    return $html === '' ? '' : '<div id="aviso">' . $html . '</div>';
}

/* ------------------------- Entrada (request) --------------------- */
function post(string $clave): string
{
    $v = $_POST[$clave] ?? '';
    return is_string($v) ? trim($v) : '';
}

function get_str(string $clave): string
{
    $v = $_GET[$clave] ?? '';
    return is_string($v) ? trim($v) : '';
}

function get_int(string $clave, int $defecto = 0): int
{
    $v = filter_var($_GET[$clave] ?? null, FILTER_VALIDATE_INT);
    return $v === false || $v === null ? $defecto : (int) $v;
}

function post_int(string $clave, int $defecto = 0): int
{
    $v = filter_var($_POST[$clave] ?? null, FILTER_VALIDATE_INT);
    return $v === false || $v === null ? $defecto : (int) $v;
}

/** Honeypot anti-spam: si el campo oculto viene completo, es un bot. */
function es_spam(): bool
{
    return post('sitio_web') !== '';
}

/* ------------------------- Formato / helpers --------------------- */
/** "1 baño" / "2 baños": número con el sustantivo en singular o plural. */
function cantidad(int $n, string $singular, string $plural): string
{
    return $n . ' ' . ($n === 1 ? $singular : $plural);
}

/** Arma un enlace de WhatsApp valido a partir de un telefono argentino (10 digitos con codigo de area). */
function wa_link(string $telefono, string $texto = 'Hola, quisiera hacer una consulta.'): string
{
    $digitos = preg_replace('/\D/', '', $telefono) ?? '';
    if (strlen($digitos) === 10) {
        $digitos = '549' . $digitos;
    }
    return 'https://wa.me/' . $digitos . '?text=' . rawurlencode($texto);
}

function tel_formato(?string $telefono): string
{
    $d = preg_replace('/\D/', '', (string) $telefono) ?? '';
    if (strlen($d) === 10) {
        return '+54 ' . substr($d, 0, 2) . ' ' . substr($d, 2, 4) . '-' . substr($d, 6);
    }
    return (string) $telefono;
}

function precio_formato($precio, string $moneda, string $operacion = ''): string
{
    // Los alquileres se publican como canon mensual
    return $moneda . ' ' . number_format((float) $precio, 0, ',', '.') . ($operacion === 'ALQUILER' ? ' / mes' : '');
}

function fecha_formato(string $fecha): string
{
    $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $t = strtotime($fecha);
    return $t ? date('j', $t) . ' de ' . $meses[(int) date('n', $t)] . ' de ' . date('Y', $t) : $fecha;
}

function img_src(?string $ruta, string $prefijo = ''): string
{
    return $prefijo . ($ruta !== null && $ruta !== '' ? $ruta : 'imagenes/propiedades.jpg');
}

/**
 * Renderiza el contenido de una noticia guardado como texto plano:
 *  - bloques separados por linea en blanco
 *  - "## Titulo" => subtitulo, lineas "- item" => lista, resto => parrafo
 * Todo se escapa: no se guarda HTML en la base (evita XSS).
 */
function render_contenido(string $texto): string
{
    $html = '';
    $bloques = preg_split('/\R\s*\R/u', trim($texto)) ?: [];
    foreach ($bloques as $bloque) {
        $bloque = trim($bloque);
        if ($bloque === '') {
            continue;
        }
        if (strpos($bloque, '## ') === 0) {
            $html .= '<h4>' . e(substr($bloque, 3)) . '</h4>';
            continue;
        }
        $lineas = preg_split('/\R/u', $bloque) ?: [];
        $esLista = count(array_filter($lineas, fn($l) => strpos(ltrim($l), '- ') === 0)) === count($lineas);
        if ($esLista) {
            $html .= '<ul>';
            foreach ($lineas as $l) {
                $html .= '<li class="lista">' . e(substr(ltrim($l), 2)) . '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p>' . nl2br(e($bloque)) . '</p>';
        }
    }
    return $html;
}

/** Valor anterior de un campo (para re-mostrar el formulario con errores). */
function viejo(array $datos, string $campo): string
{
    return e($datos[$campo] ?? '');
}

/** Span de error accesible para un campo. */
function error_campo(array $errores, string $campo): string
{
    return '<span class="error-msg" id="err-' . e($campo) . '">' . e($errores[$campo] ?? '') . '</span>';
}

/* --------------------- Panel: búsqueda y filtros ----------------- */
/**
 * Condición de búsqueda libre: (col1 LIKE :b0 OR col2 LIKE :b1 ...), con sus parámetros agregados a $params.
 * Los teléfonos se comparan sin espacios ni guiones contra los dígitos del texto buscado ("11 5515" encuentra "+54 11 55151981").
 */
function sql_busqueda(string $q, array $columnas, array $columnasTelefono, array &$params): string
{
    $condiciones = [];
    $patron = '%' . addcslashes($q, '%_\\') . '%';
    foreach (array_values($columnas) as $i => $col) {
        $condiciones[] = "$col LIKE :b$i";
        $params[":b$i"] = $patron;
    }
    $digitos = preg_replace('/\D+/', '', $q);
    if ($digitos !== '') {
        foreach (array_values($columnasTelefono) as $i => $col) {
            $condiciones[] = "REPLACE(REPLACE($col, ' ', ''), '-', '') LIKE :bt$i";
            $params[":bt$i"] = '%' . $digitos . '%';
        }
    }
    return '(' . implode(' OR ', $condiciones) . ')';
}

/** Estado de la base legible: EN_PROCESO => "En proceso". */
function estado_texto(string $estado): string
{
    if ($estado === 'EN_REVISION') {
        return 'En revisión';      // el único estado que lleva tilde
    }
    $texto = mb_strtolower(str_replace('_', ' ', $estado));
    return mb_strtoupper(mb_substr($texto, 0, 1)) . mb_substr($texto, 1);
}

/**
 * Barra de filtros del panel: buscador y pestañas por estado con su cantidad.
 * $cantidades: ['NUEVA' => 3, ...] (vacío = sin pestañas). $fijos: parámetros GET que se conservan (p. ej. tipo).
 */
function admin_filtros(array $cantidades, string $estadoActual, string $q, string $placeholder, string $textoTodos = 'Todas', array $fijos = []): string
{
    $url = function (array $extra) use ($fijos): string {
        $query = http_build_query(array_filter($fijos + $extra, fn ($v) => $v !== ''));
        return $query === '' ? '?' : '?' . $query;
    };
    $html = '<form method="get" class="filtros-admin" role="search">';
    foreach (array_filter($fijos + ['estado' => $estadoActual], fn ($v) => $v !== '') as $clave => $valor) {
        $html .= '<input type="hidden" name="' . e($clave) . '" value="' . e($valor) . '">';
    }
    $html .= '<div class="buscador-admin"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>'
           . '<input type="search" name="q" value="' . e($q) . '" placeholder="' . e($placeholder) . '" aria-label="Buscar">'
           . ($q !== '' ? '<a href="' . e($url(['estado' => $estadoActual])) . '" class="buscador-admin__limpiar" title="Limpiar búsqueda" aria-label="Limpiar búsqueda"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>' : '')
           . '<button type="submit" class="buscador-admin__boton"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Buscar</button>'
           . '</div>';
    if ($cantidades) {
        $html .= '<nav class="estados-admin" aria-label="Filtrar por estado">';
        $pestanas = ['' => [$textoTodos, array_sum($cantidades)]];
        foreach ($cantidades as $estado => $n) {
            $pestanas[$estado] = [estado_texto($estado), $n];
        }
        foreach ($pestanas as $estado => [$texto, $n]) {
            $activo = $estado === $estadoActual;
            $html .= '<a href="' . e($url(['q' => $q, 'estado' => $estado])) . '" class="' . ($activo ? 'activo' : '') . '"'
                   . ($activo ? ' aria-current="true"' : '') . '>' . e($texto) . ' <span>' . (int) $n . '</span></a>';
        }
        $html .= '</nav>';
    }
    return $html . '</form>';
}
