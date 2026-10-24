<?php
declare(strict_types=1);

/**
 * Validaciones del lado servidor (obligatorias: el cliente puede saltear el JS).
 * Las reglas son las mismas que js/validaciones.js.
 */
final class Validador
{
    public const RE_NOMBRE   = '/^[\p{L}][\p{L}\s\'\-]{1,79}$/u';
    public const RE_TELEFONO = '/^\+?\(?\d[\d\s()\-]{6,18}\d$/';

    /** @var array<string,string> */
    private array $errores = [];

    /* ---------- Reglas puras (reutilizables y testeables) ---------- */
    public static function nombreValido(string $v): bool
    {
        return preg_match(self::RE_NOMBRE, trim($v)) === 1;
    }

    public static function emailValido(string $v): bool
    {
        $v = trim($v);
        return strlen($v) <= 120 && filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** Rechaza negativos, letras y longitudes invalidas. Acepta: 11 2345-6789, +54 9 11 2345 6789 */
    public static function telefonoValido(string $v): bool
    {
        $v = trim($v);
        if (preg_match(self::RE_TELEFONO, $v) !== 1) {
            return false;
        }
        $digitos = strlen(preg_replace('/\D/', '', $v) ?? '');
        return $digitos >= 8 && $digitos <= 15;
    }

    public static function textoValido(string $v, int $min, int $max): bool
    {
        $largo = mb_strlen(trim($v));
        return $largo >= $min && $largo <= $max;
    }

    public static function urlValida(string $v): bool
    {
        if ($v === '') {
            return true;
        }
        return filter_var($v, FILTER_VALIDATE_URL) !== false && preg_match('#^https?://#i', $v) === 1;
    }

    /** Minimo 8 caracteres, al menos una letra y un numero. */
    public static function claveValida(string $v): bool
    {
        return strlen($v) >= 8 && strlen($v) <= 72 && preg_match('/[A-Za-z]/', $v) && preg_match('/\d/', $v);
    }

    /* ---------------- Reglas con registro de errores ---------------- */
    public function nombre(string $campo, string $v, string $etiqueta = 'Nombre'): void
    {
        if (!self::nombreValido($v)) {
            $this->errores[$campo] = "$etiqueta: solo letras, espacios, apóstrofe o guion (2 a 80 caracteres).";
        }
    }

    public function email(string $campo, string $v): void
    {
        if (!self::emailValido($v)) {
            $this->errores[$campo] = 'Ingrese un email válido (ej: nombre@dominio.com).';
        }
    }

    public function telefono(string $campo, string $v): void
    {
        if (!self::telefonoValido($v)) {
            $this->errores[$campo] = 'Teléfono inválido: entre 8 y 15 dígitos, sin signos negativos (ej: 11 2345-6789).';
        }
    }

    public function texto(string $campo, string $v, string $etiqueta, int $min, int $max): void
    {
        if (!self::textoValido($v, $min, $max)) {
            $this->errores[$campo] = "$etiqueta: debe tener entre $min y $max caracteres.";
        }
    }

    public function entero(string $campo, string $v, string $etiqueta, int $min, int $max): ?int
    {
        $n = filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if ($n === false) {
            $this->errores[$campo] = "$etiqueta: ingrese un número entero entre $min y $max.";
            return null;
        }
        return (int) $n;
    }

    public function decimal(string $campo, string $v, string $etiqueta, float $min, float $max, bool $opcional = false): ?float
    {
        if ($opcional && $v === '') {
            return null;
        }
        $n = filter_var(str_replace(',', '.', $v), FILTER_VALIDATE_FLOAT);
        if ($n === false || $n < $min || $n > $max) {
            $this->errores[$campo] = "$etiqueta: ingrese un número entre $min y $max.";
            return null;
        }
        return (float) $n;
    }

    public function enLista(string $campo, string $v, array $permitidos, string $etiqueta): void
    {
        if (!in_array($v, $permitidos, true)) {
            $this->errores[$campo] = "$etiqueta: seleccione una opción válida.";
        }
    }

    public function url(string $campo, string $v, string $etiqueta): void
    {
        if (!self::urlValida($v)) {
            $this->errores[$campo] = "$etiqueta: ingrese una URL válida que comience con https://";
        }
    }

    public function clave(string $campo, string $v): void
    {
        if (!self::claveValida($v)) {
            $this->errores[$campo] = 'La clave debe tener al menos 8 caracteres, con letras y números.';
        }
    }

    public function agregar(string $campo, string $mensaje): void
    {
        $this->errores[$campo] = $mensaje;
    }

    public function ok(): bool
    {
        return $this->errores === [];
    }

    /** @return array<string,string> */
    public function errores(): array
    {
        return $this->errores;
    }
}
