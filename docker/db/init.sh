#!/bin/bash
# Se ejecuta una sola vez, cuando el volumen de la base esta vacio.
# Carga los scripts en orden y en UTF-8 (sin esto los acentos se guardan mal).
set -e
for f in 01_schema 02_datos_iniciales 03_noticias_contenido; do
    echo "Cargando database/$f.sql"
    mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" --default-character-set=utf8mb4 < "/sql/$f.sql"
done
