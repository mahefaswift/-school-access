<?php
// Fichier : alerte_moto.php

// Inclusion des outils nécessaires
include_once __DIR__ . "/tools/db.php";
include_once __DIR__ . "/tools/config_mail.php"; // Ajout du fichier sécurisé
$db = getDatabaseConnection();

// --- CONFIGURATION DYNAMIQUE ---
// 1. On récupère dynamiquement l'email du Grand Admin
$query_admin = "SELECT email FROM administrateurs WHERE role = 'grand_admin' LIMIT 1";
$result_admin = $db->query($query_admin);

if ($result_admin && $result_admin->num_rows > 0) {
    $row_admin = $result_admin->fetch_assoc();
    $email_admin = $row_admin['email'];
} else {
    // Adresse de secours si aucun admin n'est trouvé
    $email_admin = "secours@ispm.mg"; 
}

// 2. Configuration des heures
$heure_actuelle = date("H:i:s"); // On utilise la vraie heure du serveur
$heure_fermeture_officielle = "18:00:00"; 
$heure_fin_cours = "17:00:00"; 
$alertes = [];

// ==========================================
// CAS 1 : MOTOS TOUJOURS LÀ APRÈS LA FERMETURE
// ==========================================
if ($heure_actuelle >= $heure_fermeture_officielle) {
    $sql_non_sortis = "
        SELECT u.first_name, u.last_name, u.marque_vehicule, p.date_heure as heure_entree 
        FROM passages p 
        JOIN users u ON p.code_badge = u.code_badge 
        WHERE p.type_mouvement = 'entree' 
        AND DATE(p.date_heure) = CURDATE()
        AND p.code_badge NOT IN (
            SELECT code_badge 
            FROM passages 
            WHERE type_mouvement = 'sortie' 
            AND DATE(date_heure) = CURDATE()
        )
    ";
    
    $resultat = $db->query($sql_non_sortis);

    if ($resultat && $resultat->num_rows > 0) {
        $alertes[] = "🛑 MOTOS NON SORTIES À LA FERMETURE ($heure_fermeture_officielle) :";
        while ($moto = $resultat->fetch_assoc()) {
            $nom_complet = $moto['first_name'] . ' ' . $moto['last_name'];
            $heure_entree = date("H:i", strtotime($moto['heure_entree']));
            $vehicule = $moto['marque_vehicule'] ? $moto['marque_vehicule'] : "Véhicule inconnu";
            
            $alertes[] = "- $vehicule ($nom_complet) - Entré à $heure_entree";
        }
        $alertes[] = "\n"; 
    }
}

// ==========================================
// CAS 2 : MOTOS SORTIES EN AVANCE
// ==========================================
$sql_sortie_avance = "
    SELECT u.first_name, u.last_name, u.marque_vehicule, TIME(p.date_heure) as heure_sortie
    FROM passages p 
    JOIN users u ON p.code_badge = u.code_badge 
    WHERE p.type_mouvement = 'sortie' 
    AND DATE(p.date_heure) = CURDATE()
    AND TIME(p.date_heure) < '$heure_fin_cours'
";

$resultat2 = $db->query($sql_sortie_avance);

if ($resultat2 && $resultat2->num_rows > 0) {
    $alertes[] = "⚠️ MOTOS SORTIES EN AVANCE (Avant $heure_fin_cours) :";
    while ($moto = $resultat2->fetch_assoc()) {
        $nom_complet = $moto['first_name'] . ' ' . $moto['last_name'];
        $heure_sortie = date("H:i", strtotime($moto['heure_sortie']));
        $vehicule = $moto['marque_vehicule'] ? $moto['marque_vehicule'] : "Véhicule inconnu";
        
        $alertes[] = "- $vehicule ($nom_complet) est parti(e) à $heure_sortie.";
    }
}

// ==========================================
// 3. ENVOI DE L'E-MAIL VIA PHPMAILER
// ==========================================
if (count($alertes) > 0) {
    require_once __DIR__ . '/vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // Utilisation des variables sécurisées
        $mail->Username   = SMTP_USER; 
        $mail->Password   = SMTP_PASS; 
        
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(SMTP_USER, 'School Access');
        $mail->addAddress($email_admin); // Le destinataire est désormais dynamique !

        $mail->isHTML(false); 
        $mail->Subject = "🚨 ALERTES PARKING : " . date("d/m/Y");
        
        $message = "Bonjour,\n\nVoici le rapport des anomalies du jour sur le parking :\n\n";
        $message .= implode("\n", $alertes);
        $message .= "\n\nLe système d'accès automatisé School Access.";
        
        $mail->Body = $message;

        $mail->send();
        echo "✅ " . count($alertes) . " alerte(s) détectée(s). L'e-mail a été envoyé avec succès à $email_admin via PHPMailer.";
    } catch (Exception $e) {
        echo "❌ Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
    }
} else {
    echo "ℹ️ Tout est en ordre. Aucune anomalie détectée dans le parking.";
}
?>