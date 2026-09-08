FROM php:8.2-cli

# Installer mysqli et PDO
RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /app
COPY . /app/

# Railway injecte $PORT dynamiquement
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080}"]
