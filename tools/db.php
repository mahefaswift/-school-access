<?php
function getDatabaseConnection() {
    // 1. On lit le fichier .env
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            putenv($line);
        }
    }

    // 2. On récupère les valeurs en gardant TES noms de variables
    $servername = getenv('DB_SERVER') ?: "localhost";
    $username = getenv('DB_USER') ?: "root";
    $password = getenv('DB_PASS') ?: "root";
    $database = getenv('DB_NAME') ?: "school_access_db";

    // 3. Connexion identique à ton code d'origine

    $connection = new mysqli($servername, $username, $password, $database);

    if($connection->connect_error) {
        die("Error failed to connect to MySQL: " . $connection->connect_error);
    }

    return $connection;
}
?>