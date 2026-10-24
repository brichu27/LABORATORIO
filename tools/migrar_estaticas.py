#!/usr/bin/env python3
"""
Migra las paginas estaticas (preguntas, politica, terminos) de .html a .php
reutilizando includes/header.php e includes/footer.php (menu y footer unicos y corregidos).

- Toma el contenido entre el fin del <section class="header"> y el inicio del footer.
- Reemplaza enlaces internos *.html -> *.php y corrige el atributo mal escrito "heigth".
- Con --mover, mueve TODOS los .html viejos a _legacy_html/ (no los borra).

Uso (desde la raiz del proyecto):
    python tools/migrar_estaticas.py            # genera los .php
    python tools/migrar_estaticas.py --mover    # genera y mueve los .html a _legacy_html/
Validacion: abrir http://localhost/LABORATORIO/preguntas.php, politica.php y terminos.php
"""
import re
import shutil
import sys
from pathlib import Path

RAIZ = Path(__file__).resolve().parent.parent
ESTATICAS = {
    "preguntas.html": "Preguntas frecuentes",
    "politica.html": "Política de privacidad",
    "terminos.html": "Términos y condiciones",
}
PAGINAS_INTERNAS = ["index", "nosotros", "propiedades", "tasaciones", "blog", "contacto",
                    "preguntas", "politica", "terminos"]
LEGACY = ["index.html", "nosotros.html", "propiedades.html", "tasaciones.html", "blog.html",
          "contacto.html", "preguntas.html", "politica.html", "terminos.html",
          "noticia1.html", "noticia2.html", "noticia3.html", "noticia4.html"]


def extraer_contenido(html: str, archivo: str) -> str:
    m_header = re.search(r'<section\s+class="header".*?</section>', html, flags=re.S | re.I)
    m_footer = re.search(r'(<!--\s*footer\s*-->|<section\s+class="footer")', html, flags=re.I)
    if not m_header or not m_footer or m_footer.start() <= m_header.end():
        raise ValueError(f"{archivo}: no se encontro la estructura header/footer esperada")
    contenido = html[m_header.end():m_footer.start()].strip()
    for pagina in PAGINAS_INTERNAS:
        contenido = re.sub(rf'href="{pagina}\.html(#[^"]*)?"', lambda m: f'href="{pagina}.php{m.group(1) or ""}"', contenido)
    contenido = re.sub(r'href="noticia(\d)\.html"', r'href="noticia.php?id=\1"', contenido)
    contenido = contenido.replace("heigth=", "height=")
    return contenido


def generar_php(nombre_html: str, titulo: str) -> Path:
    origen = RAIZ / nombre_html
    if not origen.exists():
        raise FileNotFoundError(f"No existe {origen}")
    html = origen.read_text(encoding="utf-8-sig", errors="replace")
    contenido = extraer_contenido(html, nombre_html)
    destino = origen.with_suffix(".php")
    destino.write_text(
        "<?php\n"
        "declare(strict_types=1);\n"
        f"$titulo = '{titulo}';\n"
        "$paginaActiva = '';\n"
        "require __DIR__ . '/includes/header.php';\n"
        "?>\n"
        f"{contenido}\n"
        "<?php require __DIR__ . '/includes/footer.php'; ?>\n",
        encoding="utf-8",
    )
    return destino


def mover_legacy() -> None:
    carpeta = RAIZ / "_legacy_html"
    carpeta.mkdir(exist_ok=True)
    for nombre in LEGACY:
        ruta = RAIZ / nombre
        if ruta.exists():
            shutil.move(str(ruta), str(carpeta / nombre))
            print(f"[MOVIDO] {nombre} -> _legacy_html/")


def main() -> int:
    errores = 0
    for archivo, titulo in ESTATICAS.items():
        try:
            destino = generar_php(archivo, titulo)
            print(f"[OK] {archivo} -> {destino.name}")
        except Exception as ex:  # noqa: BLE001 - se informa y se continua con el resto
            errores += 1
            print(f"[ERROR] {ex}")
    if "--mover" in sys.argv:
        if errores:
            print("[AVISO] Hubo errores: no se movieron los .html. Corrija y vuelva a ejecutar.")
        else:
            mover_legacy()
    return 1 if errores else 0


if __name__ == "__main__":
    sys.exit(main())
