<?php
// Contrôleur de démonstration / Sandbox pour les recruteurs
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . "/tools/db.php";

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '/index.php';

// Sécurité basique pour l'URL de redirection (doit être interne)
if (!str_starts_with($redirect, '/')) {
    $redirect = '/index.php';
}

switch ($action) {
    case 'login_grand_admin':
        // Connexion instantanée Grand Admin
        $_SESSION["admin_id"] = 1;
        $_SESSION["email"] = "aliciarasoanaivo1@gmail.com";
        $_SESSION["nom"] = "RASOANAIVO";
        $_SESSION["prenom"] = "Alicia";
        $_SESSION["role"] = "grand_admin";
        $_SESSION["demo_notice"] = "Connecté avec succès en tant que Grand Admin (Alicia)";
        
        $dest = ($redirect === '/login.php') ? '/dashboard.php' : $redirect;
        header("Location: " . $dest);
        exit;

    case 'login_sous_admin':
        // Connexion instantanée Sous-Admin (Gardien)
        $_SESSION["admin_id"] = 2;
        $_SESSION["email"] = "gardien@test.com";
        $_SESSION["nom"] = "RAKOTO";
        $_SESSION["prenom"] = "Tovo";
        $_SESSION["role"] = "sous_admin";
        $_SESSION["demo_notice"] = "Connecté avec succès en tant que Gardien / Sous-Admin (Tovo)";
        
        $dest = ($redirect === '/login.php' || $redirect === '/dashboard.php') ? '/index.php' : $redirect;
        header("Location: " . $dest);
        exit;

    case 'logout':
        // Déconnexion mode visiteur
        unset($_SESSION["admin_id"]);
        unset($_SESSION["email"]);
        unset($_SESSION["nom"]);
        unset($_SESSION["prenom"]);
        unset($_SESSION["role"]);
        $_SESSION["demo_notice"] = "Mode Visiteur actif (déconnecté)";
        
        // Si la page demandait un rôle, on renvoie sur l'accueil
        if (in_array($redirect, ['/dashboard.php', '/gestion_personnel.php', '/ajouter_admin.php', '/modifier_admin.php'])) {
            $redirect = '/index.php';
        }
        header("Location: " . $redirect);
        exit;

    case 'seed_today':
        // Injecte des passages aujourd'hui pour animer les graphiques du Dashboard
        $db = getDatabaseConnection();
        $today = date('Y-m-d');
        
        // Récupération de quelques badges existants
        $res = $db->query("SELECT code_badge FROM users LIMIT 6");
        $badges = [];
        while ($r = $res->fetch_assoc()) {
            $badges[] = $r['code_badge'];
        }

        if (!empty($badges)) {
            // Création de flux cohérents pour aujourd'hui
            $scenarios = [
                ['badge' => $badges[0] ?? 'SA-2026-7406', 'mouvement' => 'entree', 'heure' => '07:45:10'],
                ['badge' => $badges[1] ?? 'SA-2026-8582', 'mouvement' => 'entree', 'heure' => '08:02:44'],
                ['badge' => $badges[2] ?? 'SA-2026-6630', 'mouvement' => 'entree', 'heure' => '08:15:20'],
                ['badge' => $badges[0] ?? 'SA-2026-7406', 'mouvement' => 'sortie', 'heure' => '11:45:00'],
                ['badge' => $badges[3] ?? 'SA-2026-9510', 'mouvement' => 'entree', 'heure' => '12:05:30'],
                ['badge' => $badges[4] ?? 'SA-2026-7984', 'mouvement' => 'entree', 'heure' => '13:20:15'],
                ['badge' => $badges[1] ?? 'SA-2026-8582', 'mouvement' => 'sortie', 'heure' => '14:10:00'],
                ['badge' => $badges[5] ?? 'SA-2026-7073', 'mouvement' => 'entree', 'heure' => '14:35:50'],
            ];

            $stmt = $db->prepare("INSERT INTO passages (code_badge, type_mouvement, date_heure) VALUES (?, ?, ?)");
            foreach ($scenarios as $s) {
                $dt = $today . ' ' . $s['heure'];
                $stmt->bind_param('sss', $s['badge'], $s['mouvement'], $dt);
                $stmt->execute();
            }
            $stmt->close();
            $_SESSION["demo_notice"] = "8 passages réalistes ont été injectés avec succès pour aujourd'hui.";
        }

        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header("Location: " . $redirect);
        exit;

    case 'reset_today':
        // Nettoie uniquement les passages créés aujourd'hui
        $db = getDatabaseConnection();
        $db->query("DELETE FROM passages WHERE DATE(date_heure) = CURDATE()");
        $_SESSION["demo_notice"] = "Les passages d'aujourd'hui ont été réinitialisés avec succès.";

        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header("Location: " . $redirect);
        exit;

    default:
        header("Location: " . $redirect);
        exit;
}
