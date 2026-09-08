<?php
include "layout/header.php";

// Sécurité stricte : Seul le Grand Admin peut afficher cette page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "grand_admin") {
    header("location: /index.php");
    exit;
}

$nom = $prenom = $email = $role = "";
$message = $erreur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Vérifications de base
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($role)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } elseif ($password !== $confirm_password) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        include "tools/db.php";
        $db = getDatabaseConnection();

        // Vérifier si l'email existe déjà
        $stmt_check = $db->prepare("SELECT id FROM administrateurs WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        
        if ($stmt_check->fetch()) {
            $erreur = "Cet email est déjà utilisé par un autre compte.";
        } else {
            // Hachage du mot de passe pour la sécurité
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $statut = "actif"; // Par défaut, un nouveau compte est actif

            $stmt_check->close(); // Fermer la requête précédente

            // Insertion du nouvel utilisateur
            $stmt_insert = $db->prepare("INSERT INTO administrateurs (nom, prenom, email, password, role, statut) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssssss", $nom, $prenom, $email, $hashed_password, $role, $statut);
            
            if ($stmt_insert->execute()) {
                $message = "Le compte de $prenom $nom a été créé avec succès !";
                // Réinitialiser les champs après succès
                $nom = $prenom = $email = $role = ""; 
            } else {
                $erreur = "Erreur lors de la création du compte.";
            }
            $stmt_insert->close();
        }
    }
}
?>

<div class="container py-5">
    <div class="row align-items-center bg-white shadow-sm rounded-4 p-4">
        
        <!-- Colonne Gauche : Logo (Adapté à ta maquette) -->
        <div class="col-md-5 text-center mb-4 mb-md-0 border-end d-none d-md-block">
            <!-- Remplace le src par le chemin exact de ton logo ISPM -->
            <img src="images/logo_ispm.png" alt="Logo ISPM" class="img-fluid mb-3" style="max-height: 250px;">
            <h5 class="text-muted font-monospace">Institut Supérieur Polytechnique de Madagascar</h5>
        </div>

        <!-- Colonne Droite : Formulaire -->
        <div class="col-md-7 ps-md-5">
            <h2 class="fw-bold mb-4 text-uppercase">Ajouter un nouvel utilisateur</h2>
            
            <?php if (!empty($erreur)) { ?>
                <div class="alert alert-danger"><?= $erreur ?></div>
            <?php } ?>
            <?php if (!empty($message)) { ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php } ?>

            <form method="POST" action="ajouter_admin.php">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="ex: Jean" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($prenom) ?>" placeholder="ex: Pierre" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="ex: jean.pierre@ispm.mg" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Mot de Passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmer le Mot de Passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label d-block">Rôle :</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="role" id="roleGrand" value="grand_admin" <?= ($role == 'grand_admin') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="roleGrand">Grand Admin (Accès Complet)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="roleSous" value="sous_admin" <?= ($role == 'sous_admin') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="roleSous">Sous-Admin (Scan Uniquement, Pas de Données)</label>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/gestion_personnel.php" class="btn btn-secondary px-4">Annuler</a>
                    <button type="submit" class="btn btn-dark px-4">Créer le Compte</button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php include "layout/footer.php"; ?>
<style>
    /* Cache l'image du logo ISPM uniquement dans le footer de cette page */
    footer img, .footer img {
        display: none !important;
    }
</style>