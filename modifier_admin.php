<?php
include "layout/header.php";

// Sécurité stricte : Seul le Grand Admin peut afficher cette page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "grand_admin") {
    header("location: /index.php");
    exit;
}

include "tools/db.php";
$db = getDatabaseConnection();

$id = $nom = $prenom = $email = $role = "";
$message = $erreur = "";

// 1. LECTURE : Récupération des données actuelles pour pré-remplir le formulaire
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $db->prepare("SELECT nom, prenom, email, role FROM administrateurs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nom, $prenom, $email, $role);
    
    // Si l'ID n'existe pas, on redirige vers le tableau
    if (!$stmt->fetch()) {
        header("location: /gestion_personnel.php");
        exit;
    }
    $stmt->close();
}

// 2. ÉCRITURE : Traitement du formulaire quand tu cliques sur "Mettre à jour"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $nouveau_password = $_POST['nouveau_password'];

    // Vérification des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($email) || empty($role)) {
        $erreur = "Veuillez remplir tous les champs obligatoires (le mot de passe est facultatif).";
    } else {
        // Préparation de la mise à jour
        if (!empty($nouveau_password)) {
            // Si tu as tapé un nouveau mot de passe, on le hache et on le met à jour
            $hashed_password = password_hash($nouveau_password, PASSWORD_DEFAULT);
            $stmt_update = $db->prepare("UPDATE administrateurs SET nom=?, prenom=?, email=?, role=?, password=? WHERE id=?");
            $stmt_update->bind_param("sssssi", $nom, $prenom, $email, $role, $hashed_password, $id);
        } else {
            // Si le champ est vide, on met à jour le reste SANS toucher au mot de passe
            $stmt_update = $db->prepare("UPDATE administrateurs SET nom=?, prenom=?, email=?, role=? WHERE id=?");
            $stmt_update->bind_param("ssssi", $nom, $prenom, $email, $role, $id);
        }

        if ($stmt_update->execute()) {
            $message = "Le profil de $prenom $nom a été mis à jour avec succès !";
            
            // Astuce : Si tu as modifié ton propre profil, on met à jour la session pour que ton prénom change en direct !
            if ($id == $_SESSION["admin_id"]) {
                $_SESSION["nom"] = $nom;
                $_SESSION["prenom"] = $prenom;
            }
        } else {
            $erreur = "Erreur lors de la mise à jour.";
        }
        $stmt_update->close();
    }
}
?>

<div class="container py-5">
    <div class="row align-items-center bg-white shadow-sm rounded-4 p-4">
        
        <!-- Colonne Gauche : Design -->
        <div class="col-md-5 text-center mb-4 mb-md-0 border-end d-none d-md-block">
            <h1 class="display-1 text-primary"><i class="bi bi-person-gear"></i></h1>
            <h4 class="text-muted mt-3">Modification de profil</h4>
            <p class="small text-secondary">Mettez à jour les accès et les informations du personnel.</p>
        </div>

        <!-- Colonne Droite : Formulaire de modification -->
        <div class="col-md-7 ps-md-5">
            <h2 class="fw-bold mb-4 text-uppercase">Modifier l'utilisateur</h2>
            
            <?php if (!empty($erreur)) { ?>
                <div class="alert alert-danger"><?= $erreur ?></div>
            <?php } ?>
            <?php if (!empty($message)) { ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php } ?>

            <form method="POST" action="modifier_admin.php">
                <!-- Champ caché pour renvoyer l'ID lors de la soumission du formulaire -->
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($nom) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($prenom) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Nouveau Mot de Passe <em>(laissez vide pour conserver l'actuel)</em></label>
                    <input type="password" class="form-control" name="nouveau_password" placeholder="••••••••">
                </div>

                <div class="mb-4">
                    <label class="form-label d-block">Rôle :</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="role" id="roleGrand" value="grand_admin" <?= ($role == 'grand_admin') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="roleGrand">Grand Admin</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="role" id="roleSous" value="sous_admin" <?= ($role == 'sous_admin') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="roleSous">Sous-Admin</label>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <a href="/gestion_personnel.php" class="btn btn-secondary px-4">Retour au tableau</a>
                    <button type="submit" class="btn btn-primary px-4" style="background-color: #08618d; border: none;">Mettre à jour</button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php include "layout/footer.php"; ?>