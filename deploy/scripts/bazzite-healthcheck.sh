#!/usr/bin/env bash
# ============================================================
#  Kina · Healthcheck del servidor Bazzite
#  ------------------------------------------------------------
#  Verifica que el entorno y la app estén listos. Solo lee, no cambia nada.
#
#  Uso:
#     cd /var/www/kina
#     ./deploy/scripts/bazzite-healthcheck.sh
#
#  Devuelve exit code != 0 si algo falla (útil en CI o cron).
# ============================================================
set -uo pipefail

APP_DIR="/var/www/kina"
cd "$APP_DIR" 2>/dev/null || { echo "!! No existe $APP_DIR"; exit 1; }

FAILS=0
ok()   { printf '  \033[32m✓\033[0m %s\n' "$1"; }
bad()  { printf '  \033[31m✗\033[0m %s\n' "$1"; FAILS=$((FAILS+1)); }

echo ">> Herramientas base"
for bin in php composer node npm git; do
  if command -v "$bin" >/dev/null 2>&1; then
    ok "$bin — $("$bin" --version 2>/dev/null | head -n1)"
  else
    bad "$bin no encontrado"
  fi
done

echo ">> Versión de PHP (Laravel 12 requiere 8.2+)"
if php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' 2>/dev/null; then
  ok "PHP $(php -r 'echo PHP_VERSION;')"
else
  bad "PHP demasiado antiguo"
fi

echo ">> APP_KEY"
if grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
  ok "APP_KEY definida"
else
  bad "APP_KEY vacía -> corre: php artisan key:generate"
fi

echo ">> Conexión a base de datos"
if php artisan db:show >/dev/null 2>&1; then
  ok "DB accesible ($(php artisan db:show --json 2>/dev/null | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo ($j["platform"]["name"]??"?")." / ".($j["platform"]["config"]["database"]??"?");'))"
else
  bad "No pude conectar a la DB (revisa DB_* en .env y que MariaDB esté arriba)"
fi

echo ">> Enlace de storage"
if [[ -L public/storage ]]; then
  ok "public/storage -> $(readlink public/storage)"
else
  bad "Falta storage link -> corre: php artisan storage:link"
fi

echo ">> Permisos de escritura"
for d in storage storage/logs storage/framework bootstrap/cache; do
  if [[ -w "$d" ]]; then ok "escribible: $d"; else bad "NO escribible: $d"; fi
done

echo ">> La app responde localmente (HTTP)"
CODE=$(curl -s -o /dev/null -w '%{http_code}' -H 'Host: kina.local' http://127.0.0.1/ 2>/dev/null || echo 000)
if [[ "$CODE" =~ ^(200|302)$ ]]; then
  ok "Nginx responde (HTTP $CODE)"
else
  bad "Sin respuesta HTTP esperada (código $CODE). ¿Nginx/PHP-FPM arriba? ¿server_name correcto?"
fi

echo
if [[ "$FAILS" -eq 0 ]]; then
  echo -e "\033[32m>> Todo en orden. Servidor listo.\033[0m"
  exit 0
else
  echo -e "\033[31m>> $FAILS verificación(es) fallaron. Revisa arriba.\033[0m"
  exit 1
fi
