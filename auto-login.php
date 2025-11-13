<?php
// auto-login.php
require_once __DIR__ . "/vendor/autoload.php";
use Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();


session_start();

if (isset($_GET['token'])) {
    try {
        $key  = $_ENV['JWT_SECRET_KEY'];
        $decoded = JWT::decode($_GET['token'], new Key($key, 'HS256'));

        // Establecer sesión
        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $decoded->email;
        $_SESSION['id'] = $decoded->id;

        // Redirigir al dashboard
        header("Location: /" . $_ENV['HOST_URL'] . "?authorize=" . $_GET['token']);
        exit;

    } catch (Exception $e) {
        // Token inválido, redirigir al login normal
        header('Location: /?error=invalid_token');
        exit;
    }
} else {
    header('Location: /');
    exit;
}