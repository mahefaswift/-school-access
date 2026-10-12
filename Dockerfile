FROM php:8.2-cli

# Installer libpng et l'extension PHP GD pour la génération des QR codes
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    && docker-php-ext-install gd mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app/

# Préparation du répertoire pour les QR codes générés
RUN mkdir -p /app/images/qrcodes && chmod -R 777 /app/images/qrcodes

# Render injecte $PORT dynamiquement (par défaut 10000)
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000}"]
