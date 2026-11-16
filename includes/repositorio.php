<?php
declare(strict_types=1);

require_once __DIR__ . '/funciones.php';

/* Capa de acceso a datos: todas las consultas son preparadas. */

const OPERACIONES = ['VENTA', 'ALQUILER'];
/**
 * Órdenes del listado público. Por precio se compara todo en pesos: los precios en USD se multiplican
 * por la cotización del dólar (:cotizacion, ver cotizacion_dolar()). En pantalla cada precio sigue en su moneda.
 */
const SQL_PRECIO_EN_PESOS = "CASE WHEN p.moneda = 'USD' THEN p.precio * :cotizacion ELSE p.precio END";
const ORDENES = [
    'recientes'   => ['Más recientes', 'p.destacada DESC, p.creado_en DESC, p.id DESC'],
    'precio_asc'  => ['Menor precio', SQL_PRECIO_EN_PESOS . ' ASC, p.id DESC'],
    'precio_desc' => ['Mayor precio', SQL_PRECIO_EN_PESOS . ' DESC, p.id DESC'],
];
const ESTADOS_PROPIEDAD = ['DISPONIBLE', 'RESERVADA', 'VENDIDA', 'ALQUILADA', 'INACTIVA'];

const SQL_PORTADA = '(SELECT i.ruta FROM propiedad_imagenes i WHERE i.propiedad_id = p.id
                      ORDER BY i.es_portada DESC, i.orden ASC, i.id ASC LIMIT 1)';

/** Arma el WHERE de filtros publicos (solo propiedades DISPONIBLES). */
function filtros_propiedades(array $f, array &$params): string
{
    $where = ["p.estado = 'DISPONIBLE'"];
    if (!empty($f['operacion']) && in_array($f['operacion'], OPERACIONES, true)) {
        $where[] = 'p.operacion = :operacion';
        $params[':operacion'] = $f['operacion'];
    }
    if (!empty($f['barrio'])) {
        $where[] = 'p.barrio = :barrio';
        $params[':barrio'] = $f['barrio'];
    }
    if (!empty($f['tipo']) && ctype_digit((string) $f['tipo'])) {
        $where[] = 'p.tipo_propiedad_id = :tipo';
        $params[':tipo'] = (int) $f['tipo'];
    }
    if (!empty($f['destacada'])) {
        $where[] = 'p.destacada = 1';
    }
    return implode(' AND ', $where);
}

function propiedades_listar(array $filtros, int $offset, int $limite): array
{
    $params = [];
    $where  = filtros_propiedades($filtros, $params);
    $orden = ORDENES[$filtros['orden'] ?? ''][1] ?? ORDENES['recientes'][1];   // lista blanca: nunca se concatena lo recibido
    $sql = "SELECT p.id, p.titulo, p.operacion, p.precio, p.moneda, p.direccion, p.barrio, p.ciudad,
                   p.dormitorios, p.banos, p.superficie_m2, t.nombre AS tipo, " . SQL_PORTADA . " AS imagen
              FROM propiedades p
              JOIN tipos_propiedad t ON t.id = p.tipo_propiedad_id
             WHERE $where
             ORDER BY $orden
             LIMIT :limite OFFSET :offset";
    $st = db()->prepare($sql);
    foreach ($params as $k => $v) {
        $st->bindValue($k, $v);
    }
    $st->bindValue(':limite', max(1, min($limite, 24)), PDO::PARAM_INT);
    $st->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
    if (str_contains($orden, ':cotizacion')) {
        $st->bindValue(':cotizacion', (string) cotizacion_dolar());   // texto: DECIMAL exacto en MariaDB
    }
    $st->execute();
    return $st->fetchAll();
}

function propiedades_contar(array $filtros): int
{
    $params = [];
    $where  = filtros_propiedades($filtros, $params);
    $st = db()->prepare("SELECT COUNT(*) FROM propiedades p WHERE $where");
    $st->execute($params);
    return (int) $st->fetchColumn();
}

