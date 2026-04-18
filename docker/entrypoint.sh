#!/bin/bash
set -e

# UID del usuario de despliegue en el host (rsync/SSH); grupo www-data para PHP-FPM.
# Ajustar LARAVEL_DEPLOY_UID en docker-compose si el usuario del host no es 1000.
DEPLOY_UID="${LARAVEL_DEPLOY_UID:-1000}"

chown -R "${DEPLOY_UID}":www-data /var/www/html/storage /var/www/html/bootstrap/cache
find /var/www/html/storage /var/www/html/bootstrap/cache -type d -exec chmod 2775 {} \;
find /var/www/html/storage /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \;

# Ejecutar el comando original o php-fpm por defecto
if [ $# -eq 0 ]; then
    exec php-fpm
else
    exec "$@"
fi
