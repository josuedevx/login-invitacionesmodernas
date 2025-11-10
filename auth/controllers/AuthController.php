<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;


require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../model/AuthModel.php';
require_once __DIR__ . '/../middleware/RequestURI.php';

session_start();
header('Content-Type: application/json');

class AuthController
{
    private $model;

    public function __construct()
    {
        $conexion = DBConection::connect();
        $this->model = new AuthModel($conexion);
    }

    public function requestLogin($email, $password): void
    {

        // Validación de entrada
        if (!isset($email, $password)) {
            $this->sendErrorResponse('Missing email or password', 400);
        }

        $email = $_POST['email'];
        $passwordInput = $password;

        try {

            $user = $this->model->requestUser($email);

            if (!$user || !password_verify($passwordInput, $user['password'])) {
                $this->sendErrorResponse('Correo electrónico o contraseña no válidos', 400);
            }

            $secret_key = $_ENV['JWT_SECRET_KEY'];
            $payload = [
                "id" => $user['id'],
                "email" => $email,
                "iat" => time(),
            ];

            $jwt = JWT::encode($payload, $secret_key, 'HS256');


            $_SESSION['loggedin'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $user['id'];

            $this->sendSuccessResponse([
                'id' => $user['id'],
                'token' => $jwt,
                'message' => 'Login successful'
            ]);



        } catch (Exception $e) {
            $message = $e->getCode() === 500 ? 'Database connection failed' : 'An error occurred';
            $this->sendErrorResponse($message, $e->getCode() ?: 500, $e->getMessage());
        }

    }

    public function requestCode($email)
    {
        // error_log("📌 Step 1: requestCode: " . $email);
        return $this->model->requestCode($email);
    }

    public function verifyCode($email, $code, $token)
    {
        return $this->model->verifyCode($email, $code, $token);
    }

    public function updatePassword($email, $code, $token, $newPassword)
    {
        return $this->model->updatePassword($email, $code, $token, $newPassword);

    }

    // Enviar código por email
    public function sendResetCode($email, $code)
    {

        $mail = new PHPMailer(true);
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        try {
            // Configuración del servidor
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'];

            // Destinatarios
            $mail->setFrom($_ENV['MAIL_FROM'], 'Invitaciones Modernas');
            $mail->addAddress($email);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Código de restablecimiento - Invitaciones Modernas';
            $mail->Body = $this->getEmailTemplate($code);
            $mail->AltBody = "Tu código de restablecimiento es: $code";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            error_log("Error enviando email: " . $e->getMessage());
            return false;
        }
    }

    private function getEmailTemplate($code)
    {
        return "
       <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Restablecimiento de Contraseña - Invitaciones Modernas</title>
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
            </style>
        </head>
        <body>
            <div class='container'>
                <!-- Header con logo -->
                <div class='header'>
                    <img src='https://invitacionesmodernas.b-cdn.net/assets/img/logo.png' alt='Invitaciones Modernas' class='logo'>
                    <h1>Restablecimiento de Contraseña</h1>
                </div>
                
                <!-- Contenido principal -->
                <div class='content'>
                    <p class='instruction'>Hola,</p>
                    <p class='instruction'>Has solicitado restablecer tu contraseña en <strong>Invitaciones Modernas</strong>. Para completar el proceso, utiliza el siguiente código de verificación:</p>
                    
                    <div class='code-container'>
                        <div class='code'>{$code}</div>
                        <p style='color: #666; margin: 0;'>Código de verificación</p>
                    </div>
                    
                    <div class='expiry-notice'>
                        ⏰ <strong>Este código expirará en 30 minutos</strong>
                    </div>
                    
                    <div class='warning'>
                        ⚠️ <strong>Importante:</strong> Si no solicitaste este restablecimiento, por favor ignora este mensaje y considera cambiar tu contraseña por seguridad.
                    </div>
                    
                    <p class='instruction'>Si tienes problemas con el código o necesitas ayuda, no dudes en contactarnos.</p>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='https://invitacionesmodernas.com' target='_blank' class='button'>Visitar Nuestro Sitio</a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class='footer'>
                    <!-- Redes Sociales -->
                    <div class='social-links'>
                        <a href='https://www.facebook.com/miquinceaneravip/' class='social-link' target='_blank'>Facebook</a>
                        <a href='https://www.instagram.com/miquinceaneravip/' class='social-link' target='_blank'>Instagram</a>
                        <a href='https://www.youtube.com/@quinceaneravip' class='social-link' target='_blank'>YouTube</a>
                    </div>
                    
                    <!-- Información de Contacto -->
                    <div class='contact-info'>
                        <strong style='color: #F8B1B1;'>Contacto</strong>
                        <span class='phone-number'>📞 623-208-6099</span>
                        <span class='phone-number'>📞 1 877-863-6362</span>
                        <span class='phone-number'>📞 480-790-9091</span>
                    </div>
                    
                    <!-- Copyright -->
                    <div class='copyright'>
                        © 2025 Invitaciones Modernas. Todos los derechos reservados.<br>
                        Creando momentos inolvidables para tus celebraciones especiales.
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    public function sendMessageUpdate($email)
    {


        $mail = new PHPMailer(true);
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        try {
            // Configuración del servidor
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'];

            // Destinatarios
            $mail->setFrom($_ENV['MAIL_FROM'], 'Invitaciones Modernas');
            $mail->addAddress($email);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Cambio de contraseña - Invitaciones Modernas';
            $mail->Body = $this->getMessageTemplate();

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            error_log("Error enviando email: " . $e->getMessage());
            return false;
        }

    }


    public function getMessageTemplate()
    {

        $recoveryUrl = requestURI() . "recover-account.php";

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Cambio de Contraseña - Invitaciones Modernas</title>
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
                <!-- Header con logo -->
                <div class='header'>
                    <img src='https://invitacionesmodernas.b-cdn.net/assets/img/logo.png' alt='Invitaciones Modernas' class='logo'>
                    <h1>Cambio de Contraseña</h1>
                </div>
                
                <!-- Contenido principal -->
                <div class='content'>
                    <p class='instruction'>Hola,</p>
                    <p class='instruction'>Se ha actualizado tu contraseña en <strong>Invitaciones Modernas.</strong></p>
                    
                    <div class='security-alert'>
                        <h3>🔒 ¿No reconoces esta acción?</h3>
                        <p>Si no fuiste tú quien cambió la contraseña, puedes recuperar el control de tu cuenta inmediatamente.</p>
                    </div>

                    <div class='security-actions' style='text-align: center;'>
                        <a href='{$recoveryUrl}' class='button' target='_blank'>
                            🚨 Recuperar Mi Cuenta
                        </a>
                       
                    </div>

                     <div class='warning'>
                        ⚠️ <strong>Actúa rápidamente:</strong> Si no iniciaste este cambio, tu cuenta podría estar en riesgo.
                        Nuestro sistema de autorecuperación te permite tomar el control inmediatamente.
                    </div>
                    
                    <p class='instruction'>El botón \"Recuperar Mi Cuenta\" te guiará por un proceso seguro para verificar tu identidad y recuperar el acceso.</p>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='https://invitacionesmodernas.com' target='_blank' class='button'>Visitar Nuestro Sitio</a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class='footer'>
                    <!-- Redes Sociales -->
                    <div class='social-links'>
                        <a href='https://www.facebook.com/miquinceaneravip/' class='social-link' target='_blank'>Facebook</a>
                        <a href='https://www.instagram.com/miquinceaneravip/' class='social-link' target='_blank'>Instagram</a>
                        <a href='https://www.youtube.com/@quinceaneravip' class='social-link' target='_blank'>YouTube</a>
                    </div>
                    
                    <!-- Información de Contacto -->
                    <div class='contact-info'>
                        <strong style='color: #F8B1B1;'>Contacto</strong>
                        <span class='phone-number'>📞 623-208-6099</span>
                        <span class='phone-number'>📞 1 877-863-6362</span>
                        <span class='phone-number'>📞 480-790-9091</span>
                    </div>
                    
                    <!-- Copyright -->
                    <div class='copyright'>
                        © 2025 Invitaciones Modernas. Todos los derechos reservados.<br>
                        Creando momentos inolvidables para tus celebraciones especiales.
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }


    function sendErrorResponse(string $message, int $code = 400, string $errorDetail = null): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'error' => $errorDetail
        ]);
        exit;
    }

    function sendSuccessResponse(array $data = []): void
    {
        http_response_code(200);
        echo json_encode(array_merge([
            'success' => true,
            'code' => 200
        ], $data));
        exit;
    }
}

// Manejar las solicitudes
if ($_POST['action'] ?? false) {
    $controller = new AuthController();

    switch ($_POST['action']) {

        case 'request_login':
            $response = $controller->requestLogin($_POST['email'], $_POST['password']);
            break;

        case 'request_code':
            $response = $controller->requestCode($_POST['email']);
            break;

        case 'verify_code':
            $response = $controller->verifyCode(
                $_POST['email'],
                $_POST['code'],
                $_POST['token']
            );
            break;

        case 'update_password':
            $response = $controller->updatePassword(
                $_POST['email'],
                $_POST['code'],
                $_POST['token'],
                $_POST['new_password']
            );
            break;

        default:
            $response = ['success' => false, 'message' => 'Acción no válida'];
    }

    echo json_encode($response);
    exit;
}