<?php
// On démarre la session en TOUT PREMIER pour pouvoir lire les informations
session_start();

// SÉCURITÉ : Si quelqu'un essaie d'aller sur success.php sans s'être inscrit
if (!isset($_SESSION["badge_code"])) {
    header("location: /index.php");
    exit;
}

// On récupère les informations sauvegardées lors de l'inscription
$badge_code = $_SESSION["badge_code"];
$first_name = $_SESSION["first_name"];

include "layout/header.php";
?>

<style>
    /* Style normal pour l'écran */
    .badge-print-zone {
        border: 2px dashed #ccc;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
    }

    /* Ce qui s'applique UNIQUEMENT lors de l'impression */
    @media print {
        body * {
            visibility: hidden;
        }
        
        #zone_badge, #zone_badge * {
            visibility: visible;
        }
        
        #zone_badge {
            position: absolute;
            left: 0;
            top: 0;
            width: 8cm;
            border: 1px solid #000;
            padding: 20px;
            text-align: center !important;
        }

        #zone_badge img {
            width: 150px;
            height: 150px;
        }
    }
</style>

<div class="container py-5">
    <div class="container1">
        
        <!-- Logo et texte à gauche -->
        <div class="box text-center"> 
            <img src="images/logo_ispm.png" alt="projet2.0ispm">
            <p><span>I</span>nstitut <span>S</span>upérieur <span>P</span>olytechnique de <span>M</span>adagascar</p>
        </div>
        
        <!-- Interface de succès et badge à droite -->
        <div class="box text-center">
            
            <div class="alert alert-success mb-3 py-2">
                Félicitations <strong><?= htmlspecialchars($first_name) ?></strong> ! Ton inscription est réussie.
            </div>

            <div class="card shadow-lg border-primary mb-3 mx-auto" style="max-width: 400px;">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0">🛡️ School Access </h5>
                </div>
                <div class="card-body py-3">
                    
                    <!-- DÉBUT DE LA ZONE À IMPRIMER -->
                    <div id="zone_badge" class="badge-print-zone mb-3 mx-auto" style="max-width: 250px; padding: 15px;">
                        <h6 class="mb-2 text-uppercase fw-bold">School Access</h6>
                        <h6 class="text-primary mb-2"><?= htmlspecialchars($first_name) ?></h6>
        
                        <!-- L'image générée localement -->
                        <img src="images/qrcodes/<?= htmlspecialchars($badge_code) ?>.png" 
                            alt="Code QR" 
                            class="img-fluid mb-2" style="max-height: 110px;"> 
             
                        <p class="mb-0 fw-bold" style="font-family: monospace; font-size: 1.1rem;">
                            <?= htmlspecialchars($badge_code) ?>
                        </p>
                    </div>
                    <!-- FIN DE LA ZONE À IMPRIMER -->

                    <p class="mb-3 text-muted" style="font-size: 0.9rem;">Tu peux maintenant imprimer ce badge pour le coller sur ta moto ou le garder dans ton portefeuille.</p>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <button onclick="window.print()" class="btn btn-dark">
                    🖨️ Imprimer le Badge
                </button>
        
                <a href="/index.php" class="btn btn-primary">Retour à l'accueil</a>
            </div>
        </div>

    </div>
</div>

<?php
// On efface le badge de la mémoire (session)
unset($_SESSION["badge_code"]);
unset($_SESSION["first_name"]);

include "layout/footer.php";
?>
<style>
    footer img, .footer img {
        display: none !important;
    }
</style>