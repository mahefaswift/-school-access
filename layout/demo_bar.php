<?php
// layout/demo_bar.php - Barre / Ardoise interactive pour les recruteurs
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_role = $_SESSION["role"] ?? "visiteur";
$current_page = $_SERVER["REQUEST_URI"] ?? "/index.php";
$demo_notice = $_SESSION["demo_notice"] ?? null;
unset($_SESSION["demo_notice"]);
?>

<style>
/* =======================================================
   ARDOISE DÉMO / SANDBOX RECRUTEUR - STYLES
   ======================================================= */
#demo-sandbox-root {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 10000;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
}

.demo-bar-dock {
    background: rgba(15, 23, 42, 0.94);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.35);
    padding: 8px 16px;
    color: #e2e8f0;
}

.demo-pill-badge {
    background: linear-gradient(135deg, #0ea5e9, #10b981);
    color: #ffffff;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.5px;
    padding: 4px 10px;
    border-radius: 9999px;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.demo-role-indicator {
    font-size: 13px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.demo-role-grand-admin {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.4);
}

.demo-role-sous-admin {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.4);
}

.demo-role-visiteur {
    background: rgba(148, 163, 184, 0.15);
    color: #cbd5e1;
    border: 1px solid rgba(148, 163, 184, 0.3);
}

.demo-btn-action {
    font-size: 12px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 6px;
    text-decoration: none !important;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid transparent;
}

.demo-btn-action:hover {
    transform: translateY(-1px);
}

.demo-btn-admin {
    background: #d97706;
    color: #fff !important;
}
.demo-btn-admin:hover { background: #b45309; }

.demo-btn-gardien {
    background: #2563eb;
    color: #fff !important;
}
.demo-btn-gardien:hover { background: #1d4ed8; }

.demo-btn-visiteur {
    background: rgba(255, 255, 255, 0.1);
    color: #e2e8f0 !important;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.demo-btn-visiteur:hover { background: rgba(255, 255, 255, 0.2); }

.demo-btn-drawer {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff !important;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
}
.demo-btn-drawer:hover { filter: brightness(1.1); }

/* Panneau tiroir extensible (Drawer) */
#demo-sandbox-drawer {
    display: none;
    background: #0f172a;
    color: #f1f5f9;
    border-top: 2px solid #0284c7;
    max-height: 80vh;
    overflow-y: auto;
    padding: 24px;
    box-shadow: 0 -12px 30px rgba(0, 0, 0, 0.6);
}

#demo-sandbox-drawer.active {
    display: block;
    animation: slideUp 0.25s ease-out;
}

@keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.demo-card-box {
    background: rgba(30, 41, 59, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    padding: 16px;
    height: 100%;
}

.demo-card-box h6 {
    color: #38bdf8;
    font-weight: 700;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.demo-link-item {
    display: block;
    padding: 8px 12px;
    border-radius: 6px;
    color: #cbd5e1;
    text-decoration: none;
    font-size: 13px;
    margin-bottom: 6px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.15s;
}

.demo-link-item:hover {
    background: rgba(14, 165, 233, 0.15);
    color: #38bdf8;
    border-color: rgba(14, 165, 233, 0.4);
}

.demo-link-item.active {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border-color: rgba(16, 185, 129, 0.5);
    font-weight: 600;
}

.demo-badge-trigger {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    margin-bottom: 8px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #e2e8f0;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
}

.demo-badge-trigger:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: #38bdf8;
}

.demo-badge-trigger code {
    background: #1e293b;
    color: #38bdf8;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
}

/* Toast de notification */
.demo-toast-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10001;
    min-width: 300px;
    max-width: 450px;
    padding: 14px 18px;
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    animation: fadeIn 0.3s;
}

/* Espace réservé en bas de page pour que l'ardoise ne masque rien */
body {
    padding-bottom: 60px !important;
}
</style>

<div id="demo-sandbox-root">

    <!-- TIROIR EXTENSIBLE (DRAWER) -->
    <div id="demo-sandbox-drawer">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
                <div class="d-flex align-items-center gap-3">
                    <span class="demo-pill-badge">🎛️ Ardoise Recruteur</span>
                    <h5 class="mb-0 text-white fw-bold">Explorez 100% des fonctionnalités en 1 clic</h5>
                </div>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleDemoDrawer()">
                    ✕ Fermer
                </button>
            </div>

            <div class="row g-3">
                <!-- COLONNE 1 : RÔLES & PERMISSIONS -->
                <div class="col-md-3">
                    <div class="demo-card-box">
                        <h6><i class="fa-solid fa-user-shield"></i> Changer de Rôle</h6>
                        <p class="text-secondary small mb-3">
                            Basculez instantanément pour tester la granularité des autorisations :
                        </p>
                        
                        <div class="d-grid gap-2">
                            <a href="/demo_controller.php?action=login_grand_admin&redirect=<?= urlencode($current_page) ?>" class="demo-btn-action demo-btn-admin">
                                👑 <strong>Grand Admin</strong> (Alicia)
                            </a>
                            <small class="text-secondary" style="font-size: 11px;">Accès total : Dashboard, Personnel, Portail</small>

                            <a href="/demo_controller.php?action=login_sous_admin&redirect=/index.php" class="demo-btn-action demo-btn-gardien mt-2">
                                🛡️ <strong>Gardien</strong> (Tovo)
                            </a>
                            <small class="text-secondary" style="font-size: 11px;">Accès restreint au Portail de Scan uniquement</small>

                            <a href="/demo_controller.php?action=logout&redirect=<?= urlencode($current_page) ?>" class="demo-btn-action demo-btn-visiteur mt-2">
                                👤 <strong>Mode Visiteur</strong>
                            </a>
                            <small class="text-secondary" style="font-size: 11px;">Page d'accueil publique & demande de badge</small>
                        </div>
                    </div>
                </div>

                <!-- COLONNE 2 : SIMULATEUR DE BADGES (SANS CAMÉRA) -->
                <div class="col-md-5">
                    <div class="demo-card-box">
                        <h6><i class="fa-solid fa-qrcode"></i> Simulateur de Badges (En direct)</h6>
                        <p class="text-secondary small mb-2">
                            Cliquez sur un badge ci-dessous pour tester l'entrée/sortie, l'anti-spam (3s) et les alertes sans caméra :
                        </p>

                        <div class="demo-badge-trigger" onclick="simulerScanBadge('SA-2026-7406')">
                            <div>
                                <strong>Paul DUPONT</strong> <span class="text-secondary small">(GCA - Yamaha)</span>
                            </div>
                            <code>SA-2026-7406</code>
                        </div>

                        <div class="demo-badge-trigger" onclick="simulerScanBadge('SA-2026-6630')">
                            <div>
                                <strong>Rindra RAFITIAMALALA</strong> <span class="text-secondary small">(GCA 1)</span>
                            </div>
                            <code>SA-2026-6630</code>
                        </div>

                        <div class="demo-badge-trigger" onclick="simulerScanBadge('SA-2026-8582')">
                            <div>
                                <strong>Sarobidy RABE</strong> <span class="text-secondary small">(Jbubu)</span>
                            </div>
                            <code>SA-2026-8582</code>
                        </div>

                        <div class="demo-badge-trigger border-danger" onclick="simulerScanBadge('SA-INCONNU-9999')">
                            <div>
                                <span class="text-danger fw-bold">❌ Badge Inconnu (Test Alerte)</span>
                            </div>
                            <code class="text-danger">SA-INCONNU-9999</code>
                        </div>

                        <div id="demo-scan-result" class="alert alert-dark small mb-0 mt-2 d-none">
                            <span id="demo-scan-text">En attente de test...</span>
                        </div>
                    </div>
                </div>

                <!-- COLONNE 3 : NAVIGATION DIRECTE & DONNÉES -->
                <div class="col-md-4">
                    <div class="demo-card-box">
                        <h6><i class="fa-solid fa-compass"></i> Accès direct aux Vues</h6>
                        
                        <a href="/index.php" class="demo-link-item <?= ($current_page == '/index.php' || $current_page == '/') ? 'active' : '' ?>">
                            🚪 <strong>Portail & Scan QR</strong>
                            <span class="text-secondary d-block" style="font-size: 11px;">Scanner caméra + validation en direct</span>
                        </a>

                        <a href="/dashboard.php" class="demo-link-item <?= ($current_page == '/dashboard.php') ? 'active' : '' ?>">
                            📊 <strong>Tableau de Bord</strong>
                            <span class="text-secondary d-block" style="font-size: 11px;">Statistiques du jour, Donut Chart.js, DataTables</span>
                        </a>

                        <a href="/gestion_personnel.php" class="demo-link-item <?= ($current_page == '/gestion_personnel.php') ? 'active' : '' ?>">
                            👥 <strong>Gestion du Personnel</strong>
                            <span class="text-secondary d-block" style="font-size: 11px;">Gestion des comptes gardiens & administrateurs</span>
                        </a>

                        <a href="/register.php" class="demo-link-item <?= ($current_page == '/register.php') ? 'active' : '' ?>">
                            🎫 <strong>Demande de Badge</strong>
                            <span class="text-secondary d-block" style="font-size: 11px;">Formulaire + génération autonome de QR code</span>
                        </a>

                        <div class="mt-3 pt-2 border-top border-secondary">
                            <span class="text-secondary small d-block mb-2">⚡ Données du Dashboard :</span>
                            <div class="d-flex gap-2">
                                <a href="/demo_controller.php?action=seed_today&redirect=/dashboard.php" class="btn btn-sm btn-outline-success flex-grow-1" style="font-size: 11px;">
                                    ⚡ Animer le Dashboard
                                </a>
                                <a href="/demo_controller.php?action=reset_today&redirect=/dashboard.php" class="btn btn-sm btn-outline-danger flex-grow-1" style="font-size: 11px;">
                                    🧹 Vider le jour
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- DOCK FLOTTANT VISIBLE EN PERMANENCE -->
    <div class="demo-bar-dock">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-2">
            
            <!-- GAUCHE : IDENTIFICATION & RÔLE ACTUEL -->
            <div class="d-flex align-items-center gap-2">
                <span class="demo-pill-badge">🎛️ Sandbox Démo</span>
                <span class="text-light small d-none d-md-inline">Rôle actif :</span>
                <?php if ($current_role === 'grand_admin') { ?>
                    <span class="demo-role-indicator demo-role-grand-admin">👑 Grand Admin (Alicia)</span>
                <?php } elseif ($current_role === 'sous_admin') { ?>
                    <span class="demo-role-indicator demo-role-sous-admin">🛡️ Gardien (Tovo)</span>
                <?php } else { ?>
                    <span class="demo-role-indicator demo-role-visiteur">👤 Visiteur (Public)</span>
                <?php } ?>
            </div>

            <!-- MILIEU : BOUTONS DE CONNEXION RAPIDE 1-CLIC -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-secondary small d-none d-lg-inline">Bascule rapide :</span>
                <a href="/demo_controller.php?action=login_grand_admin&redirect=<?= urlencode($current_page) ?>" class="demo-btn-action demo-btn-admin" title="Se connecter en Grand Admin en 1 clic">
                    👑 Grand Admin
                </a>
                <a href="/demo_controller.php?action=login_sous_admin&redirect=/index.php" class="demo-btn-action demo-btn-gardien" title="Se connecter en Gardien en 1 clic">
                    🛡️ Gardien
                </a>
                <?php if ($current_role !== 'visiteur') { ?>
                    <a href="/demo_controller.php?action=logout&redirect=<?= urlencode($current_page) ?>" class="demo-btn-action demo-btn-visiteur" title="Déconnexion (Mode Visiteur)">
                        👤 Déconnexion
                    </a>
                <?php } ?>
            </div>

            <!-- DROITE : BOUTON TIROIR COMPLET -->
            <div>
                <button type="button" class="demo-btn-action demo-btn-drawer" onclick="toggleDemoDrawer()">
                    🚀 Ardoise Complète & Simulateur
                </button>
            </div>

        </div>
    </div>
</div>

<!-- GESTION DES NOTIFICATIONS TOAST -->
<?php if ($demo_notice) { ?>
<div class="demo-toast-alert alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($demo_notice) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php } ?>

<script>
function toggleDemoDrawer() {
    const drawer = document.getElementById('demo-sandbox-drawer');
    if (drawer.classList.contains('active')) {
        drawer.classList.remove('active');
    } else {
        drawer.classList.add('active');
    }
}

// SIMULATION DE SCAN DIRECT EN AJAX
function simulerScanBadge(codeBadge) {
    const box = document.getElementById('demo-scan-result');
    const txt = document.getElementById('demo-scan-text');
    box.className = 'alert alert-info small mb-0 mt-2';
    txt.innerHTML = '⏳ Traitement du scan de <code>' + codeBadge + '</code>...';
    box.classList.remove('d-none');

    const params = new URLSearchParams();
    params.append('code_badge', codeBadge);
    params.append('ajax', '1');

    fetch('/index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        let alertClass = 'alert-info';
        if (data.type === 'success') alertClass = 'alert-success';
        if (data.type === 'warning') alertClass = 'alert-warning';
        if (data.type === 'danger') alertClass = 'alert-danger';
        
        box.className = 'alert ' + alertClass + ' small mb-0 mt-2 fw-bold';
        txt.innerHTML = data.message;

        // Si on est sur index.php avec le scanner actif, mettre aussi à jour la zone d'alerte principale
        const mainAlert = document.getElementById('zone_alerte');
        if (mainAlert) {
            mainAlert.className = 'alert alert-' + data.type + ' fs-4 mb-4 fw-bold shadow-sm';
            mainAlert.innerHTML = data.message;
            mainAlert.classList.remove('d-none');
        }
    })
    .catch(err => {
        // Si non connecté en tant que personnel, index.php ne traite pas le POST
        // On propose de se connecter en 1 clic
        box.className = 'alert alert-warning small mb-0 mt-2';
        txt.innerHTML = '⚠️ Pour valider ce scan au portail, <a href="/demo_controller.php?action=login_sous_admin&redirect=/index.php" class="alert-link text-decoration-underline">connectez-vous d\'abord en Gardien ou Grand Admin (1 clic)</a>.';
    });
}
</script>
