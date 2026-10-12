<?php
if (!ob_get_level()) {
    ob_start();
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. INITIALISATION DES VARIABLES
$first_name = "";
$last_name = "";
$classe = "";
$marque_vehicule = "";
$messageError = "";

// 2. DÉTECTION DU CLIC : Si le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // Récupération des données du formulaire
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $classe = $_POST['classe'];
    $marque_vehicule = $_POST['marque_vehicule'];

    // Vérification : les champs obligatoires sont-ils remplis ?
    if (empty($first_name) || empty($last_name) || empty($classe)|| empty($marque_vehicule)) {
        $messageError = "Veuillez remplir tous les champs obligatoires (*)";
    } else {

        // Connexion à la base de données
        include "tools/db.php";
        $dbConnection = getDatabaseConnection();

        // GÉNÉRATION DU CODE BADGE 
        $annee = date('Y');
        $random_num = rand(1000, 9999);
        $badge_code = "SA-" . $annee . "-" . $random_num; 
        $created_at = date('Y-m-d H:i:s'); 

        // --- GÉNÉRATION DU QR CODE DU BADGE ---
        $chemin_dossier = "images/qrcodes/";
        if (!is_dir($chemin_dossier)) {
            mkdir($chemin_dossier, 0777, true);
        }
        $chemin_fichier = $chemin_dossier . $badge_code . ".png";
        
        if (function_exists('imagecreate')) {
            include_once "tools/phpqrcode/qrlib.php";
            QRcode::png($badge_code, $chemin_fichier, QR_ECLEVEL_L, 5);
        } else {
            // Secours si l'extension GD est absente : récupération directe de l'image QR
            $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($badge_code);
            $qrContent = @file_get_contents($qrApiUrl);
            if ($qrContent !== false && strlen($qrContent) > 100) {
                file_put_contents($chemin_fichier, $qrContent);
            } else {
                $existing = glob($chemin_dossier . "*.png");
                if (!empty($existing)) {
                    @copy($existing[0], $chemin_fichier);
                }
            }
        }

        // INSERTION DANS LA BASE DE DONNÉES
        $statement = $dbConnection->prepare(
            "INSERT INTO users (first_name, last_name, classe, marque_vehicule, code_badge, created_at) " .
            "VALUES (?, ?, ?, ?, ?, ?)"
        );

        $statement->bind_param('ssssss', $first_name, $last_name, $classe, $marque_vehicule, $badge_code, $created_at);

        if ($statement->execute()) {
            $statement->close();

            // SUCCÈS 
            $_SESSION["badge_code"] = $badge_code;
            $_SESSION["first_name"] = $first_name;

            if (!headers_sent()) {
                header("Location: /success.php");
            } else {
                echo "<script>window.location.href='/success.php';</script>";
            }
            exit;
        } else {
            $messageError = "Erreur lors de l'enregistrement : " . $statement->error;
            $statement->close();
        }
    }
}

include "layout/header.php";
?>

<div class="container py-5">
    <div class="container1">
        
        <!-- Logo et texte à gauche -->
        <div class="box text-center"> 
            <img src="images/logo_ispm.png" alt="projet2.0ispm">
            <p><span>I</span>nstitut <span>S</span>upérieur <span>P</span>olytechnique de <span>M</span>adagascar</p>
        </div>
        
        <!-- Formulaire à droite -->
        <div class="box">
            <div class="border shadow p-4 bg-white" style="border-radius: 8px;">
                <h2 class="text-center mb-4">Inscription School Access</h2>
                <hr />

                <?php if (!empty($messageError)): ?>
                    <div class="alert alert-danger"><?= $messageError ?></div>
                <?php endif; ?>

                <form method="post"> 
                     <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Nom*</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="last_name" required value="<?= htmlspecialchars($last_name) ?>">
                        </div>
                    </div> 
                        
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Prénom*</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="first_name" required value="<?= htmlspecialchars($first_name) ?>">
                        </div>
                    </div>           

                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Classe*</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="classe" placeholder="ex: IGGLIA 1B" required value="<?= htmlspecialchars($classe) ?>">
                        </div>
                    </div>        

                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Marque Véhicule*</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="marque_vehicule" placeholder="ex: Yamaha" value="<?= htmlspecialchars($marque_vehicule) ?>">
                        </div>
                    </div>  
                    
                    <div class="row mb-3 mt-4">
                        <div class="col d-grid">
                            <button type="submit" class="btn">Générer le Badge</button>
                        </div>
                        <div class="col d-grid">
                            <a href="/index.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php include "layout/footer.php"; ?>
<style>
    footer img, .footer img, footer .text-center img {
        display: none !important;
    }
</style>