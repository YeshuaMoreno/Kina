#!/usr/bin/env bash
# ============================================================
#  Kina · Instalación de dependencias en Bazzite / Fedora Atomic
#  ------------------------------------------------------------
#  Instala PHP-FPM, MariaDB, Nginx, Node, Composer y utilidades.
#  - NO borra nada.
#  - Idempotente en lo posible (rpm-ostree --idempotent / dnf reinstalable).
#  - Bazzite es INMUTABLE: se usa rpm-ostree (requiere UN reboot al final).
#
#  Uso:
#     chmod +x deploy/scripts/bazzite-install-deps.sh
#     ./deploy/scripts/bazzite-install-deps.sh
#
#  Requiere sudo para instalar paquetes (NO ejecutes todo como root).
# ============================================================
set -euo pipefail

# Paquetes necesarios (nombres Fedora). PHP 8.3 en Fedora 40+ = compatible con Laravel 12.
PKGS=(
  nginx
  php-fpm php-cli php-common
  php-mysqlnd php-mbstring php-xml php-gd php-zip php-bcmath php-opcache php-intl
  mariadb-server
  nodejs npm
  git
  composer
  firewalld
  policycoreutils-python-utils   # provee 'semanage' para contextos SELinux
)

echo ">> Kina · instalación de dependencias"
echo ">> Paquetes: ${PKGS[*]}"
echo

if command -v rpm-ostree >/dev/null 2>&1; then
  # ---- Camino Bazzite / Fedora Atomic (inmutable) ----
  echo ">> Detectado sistema inmutable (rpm-ostree). Caparé los paquetes."
  # --idempotent: no falla si ya estaban solicitados.
  # --apply-live: intenta aplicarlos sin reboot (puede no aplicar a todo).
  sudo rpm-ostree install --idempotent --apply-live "${PKGS[@]}" || \
    sudo rpm-ostree install --idempotent "${PKGS[@]}"
  echo
  echo ">> LISTO (capa creada). Si algún binario no aparece aún, REINICIA una vez:"
  echo "     sudo systemctl reboot"
elif command -v dnf >/dev/null 2>&1; then
  # ---- Camino Fedora mutable / VPS Fedora (fallback) ----
  echo ">> Detectado dnf (sistema mutable)."
  sudo dnf install -y "${PKGS[@]}"
else
  echo "!! No encontré rpm-ostree ni dnf. ¿Es realmente Fedora/Bazzite?" >&2
  exit 1
fi

echo
echo ">> Versiones disponibles (si acabas de capar en Bazzite, revísalas tras el reboot):"
for bin in php composer node npm git nginx mariadbd; do
  if command -v "$bin" >/dev/null 2>&1; then
    printf '   %-10s %s\n' "$bin" "$("$bin" --version 2>/dev/null | head -n1)"
  else
    printf '   %-10s (pendiente — aparece tras reboot en Bazzite)\n' "$bin"
  fi
done

echo
echo ">> Siguiente paso: habilitar servicios (tras el reboot si aplica):"
echo "     sudo systemctl enable --now mariadb php-fpm nginx firewalld"
echo "   Luego continúa con docs/deploy-bazzite.md (DB, .env, deploy)."
