<?php
function getDatabaseConnection() {
    // 1. On lit le fichier .env (développement local uniquement)
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue; // ignorer commentaires
            putenv($line);
        }
    }

    // 2. Lecture des variables d'environnement (Railway les injecte automatiquement)
    $servername = getenv('DB_SERVER') ?: getenv('MYSQLHOST') ?: 'localhost';
    $username   = getenv('DB_USER')   ?: getenv('MYSQLUSER') ?: '';
    $password   = getenv('DB_PASS')   ?: getenv('MYSQLPASSWORD') ?: '';
    $database   = getenv('DB_NAME')   ?: getenv('MYSQLDATABASE') ?: '';
    $port       = getenv('DB_PORT')   ?: getenv('MYSQLPORT') ?: 3306;

    // 3. Connexion
    $connection = new mysqli($servername, $username, $password, $database, (int)$port);

    if ($connection->connect_error) {
        die("Erreur de connexion à la base de données.");
    }

    $connection->set_charset('utf8mb4');
    return $connection;
}
?>