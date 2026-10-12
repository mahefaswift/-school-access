FROM php:8.2-cli

# Installer les extensions PHP requises
RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /app
COPY . /app/

# Préparation du répertoire pour les QR codes générés
RUN mkdir -p /app/images/qrcodes && chmod -R 777 /app/images/qrcodes

# Render injecte $PORT dynamiquement (par défaut 10000)
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000}"]
