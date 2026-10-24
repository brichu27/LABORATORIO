<?php
declare(strict_types=1);

const UPLOAD_MAX_BYTES = 2 * 1024 * 1024; // 2 MB
const UPLOAD_TIPOS = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

/**
 * Valida y guarda una imagen subida. Devuelve la ruta relativa a la raiz del sitio
 * (ej: uploads/propiedades/ab12....jpg). Lanza RuntimeException con mensaje para el usuario.
 */
function subir_imagen(array $archivo, string $subcarpeta): string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir la imagen (código ' . (int) ($archivo['error'] ?? -1) . ').');
    }
    if ($archivo['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('La imagen supera el máximo de 2 MB.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($archivo['tmp_name']) ?: '';
    if (!isset(UPLOAD_TIPOS[$mime]) || @getimagesize($archivo['tmp_name']) === false) {
        throw new RuntimeException('Formato no permitido. Use JPG, PNG o WEBP.');
    }
    if (!preg_match('/^[a-z]+$/', $subcarpeta)) {
        throw new RuntimeException('Carpeta de destino inválida.');
    }
    $dirRelativo = 'uploads/' . $subcarpeta;
    $dirAbsoluto = __DIR__ . '/../' . $dirRelativo;
    if (!is_dir($dirAbsoluto) && !mkdir($dirAbsoluto, 0775, true) && !is_dir($dirAbsoluto)) {
        throw new RuntimeException('No se pudo crear la carpeta de imágenes.');
    }
    $nombre = bin2hex(random_bytes(16)) . '.' . UPLOAD_TIPOS[$mime];
    if (!move_uploaded_file($archivo['tmp_name'], $dirAbsoluto . '/' . $nombre)) {
        throw new RuntimeException('No se pudo guardar la imagen en el servidor.');
    }
    return $dirRelativo . '/' . $nombre;
}

/** Convierte $_FILES['campo'] de un input multiple en una lista de archivos. */
function normalizar_archivos(?array $files): array
{
    if (!$files || !is_array($files['name'] ?? null)) {
        return [];
    }
    $lista = [];
    foreach ($files['name'] as $i => $nombre) {
        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $lista[] = [
            'name'     => $nombre,
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];
    }
    return $lista;
}

/** Borra un archivo subido (solo dentro de uploads/, nunca imagenes/ originales). */
function borrar_archivo_subido(?string $ruta): void
{
    if ($ruta && strpos($ruta, 'uploads/') === 0 && strpos($ruta, '..') === false) {
        $abs = __DIR__ . '/../' . $ruta;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}
