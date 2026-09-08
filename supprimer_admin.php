<?php
// On démarre la session manuellement car on n'inclut pas le header.php
session_start();

// 1. Sécurité stricte : Seul le Grand Admin peut accéder à ce script
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "grand_admin") {
    header("location: /index.php");
    exit;
}

// 2. On vérifie qu'on a bien reçu un ID dans l'URL (ex: supprimer_admin.php?id=3)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_a_supprimer = $_GET['id'];

    // 3. Sécurité ultime : On empêche l'admin de supprimer son propre compte !
    if ($id_a_supprimer != $_SESSION["admin_id"]) {
        
        include "tools/db.php";
        $db = getDatabaseConnection();

        // 4. Exécution de la suppression
        $stmt = $db->prepare("DELETE FROM administrateurs WHERE id = ?");
        $stmt->bind_param("i", $id_a_supprimer);
        $stmt->execute();
        $stmt->close();
    }
}

// 5. Redirection automatique et immédiate vers le tableau du personnel
header("location: /gestion_personnel.php");
exit;
?>