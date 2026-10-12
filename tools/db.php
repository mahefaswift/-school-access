<?php
/**
 * tools/db.php - Connexion robuste à la base de données MySQL / MariaDB
 * Supporte automatiquement :
 * - Variables d'environnement standard Cloud/Railway (MYSQLHOST, MYSQL_URL, DATABASE_URL...)
 * - Fallbacks Railway (mysql.railway.internal)
 * - Développement local avec .env (127.0.0.1 & localhost socket UNIX)
 */

if (!function_exists('getDatabaseConnection')) {
function getDatabaseConnection() {
    static $connection = null;

    // Réutilisation de la connexion active si encore vivante
    if ($connection instanceof mysqli && @$connection->ping()) {
        return $connection;
    }

    // 1. Chargement du fichier .env si présent (dev local)
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                putenv($line);
                list($k, $v) = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v, " \t\n\r\0\x0B\"'");
                $_ENV[$k] = $v;
                $_SERVER[$k] = $v;
            }
        }
    }

    // 2. Détection de l'environnement d'hébergement Railway
    $isRailway = !empty(getenv('RAILWAY_ENVIRONMENT'))
        || !empty(getenv('RAILWAY_PROJECT_ID'))
        || !empty(getenv('RAILWAY_STATIC_URL'))
        || !empty(getenv('RAILWAY_PUBLIC_DOMAIN'))
        || (isset($_SERVER['PWD']) && $_SERVER['PWD'] === '/app')
        || (is_dir('/app') && file_exists('/app/tools/db.php'));

    // 3. Extraction depuis une URL de base de données (ex: MYSQL_URL / DATABASE_URL)
    $urlConfig = [];
    $rawUrl = getenv('MYSQL_URL') ?: getenv('MYSQL_PRIVATE_URL') ?: getenv('DATABASE_URL');
    if (!empty($rawUrl)) {
        $parsed = parse_url($rawUrl);
        if ($parsed) {
            if (!empty($parsed['host'])) $urlConfig['host'] = $parsed['host'];
            if (!empty($parsed['user'])) $urlConfig['user'] = $parsed['user'];
            if (isset($parsed['pass']))  $urlConfig['pass'] = $parsed['pass'];
            if (!empty($parsed['port'])) $urlConfig['port'] = (int)$parsed['port'];
            if (!empty($parsed['path'])) $urlConfig['db']   = ltrim($parsed['path'], '/');
        }
    }

    // 4. Résolution ordonnée des paramètres
    // Hôte : URL > MYSQLHOST > MYSQL_HOST > DB_SERVER > DB_HOST > Fallback Railway / Local
    $host = $urlConfig['host']
        ?? (getenv('MYSQLHOST') ?: null)
        ?? (getenv('MYSQL_HOST') ?: null)
        ?? (getenv('DB_SERVER') ?: null)
        ?? (getenv('DB_HOST') ?: null)
        ?? ($isRailway ? 'mysql.railway.internal' : '127.0.0.1');

    // Utilisateur
    $user = $urlConfig['user']
        ?? (getenv('MYSQLUSER') ?: null)
        ?? (getenv('MYSQL_USER') ?: null)
        ?? (getenv('DB_USER') ?: null)
        ?? 'root';

    // Mot de passe
    $pass = $urlConfig['pass']
        ?? (getenv('MYSQLPASSWORD') !== false && getenv('MYSQLPASSWORD') !== '' ? getenv('MYSQLPASSWORD') : null)
        ?? (getenv('MYSQL_PASSWORD') !== false && getenv('MYSQL_PASSWORD') !== '' ? getenv('MYSQL_PASSWORD') : null)
        ?? (getenv('DB_PASS') !== false && getenv('DB_PASS') !== '' ? getenv('DB_PASS') : null)
        ?? (getenv('DB_PASSWORD') !== false && getenv('DB_PASSWORD') !== '' ? getenv('DB_PASSWORD') : null)
        ?? ($isRailway ? 'cJZEGrQmqRRdcHMZgdLtMJxwTXkCKiIN' : '');

    // Nom de la base de données
    $db = $urlConfig['db']
        ?? (getenv('MYSQLDATABASE') ?: null)
        ?? (getenv('MYSQL_DATABASE') ?: null)
        ?? (getenv('DB_NAME') ?: null)
        ?? ($isRailway ? 'railway' : 'school_access_db');

    // Port
    $port = $urlConfig['port']
        ?? (int)(getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: 3306);

    // 5. Normalisation de l'hôte :
    // Sous Linux/Docker, "localhost" utilise un socket UNIX local (/var/run/mysqld/mysqld.sock).
    // Si MySQL tourne via TCP ou sur un autre conteneur, "127.0.0.1" force la connexion TCP.
    $primaryHost = $host;
    $fallbackHost = null;

    if ($primaryHost === 'localhost') {
        $primaryHost = '127.0.0.1';
        $fallbackHost = 'localhost';
    } elseif ($primaryHost === '127.0.0.1') {
        $fallbackHost = 'localhost';
    } elseif ($isRailway && $primaryHost !== 'mysql.railway.internal') {
        $fallbackHost = 'mysql.railway.internal';
    }

    $hostsToAttempt = array_filter(array_unique([$primaryHost, $fallbackHost]));
    $databasesToAttempt = array_filter(array_unique([$db, 'railway', 'school_access_db']));

    $lastException = null;
    $connectedConn = null;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    foreach ($hostsToAttempt as $currentHost) {
        foreach ($databasesToAttempt as $currentDb) {
            try {
                $conn = @new mysqli($currentHost, $user, $pass, $currentDb, $port);
                if (!$conn->connect_error) {
                    $connectedConn = $conn;
                    break 2; // Connexion réussie !
                }
            } catch (mysqli_sql_exception $e) {
                $lastException = $e;
                // Si l'erreur est une base inconnue, on continue pour tester l'autre nom de base
                if (strpos($e->getMessage(), 'Unknown database') !== false) {
                    continue;
                }
                // Si l'hôte n'est pas joignable, on passe au prochain hôte
                break;
            }
        }
    }

    if (!$connectedConn) {
        showFriendlyDbError($lastException ? $lastException->getMessage() : 'Impossible de se connecter au serveur MySQL', [
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'db'   => $db,
            'isRailway' => $isRailway
        ]);
        exit;
    }

    $connectedConn->set_charset('utf8mb4');
    $connection = $connectedConn;
    return $connection;
}
}

