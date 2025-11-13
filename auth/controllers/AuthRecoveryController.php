<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../model/AuthModel.php';
require_once __DIR__ . '/../model/AuthRecoveryModel.php';
require_once __DIR__ . '/../middleware/RequestURI.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    $response = ['success' => false, 'message' => 'method not allowed', 'code' => 500];

    echo json_encode($response);
    exit;

}


class AuthRecoveryController
{
    private $authModel;
    private $recoveryModel;

    public function __construct()
    {
        $conexion = DBConection::connect();
        $this->authModel = new AuthModel($conexion);
        $this->recoveryModel = new AuthRecoveryModel($conexion);
    }

    // Iniciar proceso de recuperación de cuenta
    public function initiateAccountRecovery($email)
    {
        try {
            // Verificar que el usuario existe
            $user = $this->authModel->requestUser($email);
            if (!$user) {
                throw new Exception('No se encontró una cuenta con este email', 404);
            }

            // Generar código de verificación de identidad

            $recoveryCode = sprintf("%06d", mt_rand(1, 999999));
            $recoveryToken = bin2hex(random_bytes(32));

            // date_default_timezone_set('America/Phoenix');
            // setlocale(LC_TIME, 'en_US.UTF-8');

            date_default_timezone_set('America/Mexico_City');
            setlocale(LC_TIME, "spanish");

            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            // Guardar en la base de datos (usamos password_resets temporalmente)
            $this->recoveryModel->savePasswordTemporary($user['id'], $email, $recoveryCode, $recoveryToken, $expiresAt);

            // Enviar código por email
            $emailSent = $this->sendRecoveryCode($email, $recoveryCode);

            if ($emailSent) {
                return [
                    'success' => true,
                    'message' => 'Código de verificación enviado a tu email',
                    'recovery_token' => $recoveryToken,
                    'code' => 200
                ];
            } else {
                throw new Exception('Error al enviar el código de verificación', 500);
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

    // Verificar identidad del usuario
    public function verifyIdentity($recoveryToken, $verificationCode)
    {

        return $this->recoveryModel->verifyIdentity($recoveryToken, $verificationCode);

    }

    // Restablecer cuenta y contraseña
    public function recoverAccount($finalToken, $newPassword)
    {

        return $this->recoveryModel->recoverAccount($finalToken, $newPassword);

    }

    // Enviar código de recuperación
    private function sendRecoveryCode($email, $code)
    {
        $mail = new PHPMailer(true);
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'];

            $mail->setFrom($_ENV['MAIL_FROM'], 'Invitaciones Modernas - Seguridad');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Verificación de Identidad - Recuperación de Cuenta';
            $mail->Body = $this->getRecoveryCodeTemplate($code);

            return $mail->send();

        } catch (Exception $e) {
            error_log("Error enviando código de recuperación: " . $e->getMessage());
            return false;
        }
    }

    private function getRecoveryCodeTemplate($code)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>

             body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    margin: 0;
                    padding: 0;
                    background-color: #f8f9fa;
                    color: #333;
                }
                
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                }
                
