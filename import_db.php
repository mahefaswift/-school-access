<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$secret = $_GET['secret'] ?? '';
if ($secret !== 'mahefaswift2026') die('Accès refusé');

echo "<pre>== Variables ENV détectées ==\n";
$env = getenv();
foreach (['MYSQLHOST','MYSQLUSER','MYSQLPASSWORD','MYSQLDATABASE','MYSQLPORT'] as $k) {
    echo "$k: " . (getenv($k) ?: '(vide)') . "\n";
}
echo "\n== Test connexion ==\n";

$host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'railway';
$port = (int)(getenv('MYSQLPORT') ?: 3306);

$conn = @new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("❌ Connexion échouée : " . $conn->connect_error);
}
echo "✅ Connexion MySQL OK!\n";

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
echo "\n🎉 Import terminé !";
echo "</pre>";
