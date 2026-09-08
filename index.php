<?php
// On initialise la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// V2.0 : On autorise le Grand Admin ET le Sous-Admin (Gardien)
$is_personnel = (isset($_SESSION["role"]) && ($_SESSION["role"] === "grand_admin" || $_SESSION["role"] === "sous_admin"));

$message = "";
$alerte_couleur = "";

// TRAITEMENT DU SCAN (POST)
if ($is_personnel && $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['code_badge'])) {
    
    include_once "tools/db.php";
    $db = getDatabaseConnection();
    
    $scanned_badge = trim($_POST['code_badge']); 
    $is_ajax = isset($_POST['ajax']);

    $stmt = $db->prepare("SELECT first_name, last_name, classe FROM users WHERE code_badge = ?");
    $stmt->bind_param("s", $scanned_badge);
    $stmt->execute();
    $stmt->bind_result($prenom, $nom, $classe);

    $reponse = [];

    if ($stmt->fetch()) {
        $stmt->close();

        // 1. SÉCURITÉ ANTI-REBOND : 3 secondes d'écart minimum
        $stmt_anti_spam = $db->prepare("SELECT id FROM passages WHERE code_badge = ? AND date_heure >= (NOW() - INTERVAL 3 SECOND)");
        $stmt_anti_spam->bind_param("s", $scanned_badge);
        $stmt_anti_spam->execute();
        $stmt_anti_spam->store_result();

        if ($stmt_anti_spam->num_rows > 0) {
            $stmt_anti_spam->close();
            $reponse = [
                'type' => 'info',
                'message' => "ℹ️ Scan ignoré : $prenom $nom a déjà été validé à l'instant."
            ];
        } else {
            $stmt_anti_spam->close();

            // 2. Récupérer le dernier mouvement réel
            $stmt2 = $db->prepare("SELECT type_mouvement FROM passages WHERE code_badge = ? ORDER BY date_heure DESC LIMIT 1");
            $stmt2->bind_param("s", $scanned_badge);
            $stmt2->execute();
            $stmt2->bind_result($dernier_mouvement);

            $nouveau_mouvement = "entree"; 
            if ($stmt2->fetch()) {
                if ($dernier_mouvement == "entree") {
                    $nouveau_mouvement = "sortie"; 
                }
            }
            $stmt2->close();

            // 3. Insertion du nouveau mouvement
            $stmt3 = $db->prepare("INSERT INTO passages (code_badge, type_mouvement) VALUES (?, ?)");
            $stmt3->bind_param("ss", $scanned_badge, $nouveau_mouvement);
            $stmt3->execute();
            $stmt3->close();

            if ($nouveau_mouvement == "entree") {
                $reponse = [
                    'type' => 'success',
                    'message' => "✅ ENTRÉE : $prenom $nom ($classe) vient d'entrer."
                ];
            } else {
                $reponse = [
                    'type' => 'warning',
                    'message' => "⬅️ SORTIE : $prenom $nom ($classe) vient de sortir."
                ];
            }
        }

    } else {
        $stmt->close();
        $reponse = [
            'type' => 'danger',
            'message' => "❌ ALERTE : Ce badge ($scanned_badge) est inconnu !"
        ];
    }

    // Réponse directe en JSON si la requête vient du JavaScript (sans recharger la page)
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($reponse);
        exit;
    }

    $alerte_couleur = $reponse['type'];
    $message = $reponse['message'];
}

include "layout/header.php";
?>

