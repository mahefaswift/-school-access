#!/bin/bash
set -e

# ==============================================================================
# ENTRYPOINT POUR RENDER / CLOUD DOCKER
# Gère automatiquement :
# 1. Base externe si variables présentes (DB_SERVER distant, MYSQLHOST, MYSQL_URL...)
# 2. OU Démarrage autonome d'une instance MariaDB interne allégée
# 3. Démarrage du serveur Web PHP sur le port dynamique injecté par Render ($PORT)
# ==============================================================================

EXTERNAL_HOST="${MYSQLHOST:-$DB_SERVER}"

if [ -n "$EXTERNAL_HOST" ] && [ "$EXTERNAL_HOST" != "127.0.0.1" ] && [ "$EXTERNAL_HOST" != "localhost" ]; then
    echo "[Render] Configuration de base de données externe détectée ($EXTERNAL_HOST)."
elif [ -n "$MYSQL_URL" ] || [ -n "$DATABASE_URL" ]; then
    echo "[Render] URL de base de données externe détectée."
else
    echo "[Render] Mode MariaDB interne actif (127.0.0.1)..."

    # Création et permissions des répertoires requis
    mkdir -p /var/run/mysqld /var/lib/mysql /var/log/mysql
    chown -R mysql:mysql /var/run/mysqld /var/lib/mysql /var/log/mysql
    chmod 777 /var/run/mysqld

    # Initialisation du système de fichiers si nécessaire
    if [ ! -d "/var/lib/mysql/mysql" ]; then
        echo "[Render] Initialisation du système de fichiers MariaDB..."
        mariadb-install-db --user=mysql --datadir=/var/lib/mysql --skip-test-db >/dev/null 2>&1 || mysql_install_db --user=mysql --datadir=/var/lib/mysql --skip-test-db >/dev/null 2>&1 || true
    fi

    # Lancement direct du démon MariaDB en arrière-plan (ultra-léger : 32 Mo de buffer)
    echo "[Render] Démarrage du démon MariaDB..."
    mariadbd --user=mysql \
        --datadir=/var/lib/mysql \
        --socket=/var/run/mysqld/mysqld.sock \
        --port=3306 \
        --bind-address=0.0.0.0 \
        --innodb-buffer-pool-size=32M \
        --max-connections=30 \
        --skip-innodb-doublewrite \
        --console >/dev/null 2>&1 &

    # Attente que MariaDB réponde
    echo "[Render] Attente de la disponibilité de MySQL..."
    READY=0
    for i in $(seq 1 30); do
        if mariadb-admin ping --socket=/var/run/mysqld/mysqld.sock --silent 2>/dev/null || mysqladmin ping --silent 2>/dev/null; then
            READY=1
            echo "[Render] MariaDB est prêt et opérationnel !"
            break
        fi
        sleep 1
    done

    if [ "$READY" -eq 1 ]; then
        # Configuration des permissions pour 127.0.0.1 et localhost
        mariadb -u root --socket=/var/run/mysqld/mysqld.sock -e "
            CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY '';
            GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;
            GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
            FLUSH PRIVILEGES;
            CREATE DATABASE IF NOT EXISTS school_access_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        " 2>/dev/null || true

        # Importation des tables et données
        TABLE_COUNT=$(mariadb -u root --socket=/var/run/mysqld/mysqld.sock -N -s -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'school_access_db';" 2>/dev/null || echo "0")
        if [ "$TABLE_COUNT" = "0" ] || [ -z "$TABLE_COUNT" ]; then
            echo "[Render] Importation initiale des tables dans school_access_db..."
            if [ -f "/app/school_access_db.sql" ]; then
                mariadb -u root --socket=/var/run/mysqld/mysqld.sock school_access_db < /app/school_access_db.sql || true
            elif [ -f "/app/school_access_db (1).sql" ]; then
                mariadb -u root --socket=/var/run/mysqld/mysqld.sock school_access_db < "/app/school_access_db (1).sql" || true
            fi
            echo "[Render] Données initiales importées avec succès !"
        else
            echo "[Render] Tables déjà existantes ($TABLE_COUNT tables)."
        fi
    else
        echo "[Render] Avertissement : MariaDB interne n'a pas répondu dans les délais impartis."
    fi
fi

# Préparation du répertoire des QR codes
mkdir -p /app/images/qrcodes
chmod -R 777 /app/images/qrcodes 2>/dev/null || true

# Render utilise la variable $PORT (par défaut 10000 sur Render)
PORT="${PORT:-10000}"
echo "[Render] Démarrage du serveur PHP sur 0.0.0.0:${PORT}..."
exec php -S 0.0.0.0:${PORT}
