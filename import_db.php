<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$secret = $_GET['secret'] ?? '';
if ($secret !== 'mahefaswift2026') {
    die('Accès refusé');
}

// Afficher les variables d'env pour debug
echo "<pre>Variables ENV:\n";
echo "MYSQLHOST: " . getenv('MYSQLHOST') . "\n";
echo "MYSQLUSER: " . getenv('MYSQLUSER') . "\n";
echo "MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? '***ok***' : 'VIDE') . "\n";
echo "MYSQLDATABASE: " . getenv('MYSQLDATABASE') . "\n";
echo "MYSQLPORT: " . getenv('MYSQLPORT') . "\n";
echo "</pre>";

try {
    $host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
    $user = getenv('MYSQLUSER') ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: '';
    $db   = getenv('MYSQLDATABASE') ?: 'railway';
    $port = (int)(getenv('MYSQLPORT') ?: 3306);

    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        die("❌ Connexion échouée : " . $conn->connect_error);
    }

    echo "✅ Connexion MySQL OK !<br>";

    $sql = file_get_contents(__DIR__ . '/school_access_db (1).sql');
    $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    $success = 0;
    $errors = [];
    foreach ($queries as $query) {
        if (empty($query)) continue;
        if ($conn->query($query)) {
            $success++;
        } else {
            $errors[] = $conn->error . " → " . substr($query, 0, 80);
        }
    }

    echo "<pre>✅ Requêtes OK : $success\n";
    if ($errors) {
        echo "⚠️ Erreurs (ignorables si 'already exists') :\n";
        foreach ($errors as $e) echo "  - $e\n";
    }
    echo "\n🎉 Import terminé !</pre>";

} catch (Exception $e) {
    echo "❌ Exception : " . $e->getMessage();
}
