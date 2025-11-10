<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Config/DBConection.php';

class AccountModel
{
    private $conexion;
    private $errorMessages = [
        'limit_reached' => 'Nuestra plataforma ha alcanzado su capacidad máxima. Por favor, intenta más tarde.',
        'email_exists' => 'Este email ya está asociado a una cuenta existente.',
        'insert_failed' => 'Error al procesar el registro. Por favor, intenta nuevamente.'
    ];

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function saveUser($email, $passwordHash)
    {
        // Verificar límite de usuarios
        if ($this->getUserCount() >= 500) {
            return ['error' => 'limit_reached'];
        }

        // Verificar email existente
        if ($this->emailExists($email)) {
            return ['error' => 'email_exists'];
        }

        // Insertar usuario
        return $this->insertUser($email, $passwordHash);
    }

    public function getErrorMessage($errorKey)
    {
        return $this->errorMessages[$errorKey] ?? 'Error desconocido';
    }

    private function getUserCount()
    {
        $result = $this->conexion->query("SELECT COUNT(*) AS total FROM users");
        return $result->fetch_assoc()['total'];
    }

    private function emailExists($email)
    {
        $stmt = $this->conexion->prepare("SELECT COUNT(*) AS total FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'] > 0;
    }

    private function insertUser($email, $passwordHash)
    {
        $this->conexion->begin_transaction();

        try {
            $stmt = $this->conexion->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception("Error preparing statement");
            }
            $stmt->bind_param('ss', $email, $passwordHash);
            $stmt->execute();
            $userId = $stmt->insert_id;

            $role_id = 1;
            $stmt2 = $this->conexion->prepare("INSERT INTO roles_assignments (user_id, role_id) VALUES (?, ?)");
            if (!$stmt2) {
                throw new Exception("Error in roles_assignments");
            }
            $stmt2->bind_param('ii', $userId, $role_id);
            $stmt2->execute();


            $this->conexion->commit();

            return ['id' => $stmt->insert_id];

        } catch (Exception $e) {
            $this->conexion->rollback();
            return ['error' => 'insert_failed'];
        }
    }
}