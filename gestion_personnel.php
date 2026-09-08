<?php
include "layout/header.php";

// Sécurité stricte : Seul le Grand Admin peut afficher cette page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "grand_admin") {
    header("location: /index.php");
    exit;
}

include "tools/db.php";
$db = getDatabaseConnection();
?>

<!-- Import pour DataTables et FontAwesome (pour les icônes du groupe) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  /* Styles importés de style.css pour cette page spécifique */
  .header-top { display: flex; justify-content: space-between; align-items: center; margin: 20px 0; }
  .header-top h2 { color: rgb(3, 3, 82); margin: 0; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; }
  .add-user-container { text-align: right; margin-bottom: 15px; }
  .btn-add { background-color: rgb(3, 3, 82); color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: bold; text-decoration: none; transition: background-color 0.2s; display: inline-block; }
  .btn-add:hover { background-color: rgb(3, 3, 82); color: white; }
  .custom-card { background: white; border-radius: 6px; padding: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); margin-bottom: 30px; font-family: Arial, sans-serif; }
  .role-badge { font-weight: bold; font-size: 13px; }
  .role-grand-admin { color: #102a43; background-color: #d9e2ec; padding: 4px 8px; border-radius: 4px; }
  .role-sous-admin { color: #333; }
  .action-btn { border: none; background: none; cursor: pointer; padding: 4px 6px; font-size: 14px; text-decoration: none; }
  .btn-edit { color: #28a745; }
  .btn-delete { color: #dc3545; }
  .btn-disabled { color: #aaa; cursor: not-allowed; }
  /* Ajustements pour que DataTables s'intègre bien au design */
  table.dataTable { border-collapse: collapse !important; width: 100%; margin-bottom: 15px; }
  table.dataTable th, table.dataTable td { text-align: left; padding: 10px; border-bottom: 1px solid #e0e0e0; font-size: 14px; }
  table.dataTable th { background-color: #fff; }
</style>

<div class="container py-4">
    
    <div class="header-top">
        <h2>Gestion du Personnel</h2>
    </div>

    <div class="add-user-container">
        <a href="/ajouter_admin.php" class="btn-add">[ + Ajouter un nouvel utilisateur ]</a>
    </div>

    <div class="custom-card">
        <table id="personnelTable" class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th class="no-sort">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT id, nom, prenom, email, role FROM administrateurs ORDER BY nom ASC";
                $result = $db->query($query);

                while ($row = $result->fetch_assoc()) {
                    // Application des classes du groupe pour les badges
                    if ($row['role'] === 'grand_admin') {
                        $role_display = '<span class="role-badge role-grand-admin">Grand Admin</span>';
                    } else {
                        $role_display = '<span class="role-badge role-sous-admin">Sous-Admin</span>';
                    }

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars(strtoupper($row['nom'])) . "</td>";
                    echo "<td>" . htmlspecialchars(ucfirst($row['prenom'])) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . $role_display . "</td>";
                    echo "<td>";
                    
                    // Bouton Modifier
                    echo "[ <a href='/modifier_admin.php?id=" . $row['id'] . "' class='action-btn btn-edit' title='Modifier'><i class='fa-regular fa-pen-to-square'></i></a> ] ";
                    
                    // Sécurité pour la suppression
                    if ($row['id'] == $_SESSION['admin_id']) {
                         echo "[ <span class='action-btn btn-disabled' title='Vous ne pouvez pas supprimer votre propre compte'><i class='fa-solid fa-trash-can'></i></span> ]";
                    } else {
                         echo "[ <a href='/supprimer_admin.php?id=" . $row['id'] . "' class='action-btn btn-delete' onclick='return confirm(\"Voulez-vous vraiment supprimer cet utilisateur ?\");' title='Supprimer'><i class='fa-solid fa-trash-can'></i></a> ]";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts pour faire fonctionner DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#personnelTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        },
        "pageLength": 10,
        "ordering": true,
        "info": true,
        "lengthChange": true,
        "columnDefs": [
            { "orderable": false, "targets": 4 } // Désactive le tri sur la colonne Actions
        ]
    });
});
</script>

<?php include "layout/footer.php"; ?>