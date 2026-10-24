<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

if (usuario_actual() !== null) {
    redirigir('index.php');
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $email = post('email');
    $clave = $_POST['clave'] ?? '';
    if (login_bloqueado()) {
        $error = 'Demasiados intentos fallidos. Espere 5 minutos e intente nuevamente.';
    } elseif (!Validador::emailValido($email) || !is_string($clave) || $clave === '') {
        $error = 'Ingrese un email válido y su clave.';
    } elseif (login($email, $clave)) {
        redirigir('index.php');
    } else {
        $error = 'Email o clave incorrectos, o usuario inactivo.';   // mensaje generico: no revela cual fallo
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Ingreso al panel | Viviendas UB</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="login">
<form method="post" class="login-box" data-validar-form novalidate>
    <img src="../imagenes/Vivienda.png" alt="Viviendas UB">
    <h1>Acceso del personal</h1>
    <?= flash_render() ?>
    <?php if ($error): ?><div class="alerta alerta-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_campo() ?>
    <label for="email">Email</label>
    <input id="email" type="email" name="email" required autocomplete="username" data-validar="email" value="<?= e($email) ?>">
    <span class="error-msg"></span>
    <label for="clave">Clave</label>
    <input id="clave" type="password" name="clave" required autocomplete="current-password" data-validar="requerido">
    <span class="error-msg"></span>
    <button type="submit">Ingresar</button>
    <a href="../index.php">Volver al sitio</a>
</form>
<script src="../js/validaciones.js"></script>
</body>
</html>
