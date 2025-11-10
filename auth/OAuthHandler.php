<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/model/AuthModel.php';
require_once __DIR__ . '/model/AccountModel.php';

use Firebase\JWT\JWT;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

function handleOAuthLogin($email, $firstName, $lastName, $company)
{
    session_start();

    $conexion = DBConection::connect();
    $model = new AuthModel($conexion);
    $user = $model->requestUser($email);

    $secret_key = $_ENV['JWT_SECRET_KEY'];

    if (!$user) {
        try {

            // Usuario nuevo registrarlo automaticamente
            $model = new AccountModel($conexion);
            $result = $model->saveUser($email, password_hash(uniqid(), PASSWORD_DEFAULT));


            if (isset($result['id'])) {
                echo json_encode([
                    'success' => true,
                    'message' => '¡Registro completado exitosamente!',
                    'code' => 200
                ]);
            } else {
                throw new Exception($model->getErrorMessage($result['error']), 400);
            }


        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ]);
        }


    }

    $payload = [
        "id" => $user['id'],
        "email" => $email,
        "iat" => time(),
    ];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');

    $_SESSION['loggedin'] = true;
    $_SESSION['email'] = $email;
    $_SESSION['id'] = $user['id'];

    header("Location: /" . $_ENV['HOST_URL'] . "?authorize=" . $jwt);
    exit;

}