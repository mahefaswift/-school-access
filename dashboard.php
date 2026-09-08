<?php
include "layout/header.php";

// V2.0 : Sécurité - Seul le Grand Admin passe !
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "grand_admin") {
    header("location: /index.php");
    exit;
}


include "tools/db.php";
$db = getDatabaseConnection();

$date_jour = date('Y-m-d'); 

$stmt_in = $db->prepare("SELECT COUNT(*) FROM passages WHERE type_mouvement = 'entree' AND DATE(date_heure) = ?");
$stmt_in->bind_param("s", $date_jour);
$stmt_in->execute();
$stmt_in->bind_result($total_entrees);
$stmt_in->fetch();
$stmt_in->close();

$stmt_out = $db->prepare("SELECT COUNT(*) FROM passages WHERE type_mouvement = 'sortie' AND DATE(date_heure) = ?");
$stmt_out->bind_param("s", $date_jour);
$stmt_out->execute();
$stmt_out->bind_result($total_sorties);
$stmt_out->fetch();
$stmt_out->close();

$presents = $total_entrees - $total_sorties;
if ($presents < 0) $presents = 0;
?>

<!-- Import du CSS de DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- CSS Personnalisé -->
<style>
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }

    .card-flottante {
        animation: float 3s ease-in-out infinite;
    }

    .delay-1 {
        animation-delay: 1.5s;
    }

    .titre-gradient {
        background: linear-gradient(45deg, rgb(3, 3, 82), green);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }
</style>

<div class="container py-5">
    <!-- TITRE CORRIGÉ : Sans le badge "Aujourd'hui" -->
    <div class="d-flex align-items-center mb-4 pb-2 border-bottom border-2">
        <h1 class="display-5 fw-bolder titre-gradient mb-0">
            <i class="bi bi-speedometer2 text-primary"></i> Tableau de Bord
        </h1>
    </div>

    <!-- DISPOSITION : Cartes + Graphique -->
    <div class="row mb-5 align-items-stretch">
        
        <!-- Colonne Gauche : Les statistiques (Cartes) -->
        <div class="col-lg-8">
            <div class="row h-100">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card text-white carte-entree shadow rounded-5 card-flottante h-100 border-0">                        <div class="card-body d-flex flex-column justify-content-center align-items-center p-3 text-center">
                            <h5 class="card-title mb-1"><i class="bi bi-people-fill"></i> Personnes à l'intérieur</h5>
                            <p class="display-4 fw-bold mb-1 lh-1"><?= $presents ?></p>
                            <small class="opacity-75">Aujourd'hui : <?= $total_entrees ?> entrées</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-white carte-sortie shadow rounded-5 card-flottante delay-1 h-100 border-0">                        <div class="card-body d-flex flex-column justify-content-center align-items-center p-3 text-center">
                            <h5 class="card-title mb-1"><i class="bi bi-car-front-fill"></i> Véhicules sortis</h5>
                            <p class="display-4 fw-bold mb-1 lh-1"><?= $total_sorties ?></p>
                            <small class="opacity-75">Aujourd'hui</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne Droite : Le Graphique Chart.js -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow rounded-5 h-100 border-0">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
                    <h5 class="card-title fw-bold text-muted mb-2">Répartition des flux</h5>
                    <div style="position: relative; height: 160px; width: 100%;">
                        <canvas id="mouvementsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- TABLEAU DATATABLES -->
    <div class="card shadow rounded-4 border-0">
        <div class="card-header bg-white py-3 rounded-top-4">
            <h5 class="mb-0 fw-bold text-secondary">Historique des 50 derniers passages</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablePassages" class="table table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th> <!-- NOUVELLE COLONNE ICI -->
                            <th>Heure</th>
                            <th>Mouvement</th>
                             <th>Prénom & Nom</th>
                            <th>Classe</th>
                            <th>Code Badge</th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php
                        $query = "SELECT p.date_heure, p.type_mouvement, u.first_name, u.last_name, u.classe, p.code_badge 
                                FROM passages p 
                                JOIN users u ON p.code_badge = u.code_badge 
                                ORDER BY p.date_heure DESC 
                                LIMIT 50";

                        $result = $db->query($query);

                        while ($row = $result->fetch_assoc()) {
                    // Séparation de la date et de l'heure au format français
                        $date_formatee = date('d/m/Y', strtotime($row['date_heure']));
                        $heure_formatee = date('H:i:s', strtotime($row['date_heure']));
                        
                        $badge_couleur = ($row['type_mouvement'] == 'entree') ? 'pastille-entree' : 'pastille-sortie';
                        $mouvement_texte = ($row['type_mouvement'] == 'entree') ? 'ENTRÉE' : 'SORTIE';

                        echo "<tr>";
                        echo "<td data-sort='" . $row['date_heure'] . "'><strong>" . $date_formatee . "</strong></td>";
                        echo "<td>" . $heure_formatee . "</td>";
                        echo "<td><span class='" . $badge_couleur . "'>" . $mouvement_texte . "</span></td>";
                         echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
                         echo "<td>" . htmlspecialchars($row['classe']) . "</td>";
                         echo "<td class='text-muted'>" . htmlspecialchars($row['code_badge']) . "</td>";
                         echo "</tr>";
            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts jQuery, DataTables & Chart.js -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    $('#tablePassages').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        },
        "order": [[ 0, "desc" ]],
        "responsive": true,
        "pageLength": 10
    });
});

const ctx = document.getElementById('mouvementsChart').getContext('2d');
const mouvementsChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Entrées', 'Sorties'],
        datasets: [{
            data: [<?= $total_entrees ?>, <?= $total_sorties ?>], 
            backgroundColor: ['rgb(3, 3, 82)', 'rgba(0, 128, 0)'],
            borderWidth: 0,
            hoverOffset: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 15
                }
            }
        },
        cutout: '70%'
    }
});
</script>

<?php
include "layout/footer.php";
?>