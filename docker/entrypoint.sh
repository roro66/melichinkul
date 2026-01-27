#!/bin/bash
set -e

# Configurar permisos de storage y cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ejecutar el comando original o php-fpm por defecto
if [ $# -eq 0 ]; then
    exec php-fpm
else
    exec "$@"
fi
