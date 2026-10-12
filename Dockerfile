FROM php:8.2-cli

# Installer MariaDB et ses dépendances
RUN apt-get update && apt-get install -y \
    mariadb-server \
    mariadb-client \
    && rm -rf /var/lib/apt/lists/*

# Installer mysqli et PDO MySQL pour PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /app
COPY . /app/

RUN chmod +x /app/entrypoint.sh

# Point d'entrée pour Render / Cloud
CMD ["/app/entrypoint.sh"]
