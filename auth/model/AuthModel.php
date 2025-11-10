<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Config/DBConection.php';

class AuthModel
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function requestUser($email)
    {
        $stmt = $this->conexion->prepare('SELECT * FROM users WHERE email = ? AND status = 1');
        if (!$stmt)
            return false;

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0)
            return null;

        $id = $email = $password = $token = $status = null;
        $stmt->bind_result($id, $email, $password, $token, $status);
        $stmt->fetch();

        return [
            'id' => $id,
            'email' => $email,
            'password' => $password,
            'token' => $token,
            'status' => $status
        ];
    }

    public function requestCode($email)
    {
        try {
            // Verificar si el usuario existe
            $stmt = $this->conexion->prepare("SELECT id, email FROM users WHERE email = ? AND status = 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'No existe una cuenta con este correo electrónico.',
                    'code' => 404
                ];
            }

            // Generar código de 6 dígitos
            $resetCode = sprintf("%06d", mt_rand(1, 999999));
            $resetToken = bin2hex(random_bytes(32));

            // date_default_timezone_set('America/Phoenix');
            // setlocale(LC_TIME, 'en_US.UTF-8');

            date_default_timezone_set('America/Mexico_City');
            setlocale(LC_TIME, "spanish");

            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            // Eliminar códigos anteriores
            $stmt = $this->conexion->prepare("DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()");
            $stmt->bind_param('s', $email);
            $stmt->execute();

            // Insertar nuevo código
            $stmt = $this->conexion->prepare("
                INSERT INTO password_resets (user_id, email, reset_code, reset_token, expires_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('issss', $user['id'], $email, $resetCode, $resetToken, $expiresAt);
            $stmt->execute();

            // Llamar al controlador para enviar el email
            $emailSent = $this->sendResetCode($email, $resetCode);

            if ($emailSent) {
                return [
                    'success' => true,
                    'message' => 'Código enviado correctamente.',
                    'token' => $resetToken,
                    'code' => 200
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al enviar el código. Intente nuevamente.',
                    'code' => 500
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del servidor.',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    // Paso 2: Verificar código
    public function verifyCode($email, $code, $token)
    {
        try {
            $stmt = $this->conexion->prepare("
                SELECT * FROM password_resets 
                WHERE email = ? AND reset_code = ? AND reset_token = ? 
                AND used = 0 AND expires_at > NOW()
            ");
            $stmt->bind_param('sss', $email, $code, $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $resetRequest = $result->fetch_assoc();

            if (!$resetRequest) {
                return [
                    'success' => false,
                    'message' => 'Código inválido o expirado.',
                    'code' => 400
                ];
            }

            return [
                'success' => true,
                'message' => 'Código verificado correctamente.',
                'code' => 200
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del servidor.',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    // Paso 3: Actualizar contraseña
    public function updatePassword($email, $code, $token, $newPassword)
    {
        try {
            // Verificar código primero
            $stmt = $this->conexion->prepare("
                SELECT * FROM password_resets 
                WHERE email = ? AND reset_code = ? AND reset_token = ? 
                AND used = 0 AND expires_at > NOW()
            ");
            $stmt->bind_param('sss', $email, $code, $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $resetRequest = $result->fetch_assoc();

            if (!$resetRequest) {
                return [
                    'success' => false,
                    'message' => 'Solicitud de restablecimiento inválida.',
                    'code' => 400
                ];
            }

            // Actualizar contraseña del usuario
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->conexion->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param('ss', $hashedPassword, $email);
            $stmt->execute();

            // Marcar código como usado
            $stmt = $this->conexion->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->bind_param('i', $resetRequest['id']);
            $stmt->execute();

            $this->sendMessageUpdate($email);

            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente.',
                'code' => 200
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la contraseña.',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    private function sendMessageUpdate($email)
    {
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        return $controller->sendMessageUpdate($email);

    }

     // Método para enviar email (llama al controlador)
    private function sendResetCode($email, $code)
    {
        // Incluir el controlador para usar su método de envío
        require_once __DIR__ . '/../controllers/AuthController.php';
        $controller = new AuthController();
        return $controller->sendResetCode($email, $code);
    }
}