/**
 * Affiche une page d'erreur explicite et moderne en cas d'échec de connexion MySQL.
 */
if (!function_exists('showFriendlyDbError')) {
function showFriendlyDbError($errorMessage, array $details) {
    if (!headers_sent()) {
        http_response_code(500);
    }

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_GET['ajax']) && $_GET['ajax'] === '1')
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error'   => 'Erreur de connexion à la base de données',
            'details' => $errorMessage,
            'config'  => [
                'host' => $details['host'],
                'port' => $details['port'],
                'user' => $details['user'],
                'db'   => $details['db']
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return;
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur Base de Données - School Access</title>
        <style>
            :root {
                --bg: #0f172a;
                --card-bg: #1e293b;
                --text: #f8fafc;
                --text-muted: #94a3b8;
                --danger: #ef4444;
                --warning: #f59e0b;
                --primary: #3b82f6;
                --border: #334155;
            }
            body {
                margin: 0;
                padding: 24px;
                background-color: var(--bg);
                color: var(--text);
                font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                box-sizing: border-box;
            }
            .card {
                background: var(--card-bg);
                border: 1px solid var(--border);
                border-radius: 16px;
                padding: 32px;
                max-width: 680px;
                width: 100%;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            }
            .header {
                display: flex;
                align-items: center;
                gap: 16px;
                margin-bottom: 20px;
            }
            .icon-badge {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                background: rgba(239, 68, 68, 0.15);
                color: var(--danger);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
            }
            h1 {
                font-size: 20px;
                margin: 0;
                font-weight: 700;
            }
            .error-box {
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.3);
                border-radius: 8px;
                padding: 12px 16px;
                color: #fca5a5;
                font-family: monospace;
                font-size: 13px;
                margin-bottom: 24px;
                word-break: break-all;
            }
            .section-title {
                font-size: 14px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: var(--text-muted);
                margin: 20px 0 10px;
            }
            .params-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 24px;
                font-size: 14px;
            }
            .params-table td {
                padding: 8px 12px;
                border-bottom: 1px solid var(--border);
            }
            .params-table td:first-child {
                color: var(--text-muted);
                width: 140px;
            }
            .params-table td code {
                background: rgba(255, 255, 255, 0.08);
                padding: 2px 6px;
                border-radius: 4px;
                font-family: monospace;
                color: #38bdf8;
            }
            .guide-box {
                background: rgba(59, 130, 246, 0.08);
                border: 1px solid rgba(59, 130, 246, 0.25);
                border-radius: 10px;
                padding: 16px;
                margin-bottom: 24px;
                font-size: 14px;
                line-height: 1.6;
            }
            .guide-box ol {
                margin: 8px 0 0;
                padding-left: 20px;
            }
            .btn-row {
                display: flex;
                gap: 12px;
                margin-top: 24px;
            }
            .btn {
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 600;
                font-size: 14px;
                cursor: pointer;
                text-decoration: none;
                border: none;
                transition: opacity 0.2s;
            }
            .btn:hover {
                opacity: 0.85;
            }
            .btn-primary {
                background: var(--primary);
                color: #fff;
            }
            .btn-secondary {
                background: #334155;
                color: #e2e8f0;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="header">
                <div class="icon-badge">⚡</div>
                <div>
                    <h1>Erreur de connexion à la base de données</h1>
                    <div style="font-size: 13px; color: var(--text-muted);">School Access Management</div>
                </div>
            </div>

            <div class="error-box">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>

            <div class="section-title">Paramètres détectés lors de la tentative :</div>
            <table class="params-table">
                <tr>
                    <td>Hôte testé</td>
                    <td><code><?= htmlspecialchars($details['host'], ENT_QUOTES, 'UTF-8') ?>:<?= (int)$details['port'] ?></code></td>
                </tr>
                <tr>
                    <td>Utilisateur</td>
                    <td><code><?= htmlspecialchars($details['user'], ENT_QUOTES, 'UTF-8') ?></code></td>
                </tr>
                <tr>
                    <td>Nom de la base</td>
                    <td><code><?= htmlspecialchars($details['db'], ENT_QUOTES, 'UTF-8') ?></code></td>
                </tr>
                <tr>
                    <td>Environnement</td>
                    <td><code><?= $details['isRailway'] ? 'Railway (Production / Cloud)' : 'Local / Docker' ?></code></td>
                </tr>
            </table>

            <div class="guide-box">
                <strong>💡 Comment résoudre ce problème sur Railway :</strong>
                <ol>
                    <li>Rendez-vous sur votre dashboard <strong>Railway</strong>.</li>
                    <li>Cliquez sur votre service <strong>SchoolAccess</strong> (Web) &gt; onglet <strong>Variables</strong>.</li>
                    <li>Ajoutez la variable référencée :<br>
                        <code>MYSQL_URL = ${{MySQL.MYSQL_URL}}</code><br>
                        <em>(ou <code>MYSQLHOST = ${{MySQL.MYSQLHOST}}</code> et <code>MYSQLPASSWORD = ${{MySQL.MYSQLPASSWORD}}</code>)</em>.
                    </li>
                    <li>Railway redéploiera le service automatiquement en quelques secondes.</li>
                </ol>
            </div>

            <div class="btn-row">
                <button class="btn btn-primary" onclick="window.location.reload();">🔄 Réessayer</button>
                <a href="/" class="btn btn-secondary">🏠 Retour à l'accueil</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
}
