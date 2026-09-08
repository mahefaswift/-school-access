<?php
include "layout/header.php";

// Si déjà connecté, on redirige vers la bonne page selon le rôle
if (isset($_SESSION["email"])) {
    if ($_SESSION["role"] === "grand_admin") {
        header("location: /dashboard.php"); 
    } else {
        header("location: /index.php"); 
    }
    exit;
}

$email = "";
$error = "";

// Traitement du formulaire quand on clique sur "Se connecter"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "L'email et le mot de passe sont requis.";
    } else {
        include "tools/db.php";
        $dbConnection = getDatabaseConnection();

        // V2.0 : On cherche l'email et on récupère les nouveaux champs (role, statut, nom, prenom)
        $statement = $dbConnection->prepare(
            "SELECT id, email, password, nom, prenom, role, statut FROM administrateurs WHERE email = ?"
        );

        $statement->bind_param('s', $email);
        $statement->execute();
        
        // On attache les résultats aux variables
        $statement->bind_result($id, $db_email, $stored_password, $db_nom, $db_prenom, $db_role, $db_statut);

        if ($statement->fetch()) {
            // On vérifie le mot de passe
            if (password_verify($password, $stored_password)) {
                
                // V2.0 : On bloque l'accès si le compte a été suspendu
                if ($db_statut === 'inactif') {
                    $error = "Accès refusé. Ce compte a été désactivé.";
                } else {
                    // Connexion réussie ! On crée les sessions avec les nouvelles données
                    $_SESSION["admin_id"] = $id;
                    $_SESSION["email"] = $db_email;
                    $_SESSION["nom"] = $db_nom;
                    $_SESSION["prenom"] = $db_prenom;
                    $_SESSION["role"] = $db_role; 

                    // V2.0 : L'aiguillage (Redirection selon le rôle)
                    if ($_SESSION["role"] === "grand_admin") {
                        // Le Grand Admin va sur le tableau de bord
                        header("location: /dashboard.php"); 
                    } else {
                        // Le Sous-Admin (Gardien) va directement sur le scan
                        header("location: /index.php"); 
                    }
                    exit;
                }
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Aucun compte administrateur trouvé avec cet email.";
        }
        $statement->close();
    }
}
?>

<div class="container py-5">
    <div class="mx-auto border shadow p-4" style="max-width: 400px; width: 100%; background-color: white; border-radius: 10px;">
        <h2 class="text-center mb-4">Administration</h2>
        <hr />

        <?php if (!empty($error)) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><?= $error ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" />
            </div>

            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" class="form-control" name="password" />
            </div>
            
            <div class="row mb-3 mt-4">
                <div class="col d-grid">
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </div>
                <div class="col d-grid">
                    <a href="/index.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
include "layout/footer.php";
?>
<style>
    /* On cache l'image du footer uniquement sur cette page pour éviter le doublon */
    footer img, .footer img, footer .text-center img {
        display: none !important;
    }
</style>