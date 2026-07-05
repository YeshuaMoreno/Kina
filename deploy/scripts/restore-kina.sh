#!/usr/bin/env bash
# ============================================================
#  Kina · Restore de base de datos + storage
#  ------------------------------------------------------------
#  Restaura un backup creado por backup-kina.sh.
#
#  Ejecutar DESDE la raíz del proyecto:
#      ./deploy/scripts/restore-kina.sh backups/kina-backup-YYYYmmdd-HHMMSS.tar.gz
#
#  ⚠️  SOBREESCRIBE la base de datos y storage/app/public actuales.
#  - Pide confirmación explícita.
#  - Valida que el archivo exista.
#  - NUNCA ejecuta migrate:fresh ni borra la base entera manualmente.
# ============================================================
set -euo pipefail

ARCHIVE="${1:-}"

if [[ -z "$ARCHIVE" ]]; then
  echo "Uso: $0 <ruta-al-backup.tar.gz>" >&2
  exit 1
fi
if [[ ! -f "$ARCHIVE" ]]; then
  echo "!! No existe el archivo: $ARCHIVE" >&2
  exit 1
fi
if [[ ! -f artisan || ! -f .env ]]; then
  echo "!! Ejecuta este script desde la raíz del proyecto (falta artisan/.env)." >&2
  exit 1
fi

env_get() {
  local key="$1" val
  val="$(grep -E "^${key}=" .env | head -n1 | cut -d= -f2-)"
  val="${val%\"}"; val="${val#\"}"; val="${val%\'}"; val="${val#\'}"
  printf '%s' "$val"
}
DB_DATABASE="$(env_get DB_DATABASE)"
DB_USERNAME="$(env_get DB_USERNAME)"
DB_PASSWORD="$(env_get DB_PASSWORD)"
DB_HOST="$(env_get DB_HOST)"; DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="$(env_get DB_PORT)"; DB_PORT="${DB_PORT:-3306}"

if command -v mariadb >/dev/null 2>&1; then MYSQL=mariadb
elif command -v mysql >/dev/null 2>&1; then MYSQL=mysql
else echo "!! No encontré el cliente mariadb/mysql." >&2; exit 1; fi

echo "Vas a restaurar sobre la base '${DB_DATABASE}' y storage/app/public."
echo "Esto SOBREESCRIBE los datos actuales. No hay deshacer."
read -r -p "Escribe 'restaurar' para continuar: " CONFIRM
if [[ "$CONFIRM" != "restaurar" ]]; then
  echo ">> Cancelado. No se cambió nada."
  exit 0
fi

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
echo ">> Extrayendo backup…"
tar -xzf "$ARCHIVE" -C "$TMP"
INNER="$(find "$TMP" -maxdepth 1 -type d -name 'kina-*' | head -n1)"
[[ -z "$INNER" ]] && { echo "!! El backup no tiene la estructura esperada." >&2; exit 1; }

CNF="$(mktemp)"; chmod 600 "$CNF"
cat > "$CNF" <<EOF
[client]
user=${DB_USERNAME}
password=${DB_PASSWORD}
host=${DB_HOST}
port=${DB_PORT}
EOF
trap 'rm -f "$CNF"; rm -rf "$TMP"' EXIT

if [[ -f "$INNER/database.sql" ]]; then
  echo ">> Restaurando base de datos…"
  "$MYSQL" --defaults-extra-file="$CNF" "$DB_DATABASE" < "$INNER/database.sql"
else
  echo "!! El backup no contiene database.sql." >&2
fi

if [[ -f "$INNER/storage-public.tar.gz" ]]; then
  echo ">> Restaurando storage/app/public…"
  mkdir -p storage/app
  tar -xzf "$INNER/storage-public.tar.gz" -C storage/app
else
  echo "   (el backup no incluye storage-public.tar.gz; se omite)"
fi

echo
echo ">> Restore completado."
echo "   Recomendado: php artisan optimize:clear && php artisan storage:link"
