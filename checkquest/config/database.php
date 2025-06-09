<?php
use PDO;

if (!function_exists('getPDO')) {          // prevents re-definition
    function getPDO(): PDO
    {
        static $pdo = null;               // re-use one connection
        if ($pdo === null) {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=checkquest;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }
        return $pdo;
    }
}
