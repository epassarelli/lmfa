#!/bin/sh
# Entrypoint de produccion/contenedor.
# Cachea config/rutas/vistas de Laravel en cada arranque del contenedor,
# despues de que el .env real (o las variables de entorno reales) ya esten
# presentes -- por eso esto corre aca y no en el build del Dockerfile,
# donde solo existe el .env.example de placeholder.
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