<?php if ($is_personnel) { ?>

    <div class="container py-5">
        <div class="container1">
            <div class="box"> 
                <img src="images/logo_ispm.png" alt="projet2.0ispm">
                <p><span>I</span>nstitut <span>S</span>upérieur <span>P</span>olytechnique de <span>M</span>adagascar</p>
            </div>
            
            <div class="box">
                <h1 class="mb-4">Contrôle du Portail 🛡️</h1>

                <div id="zone_alerte" class="alert fs-4 mb-4 fw-bold shadow-sm <?= !empty($message) ? 'alert-' . $alerte_couleur : 'd-none' ?>">
                    <?= $message ?>
                </div>

                <div class="card">
                    <div id="lecteur_qr" class="lecteur_qr mb-4 mx-auto" style="max-width: 350px; width: 100%;">
                        <i class="fa-solid fa-qrcode"></i>   
                        <button type="submit" class="btn1">Start Scanning</button>
                        <p id="scan">Scan an image File</p>
                    </div>

                    <form method="POST" action="index.php" id="formulaire_scan">
                        <label class="form-label fs-5 mb-2 text-muted">Saisie manuelle :</label>
                        <input type="text" id="code_badge" name="code_badge" class="form-control form-control-lg text-center fs-4 mb-3" placeholder="Ex: SA-2026-1234" autocomplete="off" required>
                        
                        <button type="submit" id="btn_submit" class="btn">Valider l'entrée/sortie</button>
                    </form>
                </div>

                <script src="html5-qrcode.js"></script>
                <script>
                    let estEnPause = false; // Verrou anti-spam

                    function envoyerBadge(codeBadge) {
                        if (estEnPause) return;
                        estEnPause = true;

                        const zoneAlerte = document.getElementById('zone_alerte');
                        const champBadge = document.getElementById('code_badge');
                        const btnSubmit = document.getElementById('btn_submit');

                        champBadge.value = codeBadge;
                        btnSubmit.disabled = true;

                        // Envoi asynchrone (AJAX) sans rechargement de page
                        const parametres = new URLSearchParams();
                        parametres.append('code_badge', codeBadge);
                        parametres.append('ajax', '1');

                        fetch('index.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: parametres.toString()
                        })
                        .then(reponse => reponse.json())
                        .then(donnees => {
                            zoneAlerte.className = 'alert alert-' + donnees.type + ' fs-4 mb-4 fw-bold shadow-sm';
                            zoneAlerte.innerHTML = donnees.message;
                            zoneAlerte.classList.remove('d-none');
                        })
                        .catch(erreur => {
                            zoneAlerte.className = 'alert alert-danger fs-4 mb-4 fw-bold shadow-sm';
                            zoneAlerte.innerHTML = '❌ Erreur de communication avec la base de données.';
                            zoneAlerte.classList.remove('d-none');
                        })
                        .finally(() => {
                            champBadge.value = '';
                            btnSubmit.disabled = false;
                            
                            // Débloque le prochain scan après 2.5 secondes
                            setTimeout(() => {
                                estEnPause = false;
                            }, 2500);
                        });
                    }

                    // 1. Scan automatique par la caméra
                    function surSuccesScan(texteDecode, resultatDecode) {
                        envoyerBadge(texteDecode);
                    }

                    function surErreurScan(erreur) {}

                    let scanner = new Html5QrcodeScanner(
                        "lecteur_qr", 
                        { fps: 10, qrbox: {width: 200, height: 200} }, 
                        false
                    );
                    scanner.render(surSuccesScan, surErreurScan);

                    // 2. Soumission manuelle par formulaire
                    document.getElementById('formulaire_scan').addEventListener('submit', function(e) {
                        e.preventDefault();
                        const code = document.getElementById('code_badge').value.trim();
                        if (code) {
                            envoyerBadge(code);
                        }
                    });

                    // 3. Traduction dynamique des boutons
                    setInterval(function() {
                        let boutons = document.querySelectorAll('#lecteur_qr button');
                        boutons.forEach(btn => {
                            if (btn.innerText.includes('Start Scanning')) {
                                btn.innerText = 'Démarrer la caméra';
                                btn.className = 'btn1'; 
                            }
                            if (btn.innerText.includes('Stop Scanning')) {
                                btn.innerText = 'Arrêter la caméra';
                                btn.className = 'btn1';
                            }
                            if (btn.innerText.includes('Request Camera Permissions')) {
                                btn.innerText = 'Autoriser l\'accès à la caméra';
                            }
                        });

                        let liens = document.querySelectorAll('#lecteur_qr a');
                        liens.forEach(lien => {
                            if (lien.innerText.includes('Scan an Image File')) {
                                lien.innerText = 'Scanner un fichier image';
                            }
                            if (lien.innerText.includes('Scan using camera directly')) {
                                lien.innerText = 'Utiliser directement la caméra';
                            }
                        });
                    }, 300);
                </script>
            </div>
        </div>
    </div>

<?php } else { ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .hero-modern {
            background: linear-gradient(135deg, #0b4b2a 0%, #198754 100%);
            min-height: 75vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-modern::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
        }

        .hero-content {
            z-index: 1;
            position: relative;
        }

        .curseur-moto {
            display: inline-block;
            color: #fff;
            font-size: 3rem; 
            margin-left: 10px;
            transform: scaleX(-1); 
            animation: roule 0.4s infinite alternate;
            transition: all 0.1s ease;
        }

        @keyframes roule {
            0% { transform: scaleX(-1) translateY(0) rotate(0deg); }
            100% { transform: scaleX(-1) translateY(-3px) rotate(-5deg); }
        }

        .logo-anime {
            max-width: 420px;
            width: 100%;
            background-color: white;
            border-radius: 50%;
            padding: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            animation: float 5s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        .apparition-lente {
            opacity: 0;
            animation: apparait 1s ease-in forwards;
            animation-delay: 2.5s;
            color: #ffffff !important; 
        }
        
        @keyframes apparait {
            to { opacity: 1; }
        }

        #ligne1 { color: #ffffff !important; }
        #ligne2 { color: #a3e4d7 !important; }
    </style>

    <div class="hero-modern">
        <div class="container py-5 hero-content">
            <div class="row align-items-center g-5">
                <div class="col-md-6 text-center">
                    <img src="images/logo_ispm.png" class="img-fluid logo-anime" alt="Logo ISPM" />
                </div>
                <div class="col-md-6">
                    <h1 class="mb-4 display-4 fw-bolder" style="letter-spacing: -1.5px; min-height: 180px;">
                        <span id="ligne1"></span><br>
                        <span id="ligne2"></span>
                        <span id="curseur" class="curseur-moto"><i class="fa-solid fa-motorcycle"></i></span>
                    </h1>
                    <p class="lead mb-4 fs-4 apparition-lente" style="font-weight: 300;">
                        Enregistrez facilement l'entrée et la sortie des véhicules dans notre établissement pour un gain de temps et une sécurité maximale.
                    </p>
                </div>
            </div>
        </div>            
    </div> 

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const texte1 = "Une Entrée Et Une Sortie";
            const texte2 = "Qui Facilite La Vie";
            const vitesse = 60; 
            
            let i = 0;
            let j = 0;
            const curseur = document.getElementById("curseur");
            
            function ecrireLigne1() {
                if (i < texte1.length) {
                    document.getElementById("ligne1").innerHTML += texte1.charAt(i);
                    i++;
                    setTimeout(ecrireLigne1, vitesse);
                } else {
                    setTimeout(ecrireLigne2, 400);
                }
            }
            
            function ecrireLigne2() {
                if (j < texte2.length) {
                    document.getElementById("ligne2").innerHTML += texte2.charAt(j);
                    j++;
                    setTimeout(ecrireLigne2, vitesse);
                }
            }
            
            setTimeout(ecrireLigne1, 500); 
        });
    </script>

<?php } ?>

<?php include "layout/footer.php"; ?>
<style>
    footer img, .footer img, footer .text-center img {
        display: none !important;
    }
</style>