function propiedad_obtener(int $id): ?array
{
    $st = db()->prepare(
        'SELECT p.*, t.nombre AS tipo, u.nombre AS agente_nombre, u.apellido AS agente_apellido,
                u.email AS agente_email, u.telefono AS agente_telefono, u.foto AS agente_foto
           FROM propiedades p
           JOIN tipos_propiedad t ON t.id = p.tipo_propiedad_id
           JOIN usuarios u        ON u.id = p.agente_id
          WHERE p.id = ?'
    );
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function propiedad_imagenes(int $id): array
{
    $st = db()->prepare('SELECT id, ruta, es_portada, orden FROM propiedad_imagenes
                          WHERE propiedad_id = ? ORDER BY es_portada DESC, orden ASC, id ASC');
    $st->execute([$id]);
    return $st->fetchAll();
}

function barrios_disponibles(): array
{
    return db()->query("SELECT DISTINCT barrio FROM propiedades WHERE estado = 'DISPONIBLE' ORDER BY barrio")
               ->fetchAll(PDO::FETCH_COLUMN);
}

function tipos_propiedad(): array
{
    return db()->query('SELECT id, nombre FROM tipos_propiedad ORDER BY nombre')->fetchAll();
}

function agentes_visibles(): array
{
    return db()->query('SELECT id, nombre, apellido, email, telefono, cargo, foto
                          FROM usuarios WHERE activo = 1 AND visible_web = 1
                         ORDER BY id')->fetchAll();
}

function agentes_activos(): array
{
    return db()->query('SELECT id, nombre, apellido FROM usuarios WHERE activo = 1 ORDER BY apellido, nombre')->fetchAll();
}

const SQL_NOTICIA_PUBLICADA = 'publicada = 1 AND fecha_publicacion <= CURDATE()';

/** Noticias publicadas, de la más reciente a la más vieja. $offset permite paginar (Blog: "Cargar más"). */
function noticias_publicadas(int $limite = 10, int $excluirId = 0, int $offset = 0): array
{
    $st = db()->prepare('SELECT id, titulo, resumen, imagen, fecha_publicacion
                           FROM noticias
                          WHERE ' . SQL_NOTICIA_PUBLICADA . ' AND id <> :excluir
                          ORDER BY fecha_publicacion DESC, id DESC
                          LIMIT :limite OFFSET :offset');
    $st->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
    $st->bindValue(':limite', max(1, $limite), PDO::PARAM_INT);
    $st->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
}

function noticias_contar(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM noticias WHERE ' . SQL_NOTICIA_PUBLICADA)->fetchColumn();
}

/** Datos de una noticia para la API JSON (los mismos que muestra la tarjeta del blog). */
function noticia_publica(array $n): array
{
    return [
        'id'      => (int) $n['id'],
        'titulo'  => $n['titulo'],
        'resumen' => $n['resumen'],
        'fecha'   => fecha_formato($n['fecha_publicacion']),
        'imagen'  => img_src($n['imagen'] ?? null),
        'url'     => 'noticia.php?id=' . (int) $n['id'],
    ];
}

function noticia_obtener(int $id, bool $soloPublicada = true): ?array
{
    // Con el nombre del autor para el artículo (NULL si no tiene o si su usuario se borró)
    $sql = 'SELECT n.*, u.nombre AS autor_nombre, u.apellido AS autor_apellido
              FROM noticias n LEFT JOIN usuarios u ON u.id = n.autor_id
             WHERE n.id = ?' . ($soloPublicada ? ' AND n.publicada = 1 AND n.fecha_publicacion <= CURDATE()' : '');
    $st = db()->prepare($sql);
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/** Datos publicos de una propiedad para la API / tarjetas. */
function propiedad_publica(array $p): array
{
    return [
        'id'          => (int) $p['id'],
        'titulo'      => $p['titulo'],
        'precio'      => precio_formato($p['precio'], $p['moneda'], $p['operacion']),
        'operacion'   => $p['operacion'] === 'ALQUILER' ? 'Alquiler' : 'Venta',
        'tipo'        => $p['tipo'] ?? '',
        'barrio'      => $p['barrio'],
        'calle'       => $p['direccion'],
        'direccion'   => $p['direccion'] . ', ' . $p['barrio'] . ', ' . $p['ciudad'] . '.',
        'dormitorios' => (int) $p['dormitorios'],
        'banos'       => (int) $p['banos'],
        'superficie'  => (float) $p['superficie_m2'],
        'imagen'      => img_src($p['imagen'] ?? null),
        'url'         => 'propiedad.php?id=' . (int) $p['id'],
    ];
}

/**
 * Tarjeta HTML de propiedad (misma estructura que genera js/cargar-mas.js).
 * Toda la tarjeta es un enlace a la ficha.
 */
function render_tarjeta_propiedad(array $p): string
{
    $d = propiedad_publica($p);
    $dorm  = $d['dormitorios'] === 1 ? '1 dorm.' : $d['dormitorios'] . ' dorm.';
    $banos = $d['banos'] === 1 ? '1 baño' : $d['banos'] . ' baños';
    return '<article class="card-propiedad"><a class="card-propiedad__enlace" href="' . e($d['url']) . '">'
        . '<div class="card-propiedad__foto">'
        . '<img src="' . e($d['imagen']) . '" alt="' . e($d['titulo']) . '" loading="lazy" width="640" height="400">'
        . '<span class="card-propiedad__etiqueta card-propiedad__etiqueta--' . strtolower($d['operacion']) . '">' . e($d['operacion']) . '</span>'
        . '</div>'
        . '<div class="card-propiedad__cuerpo">'
        . '<p class="card-propiedad__tipo">' . e(trim($d['tipo'] . ' · ' . $d['barrio'], ' ·')) . '</p>'
        . '<p class="card-propiedad__precio">' . e($d['precio']) . '</p>'
        . '<h3 class="card-propiedad__titulo">' . e($d['titulo']) . '</h3>'
        . '<p class="card-propiedad__direccion"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ' . e($d['calle']) . '</p>'
        . '<ul class="card-propiedad__datos">'
        . '<li><i class="fa-solid fa-bed" aria-hidden="true"></i> ' . e($dorm) . '</li>'
        . '<li><i class="fa-solid fa-bath" aria-hidden="true"></i> ' . e($banos) . '</li>'
        . '<li><i class="fa-solid fa-ruler-combined" aria-hidden="true"></i> ' . e((string) round($d['superficie'])) . ' m²</li>'
        . '</ul></div></a></article>';
}

/**
 * Tarjeta HTML de noticia (misma estructura que genera js/cargar-mas.js). Toda la tarjeta es un enlace al artículo.
 * $slide = true la prepara para los carruseles de Swiper (inicio, Blog y "Otras noticias").
 */
function render_tarjeta_noticia(array $n, bool $slide = false): string
{
    $d = noticia_publica($n);
    return '<li class="card-item' . ($slide ? ' swiper-slide' : '') . '"><a href="' . e($d['url']) . '" class="card-link">'
        . '<img src="' . e($d['imagen']) . '" alt="" class="card-image" width="1200" height="1200" loading="lazy">'
        . '<p class="badge">' . e($d['fecha']) . '</p>'
        . '<h3 class="card-title">' . e($d['titulo']) . '</h3>'
        . '<p class="card-text">' . e($d['resumen']) . '</p>'
        . '</a></li>';
}
