# Kina

**Conecta a tu propio ritmo.**

Kina es una plataforma para mayores de 18 años donde personas neurodivergentes, introvertidas
o con estilos de comunicación distintos encuentran amistad, pareja, algo casual o comunidad
según sus intereses, su intención y cómo prefieren comunicarse. Sin swipes, sin prisas.

## Stack

- **Laravel 12** (PHP 8.2+) · Blade · **Tailwind CSS** · Alpine.js · Laravel Breeze
- **MySQL / MariaDB** · Eloquent · Migrations · Seeders · Form Requests · Middleware
- Chat básico **sin WebSockets** (polling controlado)
- Pruebas con **PHPUnit** (67+ tests)

## Funcionalidad (MVP)

Landing · Registro/Login · Onboarding en 7 pasos (con confirmación 18+ y consentimientos) ·
Perfiles con intereses, preferencias de comunicación y etiquetas sensibles opcionales ·
Descubrimiento con **Áreas de Sintonía** · Solicitudes de conexión · Chat entre conexiones ·
Bloqueos · Reportes · Panel de administración y moderación.

---

## Desarrollo local (Windows / dev)

Requisitos: PHP 8.2+, Composer, Node 18+, MySQL/MariaDB.

```bash
# Dependencias
composer install
npm install

# Entorno
cp .env.example .env        # (Windows: copy .env.example .env)
php artisan key:generate
# Ajusta DB_* en .env y crea la base 'kina'

# Base de datos + datos base
php artisan migrate --seed

# Assets + servidor
npm run dev                 # en una terminal (hot reload)
php artisan serve           # en otra -> http://127.0.0.1:8000
```

Usuarios de prueba sembrados: `admin@kina.local` y `test@kina.local` (contraseña `password`).
**Cámbialos antes de exponer el servidor.**

### Tests

```bash
php artisan test            # suite completa (SQLite en memoria, no toca tu DB)
```

---

## Deploy local en Bazzite (staging provisional)

Kina puede correr en un servidor casero **Bazzite Linux** (Fedora Atomic) para staging/LAN.
La guía completa paso a paso está en **[`docs/deploy-bazzite.md`](docs/deploy-bazzite.md)**.

Resumen:

```bash
# [BAZZITE]  1) Dependencias (rpm-ostree; reinicia una vez)
./deploy/scripts/bazzite-install-deps.sh && sudo systemctl reboot
sudo systemctl enable --now mariadb php-fpm nginx firewalld

# 2) Base de datos con usuario dedicado (NO root) — ver guía
#    CREATE DATABASE kina; CREATE USER 'kina_user'@'localhost' ...

# 3) Proyecto en /var/www/kina
git clone https://github.com/TU_USUARIO/kina.git /var/www/kina && cd /var/www/kina
cp .env.bazzite.example .env && php artisan key:generate   # edita DB_* y APP_URL

# 4) Despliegue completo (dependencias, build, migrate, caches, permisos)
./deploy/scripts/bazzite-deploy.sh

# 5) Nginx + firewall LAN (ver guía) y verificación
sudo cp deploy/nginx/kina.local.conf /etc/nginx/conf.d/ && sudo nginx -t && sudo systemctl reload nginx
./deploy/scripts/bazzite-healthcheck.sh
```

Artefactos de deploy en [`deploy/`](deploy/):
`nginx/kina.local.conf`, `systemd/kina-queue.service`, `scripts/bazzite-{install-deps,deploy,healthcheck}.sh`.
Plantilla de entorno: [`.env.bazzite.example`](.env.bazzite.example).

> 🔒 **Seguridad — léelo antes de exponer nada:**
>
> - **No** abras el puerto 80/443 del router directo, ni el **3306** de la base de datos.
> - Acceso externo **solo** vía **Tailscale** o **Cloudflare Tunnel** (con HTTPS).
> - `APP_DEBUG=false`, DB con `kina_user` (no root), firewall activo, sin `chmod 777`.
> - **Nunca** subas tu `.env` real a git ni corras `migrate:fresh` en el servidor.
> - Revisa el **“Checklist antes de encender acceso remoto”** en la guía.

---

## Estructura relevante

```text
app/Http/Controllers      # incl. Admin/ y flujo de onboarding/chat/descubrir
app/Services              # CompatibilityService, ProfileVisibilityService, ChatAccessService
app/Http/Requests         # validación (Form Requests)
database/migrations       # esquema Kina
deploy/                   # Nginx, systemd y scripts de Bazzite
docs/deploy-bazzite.md    # guía de despliegue
tests/Feature             # cobertura de onboarding, descubrimiento, chat y admin
```
