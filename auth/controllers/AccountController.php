<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../model/AccountModel.php';
use Firebase\JWT\JWT;

session_start();
header('Content-Type: application/json');

class AccountController
{

    private $model;

    public function __construct()
    {
        $conexion = DBConection::connect();
        $this->model = new AccountModel($conexion);
    }

    public function registerUser($emailInput, $passwordInput): array
    {

        try {
            // Validar método
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido', 405);
            }

            $email = filter_var($emailInput ?? '', FILTER_VALIDATE_EMAIL);
            $password = $passwordInput ?? '';

            error_log("📌 Step 1: requestCode: " . $email);

            if (!$email || empty($password)) {
                throw new Exception('Email y contraseña son requeridos', 400);
            }

            if (strlen($password) < 6) {
                throw new Exception('La contraseña debe tener al menos 6 caracteres', 400);
            }

            // Registro

            $result = $this->model->saveUser($email, password_hash($password, PASSWORD_DEFAULT));

            $secret_key = $_ENV['JWT_SECRET_KEY'];
            $payload = [
                "id" => $result['id'],
                "email" => $email,
                "iat" => time(),
            ];

            $jwt = JWT::encode($payload, $secret_key, 'HS256');


            $_SESSION['loggedin'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $result['id'];

            if (isset($result['id'])) {
                error_log("📌 Step 4: User registered successfully - ID: " . $result['id']);
                return [
                    'success' => true,
                    'token' => $jwt,
                    'message' => '¡Registro completado exitosamente!',
                    'code' => 200
                ];
            } else {
                $errorMessage = $this->model->getErrorMessage($result['error'] ?? 'unknown_error');
                error_log("📌 Step 4: Registration failed - " . $errorMessage);
                throw new Exception($errorMessage, 400);
            }

        } catch (Exception $e) {
            error_log("❌ Error in registerUser: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
}


if ($_POST['action'] ?? false) {

    $controller = new AccountController();
    $response = [];
    switch ($_POST['action']) {

        case 'register_user';
            $response = $controller->registerUser($_POST['email'] ?? '', $_POST['password'] ?? '');
            break;


        default:
            $response = ['success' => false, 'message' => 'Acción no válida'];
    }


    echo json_encode($response);
    exit;

}