<?php
declare(strict_types=1);

require_once __DIR__ . '/funciones.php';

/* Capa de acceso a datos: todas las consultas son preparadas. */

const OPERACIONES = ['VENTA', 'ALQUILER'];
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
    if (!empty($f['destacada'])) {
        $where[] = 'p.destacada = 1';
    }
    return implode(' AND ', $where);
}

function propiedades_listar(array $filtros, int $offset, int $limite): array
{
    $params = [];
    $where  = filtros_propiedades($filtros, $params);
    $sql = "SELECT p.id, p.titulo, p.operacion, p.precio, p.moneda, p.direccion, p.barrio, p.ciudad,
                   p.dormitorios, p.banos, p.superficie_m2, " . SQL_PORTADA . " AS imagen
              FROM propiedades p
             WHERE $where
             ORDER BY p.destacada DESC, p.creado_en DESC, p.id DESC
             LIMIT :limite OFFSET :offset";
    $st = db()->prepare($sql);
    foreach ($params as $k => $v) {
        $st->bindValue($k, $v);
    }
    $st->bindValue(':limite', max(1, min($limite, 24)), PDO::PARAM_INT);
    $st->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
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

function noticias_publicadas(int $limite = 10, int $excluirId = 0): array
{
    $st = db()->prepare('SELECT id, titulo, resumen, imagen, fecha_publicacion
                           FROM noticias
                          WHERE publicada = 1 AND fecha_publicacion <= CURDATE() AND id <> :excluir
                          ORDER BY fecha_publicacion DESC, id DESC
                          LIMIT :limite');
    $st->bindValue(':excluir', $excluirId, PDO::PARAM_INT);
    $st->bindValue(':limite', $limite, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
}

function noticia_obtener(int $id, bool $soloPublicada = true): ?array
{
    $sql = 'SELECT * FROM noticias WHERE id = ?' . ($soloPublicada ? ' AND publicada = 1 AND fecha_publicacion <= CURDATE()' : '');
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
        'precio'      => precio_formato($p['precio'], $p['moneda']),
        'direccion'   => $p['direccion'] . ', ' . $p['barrio'] . ', ' . $p['ciudad'] . '.',
        'dormitorios' => (int) $p['dormitorios'],
        'banos'       => (int) $p['banos'],
        'superficie'  => (float) $p['superficie_m2'],
        'imagen'      => img_src($p['imagen'] ?? null),
        'url'         => 'propiedad.php?id=' . (int) $p['id'],
    ];
}

/** Tarjeta HTML de propiedad (misma estructura que genera js/cargar-mas.js). */
function render_tarjeta_propiedad(array $p): string
{
    $d = propiedad_publica($p);
    $banos = $d['banos'] === 1 ? '1 baño' : $d['banos'] . ' baños';
    $dorm  = $d['dormitorios'] === 1 ? '1 habitación' : $d['dormitorios'] . ' habitaciones';
    return '<div class="box">'
        . '<div class="image"><img src="' . e($d['imagen']) . '" alt="' . e($d['titulo']) . '" loading="lazy"></div>'
        . '<div class="content">'
        . '<h3>' . e($d['precio']) . '</h3>'
        . '<p><i class="fa-solid fa-location-dot"></i> ' . e($d['direccion']) . '</p>'
        . '<div class="iconos"><i class="fa-solid fa-bath"></i> ' . e($banos)
        . ' <i class="fa-solid fa-bed"></i> ' . e($dorm)
        . ' <i class="fa-solid fa-ruler-horizontal"></i> ' . e((string) round($d['superficie'])) . 'm²</div>'
        . '<a href="' . e($d['url']) . '" class="btn">Más información</a>'
        . '</div></div>';
}
