# Migración de Kina a VPS / Cloud (producción)

Cuando el staging en Bazzite ya no baste, mueve Kina a un VPS (Hetzner, DigitalOcean,
Vultr, Linode, etc.). El **código no cambia**: cambian infraestructura, credenciales y HTTPS.

## Staging (Bazzite) vs Producción (VPS) — diferencias clave

| Tema | Bazzite (staging) | VPS (producción) |
|---|---|---|
| Sistema | Inmutable (rpm-ostree) | Mutable (`dnf`/`apt` normal) |
| Acceso | LAN / Tailscale | Dominio público con **HTTPS** |
| `.env` | `APP_ENV=staging`, `APP_DEBUG=false` | `APP_ENV=production`, `APP_DEBUG=false` |
| Cookies | `SESSION_SECURE_COOKIE=false` | `SESSION_SECURE_COOKIE=true` |
| Firewall | firewalld en LAN | Security Groups + firewall local |
| Backups | Locales + copia manual | Automáticos y **externos** (S3/Spaces) |
| TLS | Opcional (Tailscale) | Obligatorio (Certbot / Cloudflare) |

## Variables `.env` de producción (lo mínimo a cambiar)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com
SESSION_SECURE_COOKIE=true
LOG_LEVEL=error

DB_HOST=127.0.0.1
DB_DATABASE=kina
DB_USERNAME=kina_user          # NUNCA root
DB_PASSWORD=<fuerte-y-unica>

# Considera un mailer real (no 'log') si envías correos de verdad.
MAIL_MAILER=smtp
```

---

## Pasos de migración

### 1. Preparar el VPS
```bash
# [VPS]  (Ubuntu/Debian ejemplo)
sudo apt update && sudo apt install -y \
  nginx php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-gd php8.3-zip php8.3-bcmath php8.3-intl \
  mariadb-server git unzip curl
# Composer y Node (NodeSource) según su documentación oficial.
sudo systemctl enable --now nginx php8.3-fpm mariadb
```

### 2. Clonar el repo
```bash
# [VPS]
sudo mkdir -p /var/www/kina && sudo chown "$USER":www-data /var/www/kina
git clone https://github.com/TU_USUARIO/kina.git /var/www/kina
cd /var/www/kina
```
> En Ubuntu el grupo del web server es `www-data` (no `nginx`). Ajusta scripts/nginx en consecuencia.

### 3. Configurar `.env`
```bash
# [VPS]
cp .env.example .env
nano .env                      # usa los valores de producción de arriba
php artisan key:generate
```
Crea la DB y el usuario `kina_user` (igual que en la guía de Bazzite, sección 3).

### 4. Restaurar backup (si migras datos existentes)
```bash
# [VPS]  sube el backup y restaura
./deploy/scripts/restore-kina.sh backups/kina-backup-XXXX.tar.gz
```
Si empiezas de cero, sáltate esto y usa `migrate --seed` en el paso 6.

### 5. Dependencias y build
```bash
# [VPS]
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### 6. Migraciones y storage
```bash
# [VPS]
php artisan migrate --force        # NUNCA migrate:fresh en producción
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### 7. Nginx + HTTPS
```bash
# [VPS]  usa deploy/nginx/kina.local.conf como base; cambia server_name a tu dominio
sudo cp deploy/nginx/kina.local.conf /etc/nginx/sites-available/kina.conf
# ajusta fastcgi_pass al socket de Ubuntu: unix:/run/php/php8.3-fpm.sock
sudo ln -s /etc/nginx/sites-available/kina.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# HTTPS con Let's Encrypt:
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tudominio.com
# (Alternativa: poner Cloudflare delante con SSL Full.)
```

### 8. Firewall / Security Groups
```bash
# [VPS]  abre solo 80/443 y SSH; NUNCA el 3306
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```
En el panel del proveedor, el Security Group debe permitir solo 22, 80, 443.
La base de datos escucha en `127.0.0.1` (no la expongas).

### 9. Healthcheck
```bash
# [VPS]  ajusta APP_DIR/rutas si difieren; verifica DB, storage, permisos y HTTP
./deploy/scripts/bazzite-healthcheck.sh
curl -I https://tudominio.com
```

### 10. Cambiar DNS
- Apunta el registro `A` (y `AAAA` si hay IPv6) del dominio a la IP del VPS.
- Espera propagación, verifica HTTPS y vuelve a correr el healthcheck.

---

## Después de migrar (producción sólida)

- **Backups externos automáticos**: `backup-kina.sh` + subida a S3/Spaces (cron diario).
- **Queue worker** por systemd si pasas a `QUEUE_CONNECTION=database`
  (usa `deploy/systemd/kina-queue.service`, ajustando `User=`/`Group=www-data`).
- **Logs**: revisa `storage/logs/laravel.log` y los de Nginx; considera rotación (`logrotate`).
- **Monitoreo básico**: un uptime check externo (UptimeRobot/HealthChecks) apuntando a `/up`
  (Laravel ya expone esa ruta de salud) y alertas por email.
- **Actualizaciones**: `bazzite-deploy.sh` sirve igual en el VPS (ajusta `WEB_GROUP=www-data`).

## Checklist de corte final

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`
- [ ] HTTPS válido (candado verde) y redirección 80→443
- [ ] DB con `kina_user`, contraseña fuerte, puerto 3306 cerrado al exterior
- [ ] Firewall/Security Group solo 22/80/443
- [ ] Admin y usuarios de prueba con credenciales cambiadas o eliminados
- [ ] Backups externos automáticos verificados (probaste un restore)
- [ ] `php artisan test` y `npm run build` verdes antes de desplegar
- [ ] Caches de config/rutas/vistas activas
