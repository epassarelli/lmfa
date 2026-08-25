#!/bin/bash
# Correr esto por SSH en Hostinger despues de cada "git pull", desde la raiz
# del proyecto (donde esta "artisan"):
#
#   bash scripts/post-deploy.sh
#
# Regenera los caches de Laravel para que el sitio use la config, las rutas
# y las vistas compiladas mas rapido, sin recalcular nada en cada visita.
# Es exactamente lo que faltaba: hoy nada corre config:cache/route:cache/
# view:cache en el servidor, asi que Laravel repite ese trabajo en cada
# request.
set -e

echo "==> Limpiando caches viejos..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> Regenerando caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Listo. Si instalaste dependencias nuevas de Composer, correr antes:"
echo "    composer install --no-dev --optimize-autoloader"
