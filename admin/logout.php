<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}
csrf_verificar();
logout();
header('Location: login.php');
exit;
