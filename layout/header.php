<?php
// On initialise la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// V2.0 : Vérification des nouveaux rôles
$is_grand_admin = false;
$is_sous_admin = false;
$is_personnel = false; // Vrai si c'est un admin OU un gardien

if (isset($_SESSION["role"])) {
    if ($_SESSION["role"] === "grand_admin") {
        $is_grand_admin = true;
        $is_personnel = true;
    } elseif ($_SESSION["role"] === "sous_admin") {
        $is_sous_admin = true;
        $is_personnel = true;
    }
}
?>

<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Access</title>
    <link rel="icon" href="images/fed61408-756e-4d02-8830-0ce6147c8073.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="style.css">
  </head>
  <body class="d-flex flex-column min-vh-100" style="overflow-x: hidden;">

    <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom shadow-sm">
         <div class="container">
            <a class="navbar-brand" href="/index.php">
                <img src="images/fed61408-756e-4d02-8830-0ce6147c8073.jpeg" width="30" height="30" class="d-inline-block align-top" alt="">   School Access
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                 <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="/index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" style="color: rgb(3, 3, 82);" href="/register.php">Obtenir un Badge</a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <?php if ($is_personnel) { ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark fw-bold" href="#" role="button" data-bs-toggle="dropdown">
                                 Menu Portail <?= $is_grand_admin ? "(Admin)" : "(Gardien)" ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/index.php">Scanner un Badge</a></li>
                                
                                <!-- Zone réservée au Grand Admin -->
                                <?php if ($is_grand_admin) { ?>
                                    <li><a class="dropdown-item" href="/dashboard.php">Tableau de bord</a></li>
                                    <li><a class="dropdown-item" href="/gestion_personnel.php">Gestion du Personnel</a></li>
                                <?php } ?>
                                
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/logout.php">Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item">
                            <a href="/login.php" class="btn btn-sm btn-outline-secondary">Accès Personnel</a>
                        </li>
                    <?php } ?>
                </ul>

            </div>
        </div>
    </nav>