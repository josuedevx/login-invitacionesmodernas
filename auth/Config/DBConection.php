<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

class DBConection
{
    public static function connect()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $DATABASE_HOST = $_ENV['DATABASE_DB_HOST'];
        $DATABASE_USER = $_ENV['DATABASE_DB_USER'];
        $DATABASE_PASS = $_ENV['DATABASE_DB_PASSWORD'];
        $DATABASE_NAME = $_ENV['DATABASE_DB_NAME'];

        $conexion = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

         if (!$conexion) {
            throw new Exception('Fallo en la conexión de MySQL: ' . mysqli_connect_error());
        }

        return $conexion;
    }
}