                .header {
                    background: linear-gradient(135deg, #E37C7D 0%, #F8B1B1 100%);
                    padding: 30px 20px;
                    text-align: center;
                }
                
                .logo {
                    max-width: 200px;
                    height: auto;
                    margin-bottom: 15px;
                }
                
                .header h1 {
                    color: white;
                    margin: 0;
                    font-size: 28px;
                    font-weight: 700;
                }
                
                .content {
                    padding: 40px 30px;
                }
                
                .code-container {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    border-radius: 12px;
                    padding: 25px;
                    margin: 30px 0;
                    text-align: center;
                    border: 2px dashed #E37C7D;
                }
                
                .code { 
                    font-size: 42px; 
                    font-weight: bold; 
                    text-align: center; 
                    color: #E37C7D;
                    letter-spacing: 8px;
                    margin: 10px 0;
                    font-family: 'Courier New', monospace;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
                }
                
                .instruction {
                    font-size: 16px;
                    line-height: 1.6;
                    color: #666;
                    margin-bottom: 25px;
                }
                
                .expiry-notice {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                }
                
                .warning {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                    color: #721c24;
                }
                
                .footer {
                    background: #2c3e50;
                    color: white;
                    padding: 30px 20px;
                    text-align: center;
                }
                
                .social-links {
                    margin: 20px 0;
                }
                
                .social-link {
                    display: inline-block;
                    margin: 0 10px;
                    color: #F8B1B1;
                    text-decoration: none;
                    font-weight: 600;
                    transition: color 0.3s ease;
                }
                
                .social-link:hover {
                    color: #E37C7D;
                }
                
                .contact-info {
                    margin: 20px 0;
                    line-height: 1.8;
                }
                
                .phone-number {
                    display: block;
                    margin: 5px 0;
                    color: #ecf0f1;
                }
                
                .copyright {
                    margin-top: 20px;
                    font-size: 12px;
                    color: #bdc3c7;
                }
                
                .button {
                    display: inline-block;
                    background: linear-gradient(135deg, #E37C7D 0%, #F8B1B1 100%);
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: 600;
                    margin: 10px 0;
                    transition: transform 0.3s ease;
                }
                
                .button:hover {
                    transform: translateY(-2px);
                }
                
                @media only screen and (max-width: 600px) {
                    .content {
                        padding: 20px 15px;
                    }
                    
                    .code {
                        font-size: 32px;
                        letter-spacing: 6px;
                    }
                    
                    .header h1 {
                        font-size: 24px;
                    }
                }
            
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='https://invitacionesmodernas.b-cdn.net/assets/img/logo.png' class='logo'>
                    <h1>Verificación de Identidad</h1>
                </div>
                
                <div class='content'>
                    <p class='instruction'>Estás recuperando el acceso a tu cuenta. Usa este código para verificar tu identidad:</p>
                    
                    <div class='code-container'>
                        <div class='code'>{$code}</div>
                    </div>
                    
                    <div class='warning'>
                        ⚠️ <strong>Este código expira en 30 minutos</strong>
                    </div>
                    
                    <p class='instruction'>Si no iniciaste este proceso, ignora este mensaje.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    public function sendRecoverySuccessEmail($email)
    {
        $mail = new PHPMailer(true);
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'];

            $mail->setFrom($_ENV['MAIL_FROM'], 'Invitaciones Modernas - Seguridad');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Cuenta Recuperada Exitosamente';
            $mail->Body = $this->getRecoverySuccessTemplate();

            return $mail->send();

        } catch (Exception $e) {
            error_log("Error enviando confirmación: " . $e->getMessage());
            return false;
        }
    }

    private function getRecoverySuccessTemplate()
    {

        $recoveryUrl = requestURI();

        return "
        <!DOCTYPE html>
        <html>
        <head>
                        <style>
                /* Reset y estilos base */
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    margin: 0;
                    padding: 0;
                    background-color: #f8f9fa;
                    color: #333;
                }
                
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                }
                
