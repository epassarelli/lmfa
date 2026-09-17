#!/bin/bash
# Refresco post-deploy para Hostinger.
#
# Uso, por SSH y desde la raiz del proyecto (donde esta "artisan"):
#
#   git pull && bash scripts/post-deploy.sh
#
# El "git pull" queda deliberadamente afuera: este script esta versionado en el
# repo, y bash lee los scripts por tramos mientras los ejecuta. Si un pull
# reescribiera este archivo a mitad de la corrida, bash seguiria leyendo desde
# un offset que ya no corresponde al contenido nuevo.
#
# El orden importa: primero el autoload, despues las caches. Generar las caches
# arranca la aplicacion Laravel completa, asi que el mapa de clases tiene que
# estar al dia antes; si el pull agrego, renombro o borro clases, cachear con el
# mapa viejo puede fallar o congelar un estado roto.
#
# En hosts con varias versiones de PHP se puede forzar el binario:
#
#   PHP_BIN=/usr/bin/php82 bash scripts/post-deploy.sh

set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
# bootstrap/cache/ ya esta ignorado por git entero, asi que el marcador no
# ensucia el working tree del servidor ni traba futuros "git pull".
LOCK_MARKER="bootstrap/cache/.composer-lock-deployed"

if [ ! -f artisan ]; then
    echo "Error: ejecutar desde la raiz del proyecto, donde esta 'artisan'." >&2
    exit 1
fi

# hash_file via PHP en vez de sha256sum: PHP siempre esta disponible aca.
current_lock_hash() {
    "$PHP_BIN" -r 'echo @hash_file("sha256", "composer.lock") ?: "";'
}

echo "==> Autoload"
CURRENT_LOCK="$(current_lock_hash)"
PREVIOUS_LOCK="$(cat "$LOCK_MARKER" 2>/dev/null || true)"

if [ -n "$CURRENT_LOCK" ] && [ "$CURRENT_LOCK" != "$PREVIOUS_LOCK" ]; then
    echo "    composer.lock cambio: instalando dependencias"
    "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction
else
    echo "    composer.lock sin cambios: solo regenero el mapa de clases"
    "$COMPOSER_BIN" dump-autoload -o --no-interaction
fi

if [ -n "$CURRENT_LOCK" ]; then
    printf '%s' "$CURRENT_LOCK" > "$LOCK_MARKER"
fi

echo "==> Limpiando caches viejas"
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear
"$PHP_BIN" artisan cache:clear

echo "==> Regenerando caches"
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

echo "==> Descartando respuestas HTTP cacheadas"
"$PHP_BIN" artisan responsecache:clear

echo
echo "Listo."
echo
echo "AVISO: la configuracion quedo cacheada. A partir de ahora los cambios en"
echo ".env NO tienen efecto hasta volver a correr este script (o al menos"
echo "'$PHP_BIN artisan config:cache'). Si estas depurando algo via .env,"
echo "corre '$PHP_BIN artisan config:clear' y dejalo sin cachear hasta terminar."
