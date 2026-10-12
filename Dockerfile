FROM php:8.2-cli

ENV DEBIAN_FRONTEND=noninteractive

# Installer MariaDB et les dépendances nécessaires sans bloquer debconf
RUN apt-get update && apt-get install -y --no-install-recommends \
    mariadb-server \
    mariadb-client \
    procps \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP MySQLi et PDO
RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /app
COPY . /app/

RUN chmod +x /app/entrypoint.sh

# Point d'entrée pour Render / Cloud
CMD ["/app/entrypoint.sh"]