                .header {
                    background: linear-gradient(135deg, #E37C7D 0%, #F8B1B1 100%);
                    padding: 30px 20px;
                    text-align: center;
                }
                
                .logo {
                    max-width: 200px;
                    height: auto;
                    margin-bottom: 15px;
                }
                
                .header h1 {
                    color: white;
                    margin: 0;
                    font-size: 28px;
                    font-weight: 700;
                }
                
                .content {
                    padding: 40px 30px;
                }
                
                .code-container {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    border-radius: 12px;
                    padding: 25px;
                    margin: 30px 0;
                    text-align: center;
                    border: 2px dashed #E37C7D;
                }
                
                .code { 
                    font-size: 42px; 
                    font-weight: bold; 
                    text-align: center; 
                    color: #E37C7D;
                    letter-spacing: 8px;
                    margin: 10px 0;
                    font-family: 'Courier New', monospace;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
                }
                
                .instruction {
                    font-size: 16px;
                    line-height: 1.6;
                    color: #666;
                    margin-bottom: 25px;
                }
                
                .expiry-notice {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                }
                
                .warning {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                    color: #721c24;
                }
                
                .footer {
                    background: #2c3e50;
                    color: white;
                    padding: 30px 20px;
                    text-align: center;
                }
                
                .social-links {
                    margin: 20px 0;
                }
                
                .social-link {
                    display: inline-block;
                    margin: 0 10px;
                    color: #F8B1B1;
                    text-decoration: none;
                    font-weight: 600;
                    transition: color 0.3s ease;
                }
                
                .social-link:hover {
                    color: #E37C7D;
                }
                
                .contact-info {
                    margin: 20px 0;
                    line-height: 1.8;
                }
                
                .phone-number {
                    display: block;
                    margin: 5px 0;
                    color: #ecf0f1;
                }
                
                .copyright {
                    margin-top: 20px;
                    font-size: 12px;
                    color: #bdc3c7;
                }
                
                .button {
                    display: inline-block;
                    background: linear-gradient(135deg, #E37C7D 0%, #F8B1B1 100%);
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: 600;
                    margin: 10px 0;
                    transition: transform 0.3s ease;
                }
                
                .button:hover {
                    transform: translateY(-2px);
                }
                
                @media only screen and (max-width: 600px) {
                    .content {
                        padding: 20px 15px;
                    }
                    
                    .code {
                        font-size: 32px;
                        letter-spacing: 6px;
                    }
                    
                    .header h1 {
                        font-size: 24px;
                    }
                }

                .security-actions {
                    display: flex;
                    gap: 15px;
                    justify-content: center;
                    flex-wrap: wrap;
                    margin: 25px 0;
                }
            
                .btn-recovery {
                    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                    color: white;
                    padding: 12px 25px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    text-align: center;
                    border: none;
                    cursor: pointer;
                }
                
                .btn-recovery:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
                }
                
                .btn-secondary {
                    background: #6c757d;
                    color: white;
                    padding: 12px 25px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }
                
                .btn-secondary:hover {
                    background: #545b62;
                    transform: translateY(-2px);
                }
                
                .security-alert {
                    background: #fff3cd;
                    border: 2px solid #ffc107;
                    border-radius: 10px;
                    padding: 20px;
                    margin: 20px 0;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='https://invitacionesmodernas.b-cdn.net/assets/img/logo.png' class='logo'>
                    <h1>✅ Cuenta Recuperada</h1>
                </div>
                
                <div class='content'>
                    <p class='instruction'>¡Excelente! Has recuperado el acceso a tu cuenta exitosamente.</p>
                    
                    <div class='security-alert' style='background: #d4edda; border-color: #c3e6cb; color: #155724;'>
                        <h3>🔒 Seguridad Restaurada</h3>
                        <p>Tu cuenta ahora está segura y protegida.</p>
                    </div>
                    
                    <p class='instruction'>Te recomendamos:</p>
                    <ul style='text-align: left; margin: 20px 0;'>
                        <li>Revisar tu actividad reciente</li>
                        <li>Verificar tu información personal</li>
                        <li>Usar una contraseña única y segura</li>
                    </ul>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='{$recoveryUrl}' class='button'>Iniciar Sesión</a>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}

// Manejar solicitudes de recuperación
if ($_POST['action'] ?? false) {
    $controller = new AuthRecoveryController();

    switch ($_POST['action']) {
        case 'initiate_account_recovery':
            $response = $controller->initiateAccountRecovery($_POST['email'] ?? '');
            break;

        case 'verify_recovery_identity':
            $response = $controller->verifyIdentity($_POST['token'] ?? '', $_POST['code'] ?? '');
            break;

        case 'recover_account':
            $response = $controller->recoverAccount($_POST['final_token'] ?? '', $_POST['new_password'] ?? '');
            break;

        default:
            $response = ['success' => false, 'message' => 'Acción no válida'];
    }

    echo json_encode($response);
    exit;
}