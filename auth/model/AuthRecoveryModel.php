<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Config/DBConection.php';

class AuthRecoveryModel
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function savePasswordTemporary($userId, $email, $recoveryCode, $recoveryToken, $expiresAt)
    {


        $stmt = $this->conexion->prepare("
                INSERT INTO password_resets (user_id, email, reset_code, reset_token, expires_at, used) 
                VALUES (?, ?, ?, ?, ?, 0)
            ");
        $stmt->bind_param('issss', $userId, $email, $recoveryCode, $recoveryToken, $expiresAt);
        $stmt->execute();
        $this->conexion->commit();
    }

    public function verifyIdentity($recoveryToken, $verificationCode)
    {

        try {
            // Verificar código y token
            $stmt = $this->conexion->prepare("
                SELECT * FROM password_resets 
                WHERE reset_token = ? AND reset_code = ? AND used = 0 AND expires_at > NOW()
            ");
            $stmt->bind_param('ss', $recoveryToken, $verificationCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $recoveryRequest = $result->fetch_assoc();

            if (!$recoveryRequest) {
                throw new Exception('Código de verificación inválido o expirado', 400);
            }

            // Marcar como usado
            $stmt = $this->conexion->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->bind_param('i', $recoveryRequest['id']);
            $stmt->execute();

            // Generar token de recuperación final
            $finalRecoveryToken = bin2hex(random_bytes(32));
            $finalExpiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $this->conexion->prepare("
                INSERT INTO password_resets (user_id, email, reset_token, expires_at, used) 
                VALUES (?, ?, ?, ?, 0)
            ");
            $stmt->bind_param('isss', $recoveryRequest['user_id'], $recoveryRequest['email'], $finalRecoveryToken, $finalExpiresAt);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Identidad verificada correctamente',
                'final_token' => $finalRecoveryToken,
                'user_email' => $recoveryRequest['email'],
                'code' => 200
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }

    }

    public function recoverAccount($finalToken, $newPassword)
    {


        try {
            // Verificar token final
            $stmt = $this->conexion->prepare("
                SELECT * FROM password_resets 
                WHERE reset_token = ? AND used = 0 AND expires_at > NOW()
            ");
            $stmt->bind_param('s', $finalToken);
            $stmt->execute();
            $result = $stmt->get_result();
            $recoveryRequest = $result->fetch_assoc();

            if (!$recoveryRequest) {
                throw new Exception('Token de recuperación inválido o expirado', 400);
            }

            // Cambiar la contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->conexion->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hashedPassword, $recoveryRequest['user_id']);
            $stmt->execute();

            // Limpiar todos los tokens del usuario
            $stmt = $this->conexion->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->bind_param('i', $recoveryRequest['user_id']);
            $stmt->execute();

            // Enviar confirmación
            $this->sendRecoverySuccessEmail($recoveryRequest['email']);

            return [
                'success' => true,
                'message' => 'Cuenta recuperada exitosamente. Tu contraseña ha sido actualizada.',
                'code' => 200
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }

    }

    private function sendRecoverySuccessEmail($email){
        require_once __DIR__ . '/../controllers/AuthRecoveryController.php';
         $controller = new AuthRecoveryController();
          return $controller->sendRecoverySuccessEmail($email);
    }

}