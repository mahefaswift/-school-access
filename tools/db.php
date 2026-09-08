<?php
function getDatabaseConnection() {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            putenv($line);
        }
    }

    $host = getenv('MYSQLHOST') ?: getenv('DB_SERVER') ?: 'localhost';
    $user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
    $db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';
    $port = (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

    $connection = new mysqli($host, $user, $pass, $db, $port);
    if ($connection->connect_error) {
        die("Erreur de connexion à la base de données.");
    }
    $connection->set_charset('utf8mb4');
    return $connection;
}
