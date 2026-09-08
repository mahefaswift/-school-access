<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$secret = $_GET['secret'] ?? '';
if ($secret !== 'mahefaswift2026') die('Accès refusé');

// Connexion directe avec les credentials Railway MySQL
$conn = new mysqli(
    'mysql.railway.internal',
    'root',
    'cJZEGrQmqRRdcHMZgdLtMJxwTXkCKiIN',
    'railway',
    3306
);

if ($conn->connect_error) {
    die("❌ Connexion échouée : " . $conn->connect_error);
}
echo "✅ Connexion MySQL OK!<br><pre>";

$sql = file_get_contents(__DIR__ . '/school_access_db (1).sql');
$sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
$queries = array_filter(array_map('trim', explode(';', $sql)));

$ok = 0; $errs = [];
foreach ($queries as $q) {
    if (empty($q)) continue;
    if ($conn->query($q)) $ok++;
    else $errs[] = $conn->error . " → " . substr($q, 0, 60);
}

echo "✅ Requêtes OK: $ok\n";
if ($errs) { echo "⚠️ Erreurs:\n"; foreach($errs as $e) echo "  - $e\n"; }
echo "\n🎉 Import terminé ! La base de données est prête.";
echo "</pre>";
