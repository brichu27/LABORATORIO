#!/usr/bin/env python3
"""
Extrae el texto completo de noticia1.html ... noticia4.html y genera
database/03_noticias_contenido.sql con UPDATEs sobre la tabla noticias (ids 1..4).

Formato generado (lo interpreta render_contenido() en PHP):
  "## Subtitulo"  -> <h4>
  "- item"        -> <li>
  parrafos separados por linea en blanco

Uso (desde la raiz, ANTES de mover los .html con migrar_estaticas.py --mover):
    python tools/extraer_noticias.py
Luego importar database/03_noticias_contenido.sql en phpMyAdmin o con mysql.exe
"""
import sys
from html.parser import HTMLParser
from pathlib import Path

RAIZ = Path(__file__).resolve().parent.parent
SALIDA = RAIZ / "database" / "03_noticias_contenido.sql"


class ExtractorNoticia(HTMLParser):
    """Recorre <section class="noticia"> <div class="text"> y arma bloques de texto."""

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.en_texto = False
        self.profundidad_div = 0
        self.etiqueta = None
        self.buffer = []
        self.bloques = []

    def handle_starttag(self, tag, attrs):
        clases = dict(attrs).get("class", "") or ""
        if tag == "div" and "text" in clases.split() and not self.en_texto:
            self.en_texto = True
            self.profundidad_div = 1
            return
        if self.en_texto and tag == "div":
            self.profundidad_div += 1
        if self.en_texto and tag in ("h4", "p", "li"):
            self.etiqueta = tag
            self.buffer = []

    def handle_endtag(self, tag):
        if self.en_texto and tag == "div":
            self.profundidad_div -= 1
            if self.profundidad_div == 0:
                self.en_texto = False
            return
        if self.en_texto and tag == self.etiqueta:
            texto = " ".join("".join(self.buffer).split())
            if texto:
                prefijo = {"h4": "## ", "li": "- ", "p": ""}[tag]
                self.bloques.append(prefijo + texto)
            self.etiqueta = None

    def handle_data(self, data):
        if self.en_texto and self.etiqueta:
            self.buffer.append(data)


def sql_texto(valor: str) -> str:
    return "'" + valor.replace("\\", "\\\\").replace("'", "''") + "'"


def main() -> int:
    sentencias = ["-- Generado por tools/extraer_noticias.py", "USE viviendas_ub;", "SET NAMES utf8mb4;", "START TRANSACTION;"]
    faltantes = 0
    for numero in range(1, 5):
        archivo = RAIZ / f"noticia{numero}.html"
        if not archivo.exists():
            archivo = RAIZ / "_legacy_html" / f"noticia{numero}.html"
        if not archivo.exists():
            print(f"[ERROR] No se encontro noticia{numero}.html")
            faltantes += 1
            continue
        parser = ExtractorNoticia()
        parser.feed(archivo.read_text(encoding="utf-8-sig", errors="replace"))
        if not parser.bloques:
            print(f"[ERROR] {archivo.name}: no se encontro contenido en <div class=\"text\">")
            faltantes += 1
            continue
        contenido = "\n\n".join(parser.bloques)
        sentencias.append(f"UPDATE noticias SET contenido = {sql_texto(contenido)} WHERE id = {numero};")
        print(f"[OK] {archivo.name}: {len(parser.bloques)} bloques")
    sentencias.append("COMMIT;")
    SALIDA.write_text("\n".join(sentencias) + "\n", encoding="utf-8")
    print(f"[OK] Generado {SALIDA.relative_to(RAIZ)}")
    return 1 if faltantes else 0


if __name__ == "__main__":
    sys.exit(main())
