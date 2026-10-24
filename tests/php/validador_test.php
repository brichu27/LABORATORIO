<?php
/**
 * Pruebas del validador del SERVIDOR sin dependencias (no requiere PHPUnit ni base de datos).
 * Ejecutar:  php tests/php/validador_test.php
 */
declare(strict_types=1);
require __DIR__ . '/../../includes/validador.php';

$ok = 0;
$fallas = 0;
function verificar(string $descripcion, bool $condicion): void
{
    global $ok, $fallas;
    if ($condicion) {
        $ok++;
        echo "[OK]    $descripcion" . PHP_EOL;
    } else {
        $fallas++;
        echo "[FALLA] $descripcion" . PHP_EOL;
    }
}

verificar('CP-05 telefono valido 11 2345-6789', Validador::telefonoValido('11 2345-6789'));
verificar('CP-05 telefono valido +54 9 11 2345-6789', Validador::telefonoValido('+54 9 11 2345-6789'));
verificar('CP-06 telefono negativo rechazado', !Validador::telefonoValido('-1123456789'));
verificar('CP-06 telefono con letras rechazado', !Validador::telefonoValido('abc12345678'));
verificar('CP-06 telefono corto rechazado', !Validador::telefonoValido('1234567'));
verificar('CP-07 email valido', Validador::emailValido('juan@mail.com'));
verificar('CP-07 email invalido', !Validador::emailValido('juan@'));
verificar('CP-08 nombre con acentos valido', Validador::nombreValido('José Pérez'));
verificar('CP-08 nombre con numeros invalido', !Validador::nombreValido('Juan123'));
verificar('CP-08 nombre con HTML invalido', !Validador::nombreValido('<script>'));
verificar('URL javascript: rechazada', !Validador::urlValida('javascript:alert(1)'));
verificar('URL vacia permitida (opcional)', Validador::urlValida(''));
verificar('Clave debil rechazada', !Validador::claveValida('abcdefgh'));
verificar('Clave fuerte aceptada', Validador::claveValida('Clave2026'));

$v = new Validador();
$v->telefono('telefono', '-5');
$v->texto('mensaje', 'corto', 'Mensaje', 10, 1000);
$anios = $v->entero('anios', '70', 'Años', 0, 60);
verificar('Validador acumula 3 errores', count($v->errores()) === 3 && $anios === null);

echo PHP_EOL . "Resultado: $ok OK, $fallas fallas" . PHP_EOL;
exit($fallas === 0 ? 0 : 1);
