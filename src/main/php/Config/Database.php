<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Config;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }
        
        $host = getenv('DB_HOST');
        $database   = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');
        
        $dsn = "mysql:host=$host;dbname=$database";
        
        if ($database === '' || $username === '') {
            throw new RuntimeException('Configuração do banco de dados incompleta.');
        }

        self::$connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }
}