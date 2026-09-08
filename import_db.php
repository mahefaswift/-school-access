<?php
// Script temporaire d'import DB - À SUPPRIMER APRÈS UTILISATION
$secret = $_GET['secret'] ?? '';
if ($secret !== 'mahefaswift2026') {
    die('Accès refusé');
}

include_once "tools/db.php";
$db = getDatabaseConnection();

$sql = file_get_contents(__DIR__ . '/school_access_db (1).sql');

// Nettoyer les commentaires et les lignes vides
$sql = preg_replace('/--.*\n/', "\n", $sql);
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

$queries = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$errors = [];

foreach ($queries as $query) {
    if (empty($query)) continue;
    if ($db->query($query) === true) {
        $success++;
    } else {
        $errors[] = $db->error . " -- Query: " . substr($query, 0, 80);
    }
}

echo "<pre>";
echo "✅ Requêtes exécutées : $success\n";
if (!empty($errors)) {
    echo "⚠️ Erreurs (ignorables si 'already exists') :\n";
    foreach ($errors as $e) echo "  - $e\n";
}
echo "\n🎉 Import terminé ! Supprimez ce fichier maintenant.";
echo "</pre>";
