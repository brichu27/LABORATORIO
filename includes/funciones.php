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

/**
 * Pesos argentinos por 1 dólar (blue, venta), para ordenar por precio sin separar USD de ARS.
 * Se consulta config cotizacion.url como máximo una vez por hora (archivo temporal); si la API falla o tarda
 * más de 2 s se usa el último valor obtenido y, si nunca hubo uno, cotizacion.respaldo. Tras un fallo espera 5 minutos para reintentar.
 */
function cotizacion_dolar(): float
{
    static $valor = null;
    if ($valor !== null) {
        return $valor;
    }
    $cfg = config()['cotizacion'] ?? [];
    $respaldo = (float) ($cfg['respaldo'] ?? 1647.37);
    $url = (string) ($cfg['url'] ?? '');
    $ttl = (int) ($cfg['cache_segundos'] ?? 3600);
    $archivo = sys_get_temp_dir() . '/viviendas_ub_cotizacion.json';

    $cache = is_file($archivo) ? json_decode((string) @file_get_contents($archivo), true) : null;
    $guardado = is_array($cache) && ($cache['valor'] ?? 0) > 0 ? (float) $cache['valor'] : null;
    $proximoIntento = is_array($cache) ? (int) ($cache['proximo_intento'] ?? 0) : 0;

    if ($url === '' || time() < $proximoIntento) {
        return $valor = $guardado ?? $respaldo;
    }

    $contexto = stream_context_create(['http' => ['timeout' => 2, 'ignore_errors' => true, 'header' => "Accept: application/json
"]]);
    $json = json_decode((string) @file_get_contents($url, false, $contexto), true);
    $venta = is_array($json) && is_numeric($json['venta'] ?? null) ? (float) $json['venta'] : 0.0;

    if ($venta > 0) {
        $valor = $venta;
        $siguiente = time() + $ttl;
    } else {
        $valor = $guardado ?? $respaldo;
        $siguiente = time() + 300;
    }
    @file_put_contents($archivo, json_encode(['valor' => $valor, 'obtenido' => $venta > 0 ? date('c') : ($cache['obtenido'] ?? null), 'proximo_intento' => $siguiente]), LOCK_EX);
    return $valor;
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

/** Celular argentino en formato internacional: 1152591813 => "+54 9 11 5259-1813". */
function tel_formato(?string $telefono): string
{
    $d = preg_replace('/\D/', '', (string) $telefono) ?? '';
    if (strlen($d) === 10) {
        return '+54 9 ' . substr($d, 0, 2) . ' ' . substr($d, 2, 4) . '-' . substr($d, 6);
    }
    return (string) $telefono;
}

/**
 * Mensaje sugerido de WhatsApp según la sección del sitio desde la que se escribe
 * (botón flotante y teléfonos del pie). Una página puede definir $mensajeWhatsapp antes del footer para uno propio.
 */
function mensaje_whatsapp(string $pagina): string
{
    return match ($pagina) {
        'index'       => 'Hola Viviendas UB, vi su sitio y quisiera hacer una consulta.',
        'propiedades' => 'Hola Viviendas UB, estoy viendo las propiedades publicadas y quisiera más información.',
        'tasaciones'  => 'Hola Viviendas UB, quisiera solicitar la tasación de mi propiedad.',
        'contacto'    => 'Hola Viviendas UB, me interesa sumarme al equipo de agentes.',
        'nosotros'    => 'Hola Viviendas UB, quisiera hablar con uno de sus agentes.',
        'blog'        => 'Hola Viviendas UB, leí su blog y quisiera hacer una consulta.',
        default       => 'Hola Viviendas UB, quisiera hacer una consulta.',
    };
}

/** Mensaje para responderle por WhatsApp a quien dejó una consulta, tasación o postulación (panel). */
function mensaje_whatsapp_respuesta(string $tipo, array $fila): string
{
    $saludo = 'Hola ' . $fila['nombre'] . ', te escribo de Viviendas UB ';
    return match ($tipo) {
        'consultas'  => $saludo . 'por tu consulta sobre la propiedad "' . $fila['propiedad'] . '".',
        'tasaciones' => $saludo . 'por tu pedido de tasación de ' . $fila['direccion_inmueble'] . '.',
        default      => $saludo . 'por tu postulación para sumarte como agente.',
    };
}

/**
 * Botón que copia un email al portapapeles (js/copiar-email.js).
 * Reemplaza a los enlaces mailto:, que no hacen nada si el navegador no tiene un programa de correo configurado.
 */
function boton_copiar_email(string $email, string $clase = '', bool $conTexto = true, string $icono = 'fas fa-envelope', string $etiqueta = ''): string
{
    $etiqueta = $etiqueta !== '' ? $etiqueta : 'Copiar el email ' . $email;
    return '<button type="button" class="copiar-email' . ($clase !== '' ? ' ' . e($clase) : '') . '"'
        . ' data-copiar="' . e($email) . '" title="Copiar ' . e($email) . '" aria-label="' . e($etiqueta) . '">'
        . '<i class="' . e($icono) . '" aria-hidden="true"></i>'
        . ($conTexto ? ' <span>' . e($email) . '</span>' : '')
        . '</button>';
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
 *  - "## Titulo" => subtitulo <h2> (el <h1> es el título de la noticia), lineas "- item" => lista, resto => parrafo
 *  - un item que termina en ":" funciona como título corto y va en negrita (clase "lista")
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
            $html .= '<h2>' . e(substr($bloque, 3)) . '</h2>';
            continue;
        }
        $lineas = preg_split('/\R/u', $bloque) ?: [];
        $esLista = count(array_filter($lineas, fn($l) => strpos(ltrim($l), '- ') === 0)) === count($lineas);
        if ($esLista) {
            $html .= '<ul>';
            foreach ($lineas as $l) {
                $item = trim(substr(ltrim($l), 2));
                $html .= '<li' . (str_ends_with($item, ':') ? ' class="lista"' : '') . '>' . e($item) . '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p>' . nl2br(e($bloque)) . '</p>';
        }
    }
    return $html;
}

/** Minutos de lectura estimados de un texto (unas 200 palabras por minuto; como mínimo 1). */
function minutos_lectura(string $texto): int
{
    $palabras = count(preg_split('/\s+/u', trim(str_replace('## ', '', $texto)), -1, PREG_SPLIT_NO_EMPTY) ?: []);
    return max(1, (int) ceil($palabras / 200));
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
 * Los teléfonos se comparan sin espacios ni guiones contra los dígitos del texto buscado ("11 5259" encuentra "1152591813").
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
