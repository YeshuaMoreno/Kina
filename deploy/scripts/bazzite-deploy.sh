#!/usr/bin/env bash
# ============================================================
#  Kina · Deploy / actualización en el servidor Bazzite
#  ------------------------------------------------------------
#  Ejecutar DENTRO de /var/www/kina como el usuario dueño del proyecto
#  (NO como root; solo se usa sudo para recargar servicios y permisos).
#
#  Uso:
#     cd /var/www/kina
#     ./deploy/scripts/bazzite-deploy.sh
#
#  Hace: git pull, dependencias, build, migrate (SIN fresh), caches y permisos.
#  NUNCA ejecuta migrate:fresh ni borra datos.
# ============================================================
set -euo pipefail

APP_DIR="/var/www/kina"
WEB_GROUP="nginx"   # grupo del servidor web en Fedora/Bazzite

cd "$APP_DIR"

# Seguridad mínima: confirma que estamos en un proyecto Laravel.
if [[ ! -f artisan ]]; then
  echo "!! No encuentro 'artisan' en $APP_DIR. ¿Ruta correcta?" >&2
  exit 1
fi

echo ">> 1/9 Trayendo cambios (git pull)…"
git pull --ff-only

echo ">> 2/9 Dependencias PHP (sin dev, autoloader optimizado)…"
composer install --no-dev --optimize-autoloader --no-interaction

echo ">> 3/9 Dependencias JS (npm ci)…"
npm ci

echo ">> 4/9 Compilando assets (npm run build)…"
npm run build

echo ">> 5/9 Migraciones (--force, NUNCA fresh)…"
php artisan migrate --force

echo ">> 6/9 Enlace de storage…"
php artisan storage:link || true   # ya existe -> no es error

echo ">> 7/9 Cacheando config, rutas y vistas…"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">> 8/9 Permisos de storage y bootstrap/cache (sin 777)…"
# Dueño: tu usuario; grupo: el del servidor web, con escritura de grupo.
sudo chown -R "$USER":"$WEB_GROUP" storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 2775 {} \;   # 2 = setgid (hereda grupo)
find storage bootstrap/cache -type f -exec chmod 0664 {} \;
# Reaplica contexto SELinux de escritura (definido una vez en la guía).
if command -v restorecon >/dev/null 2>&1; then
  sudo restorecon -R storage bootstrap/cache || true
fi

echo ">> 9/9 Recargando servicios (si están presentes)…"
sudo systemctl reload php-fpm 2>/dev/null || sudo systemctl restart php-fpm 2>/dev/null || true
sudo systemctl reload nginx  2>/dev/null || true
# Si usas el worker de colas por systemd, avísale que recargue el código:
php artisan queue:restart >/dev/null 2>&1 || true

echo
echo ">> Deploy completado. Verifica con:"
echo "     ./deploy/scripts/bazzite-healthcheck.sh"
