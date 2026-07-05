#!/usr/bin/env bash
# ============================================================
#  Kina · Backup de base de datos + storage
#  ------------------------------------------------------------
#  Genera un .tar.gz con:
#    - dump de la base de datos (mariadb-dump / mysqldump)
#    - storage/app/public (fotos de perfil subidas)
#    - (opcional) copia del .env  -> SOLO con --with-env (es SENSIBLE)
#
#  Ejecutar DESDE la raíz del proyecto (donde está 'artisan' y '.env').
#      ./deploy/scripts/backup-kina.sh            # DB + storage
#      ./deploy/scripts/backup-kina.sh --with-env # + .env (sensible)
#
#  - NO borra backups anteriores.
#  - NO incluye vendor/ ni node_modules/.
#  - NO imprime contraseñas (usa un my.cnf temporal con permisos 600).
#  - Lee credenciales desde .env.
# ============================================================
set -euo pipefail

WITH_ENV="no"
[[ "${1:-}" == "--with-env" ]] && WITH_ENV="yes"

# Debe correrse en la raíz del proyecto.
if [[ ! -f artisan || ! -f .env ]]; then
  echo "!! Ejecuta este script desde la raíz del proyecto (falta artisan/.env)." >&2
  exit 1
fi

# Lee una variable del .env sin exponerla (quita comillas envolventes).
env_get() {
  local key="$1"
  local val
  val="$(grep -E "^${key}=" .env | head -n1 | cut -d= -f2-)"
  val="${val%\"}"; val="${val#\"}"
  val="${val%\'}"; val="${val#\'}"
  printf '%s' "$val"
}

DB_DATABASE="$(env_get DB_DATABASE)"
DB_USERNAME="$(env_get DB_USERNAME)"
DB_PASSWORD="$(env_get DB_PASSWORD)"
DB_HOST="$(env_get DB_HOST)"; DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="$(env_get DB_PORT)"; DB_PORT="${DB_PORT:-3306}"

if [[ -z "$DB_DATABASE" || -z "$DB_USERNAME" ]]; then
  echo "!! No pude leer DB_DATABASE/DB_USERNAME del .env." >&2
  exit 1
fi

# Elige la herramienta de dump disponible.
if command -v mariadb-dump >/dev/null 2>&1; then DUMP=mariadb-dump
elif command -v mysqldump >/dev/null 2>&1; then DUMP=mysqldump
else echo "!! No encontré mariadb-dump ni mysqldump." >&2; exit 1; fi

STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="backups/kina-$STAMP"
ARCHIVE="backups/kina-backup-$STAMP.tar.gz"
mkdir -p "$WORK"

# my.cnf temporal para NO pasar la contraseña por la línea de comandos.
CNF="$(mktemp)"
chmod 600 "$CNF"
cat > "$CNF" <<EOF
[client]
user=${DB_USERNAME}
password=${DB_PASSWORD}
host=${DB_HOST}
port=${DB_PORT}
EOF
# Asegura limpieza del my.cnf pase lo que pase.
trap 'rm -f "$CNF"' EXIT

echo ">> Backup de base de datos '${DB_DATABASE}'…"
"$DUMP" --defaults-extra-file="$CNF" \
  --single-transaction --quick --routines --triggers \
  "$DB_DATABASE" > "$WORK/database.sql"

echo ">> Backup de storage/app/public…"
if [[ -d storage/app/public ]]; then
  tar -czf "$WORK/storage-public.tar.gz" -C storage/app public
else
  echo "   (no existe storage/app/public; se omite)"
fi

if [[ "$WITH_ENV" == "yes" ]]; then
  echo ">> Incluyendo .env (SENSIBLE: guarda este backup en lugar seguro)…"
  cp .env "$WORK/env.backup"
fi

echo ">> Empaquetando…"
tar -czf "$ARCHIVE" -C backups "kina-$STAMP"
rm -rf "$WORK"

SIZE="$(du -h "$ARCHIVE" | cut -f1)"
echo
echo ">> Backup listo: $ARCHIVE ($SIZE)"
echo "   Contiene: database.sql + storage-public.tar.gz$([[ "$WITH_ENV" == "yes" ]] && echo ' + env.backup')"
echo "   Cópialo FUERA de esta máquina (ver docs/backups.md)."
