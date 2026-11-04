<?php
/**
 * Disposición inicial del diagrama de relaciones (Esquema de base de datos) de viviendas_ub.
 * Sin esto Adminer apila las 9 tablas en una sola columna. Ordena de izquierda a derecha:
 * catálogos (y la tasación, que apunta a tipos_propiedad) -> usuarios -> propiedades -> lo que depende de
 * propiedades. postulaciones no tiene relaciones y va donde no la cruza ninguna línea.
 * Formato de Adminer: tabla:ARRIBAxIZQUIERDA (en em), separadas por "_".
 * Si se mueve una tabla en el diagrama, Adminer guarda esa posición y deja de usar esta.
 */
$cookie = 'adminer_schema-viviendas_ub';
if (!isset($_COOKIE[$cookie])) {
    $_COOKIE[$cookie] = implode('_', [
        'roles:4x0', 'tipos_propiedad:11x0', 'solicitudes_tasacion:16x0', 'postulaciones:33x0',
        'usuarios:0x16',
        'propiedades:0x32',
        'consultas:0x48', 'propiedad_imagenes:18x48', 'noticias:29x48',
    ]);
}

return new class {
};
