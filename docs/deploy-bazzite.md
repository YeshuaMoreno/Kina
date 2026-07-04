# Deploy local de Kina en Bazzite (staging provisional)

Guía práctica para correr **Kina** (Laravel 12 + Blade + Tailwind + MariaDB) en tu
**Lenovo IdeaPad 330 con Bazzite Linux**, como servidor de **staging/LAN**, no producción final.

> ⚠️ **Bazzite es un sistema inmutable (Fedora Atomic / rpm-ostree).** No se instala con `dnf`
> sobre el host: se **capan** paquetes con `rpm-ostree install` y se reinicia **una vez**.
> Esta guía usa esa vía. Todo lo demás (Nginx, PHP-FPM, systemd, firewalld) funciona nativo.

> 🔒 **No expongas esto a internet a lo bruto.** Para acceso externo usa **Tailscale** o
> **Cloudflare Tunnel** (sección 9). Nunca abras el puerto 3306 ni el 80 a internet sin HTTPS + firewall.

Los comandos están separados por dónde se ejecutan:

- `# [DEV/Windows]` → tu máquina de desarrollo.
- `# [BAZZITE]` → el servidor Lenovo.
- `# [VPS futuro]` → notas para cuando migres a cloud (Fase 8).

---

## 0. Antes de nada, en tu máquina de desarrollo

```powershell
# [DEV/Windows]  Asegura que todo pasa ANTES de desplegar
php artisan test
npm run build

# Sube el proyecto a GitHub (el servidor lo clonará desde ahí)
git push origin main
```

El servidor **no** copia tu `.env` ni tu base local: se configuran aparte en Bazzite.

---

## 1. Instalar dependencias en Bazzite

```bash
# [BAZZITE]  Clona temporalmente para tener los scripts (o cópialos por USB/scp)
git clone https://github.com/TU_USUARIO/kina.git ~/kina-src
cd ~/kina-src

# Instala PHP-FPM, MariaDB, Nginx, Node, Composer, firewalld, semanage…
chmod +x deploy/scripts/*.sh
./deploy/scripts/bazzite-install-deps.sh

# Bazzite es inmutable: si el script capó paquetes nuevos, REINICIA una vez
sudo systemctl reboot
```

Tras el reboot, habilita los servicios:

```bash
# [BAZZITE]
sudo systemctl enable --now mariadb php-fpm nginx firewalld
```

Verifica versiones (PHP debe ser 8.2+):

```bash
# [BAZZITE]
php --version && composer --version && node --version && nginx -v
```

---

## 2. Ajustar PHP-FPM al usuario del servidor web

En Fedora, PHP-FPM corre como `apache` y Nginx como `nginx`. Los alineamos a `nginx`:

```bash
# [BAZZITE]
sudo sed -i 's/^user = apache/user = nginx/'  /etc/php-fpm.d/www.conf
sudo sed -i 's/^group = apache/group = nginx/' /etc/php-fpm.d/www.conf

# Asegura el socket y que Nginx pueda usarlo
sudo sed -i 's|^listen = .*|listen = /run/php-fpm/www.sock|'   /etc/php-fpm.d/www.conf
sudo sed -i 's/^;listen.owner = .*/listen.owner = nginx/'      /etc/php-fpm.d/www.conf
sudo sed -i 's/^;listen.group = .*/listen.group = nginx/'      /etc/php-fpm.d/www.conf

sudo systemctl restart php-fpm
# Comprueba el socket real:
sudo ss -lx | grep php
```

---

## 3. Crear la base de datos y el usuario (NO root)

```bash
# [BAZZITE]  Asegura la instalación de MariaDB (pon password de root, quita anónimos…)
sudo mariadb-secure-installation
```

```bash
# [BAZZITE]  Entra como root de MariaDB y crea la DB + usuario DEDICADO
sudo mariadb
```

```sql
-- Dentro de MariaDB. Cambia la contraseña por una fuerte y única.
CREATE DATABASE kina CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kina_user'@'localhost' IDENTIFIED BY 'PON_UNA_PASSWORD_FUERTE';
GRANT ALL PRIVILEGES ON kina.* TO 'kina_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> `kina_user` solo puede entrar desde `localhost`. **No** uses `root`. **No** abras el 3306.
> Verifica que MariaDB escucha solo local:
> ```bash
> # [BAZZITE]
> sudo ss -ltnp | grep 3306   # debe mostrar 127.0.0.1:3306, NO 0.0.0.0:3306
> ```

---

## 4. Colocar el proyecto en /var/www/kina

```bash
# [BAZZITE]  Carpeta del proyecto, propiedad de TU usuario (no root) y grupo nginx
sudo mkdir -p /var/www/kina
sudo chown "$USER":nginx /var/www/kina

# Clona el repo dentro (composer se corre como TU usuario, no root)
git clone https://github.com/TU_USUARIO/kina.git /var/www/kina
cd /var/www/kina
```

---

## 5. Configurar el `.env`

```bash
# [BAZZITE]
cp .env.bazzite.example .env
nano .env    # ajusta APP_URL (tu IP LAN) y DB_PASSWORD (la de kina_user)

# Genera la APP_KEY EN EL SERVIDOR
php artisan key:generate
```

Puntos clave del `.env` en staging:

- `APP_ENV=staging`, `APP_DEBUG=false`
- `APP_URL=http://TU_IP_LAN`
- `DB_USERNAME=kina_user`, `DB_PASSWORD=…` (la que creaste)
- `SESSION_SECURE_COOKIE=false` (LAN sin HTTPS). Ponlo `true` si sirves por HTTPS.

---

## 6. Instalar dependencias del proyecto

```bash
# [BAZZITE]
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

---

## 7. Migraciones, seeders y storage link

```bash
# [BAZZITE]  Crea tablas y datos base (intereses, etiquetas, admin de prueba)
php artisan migrate --seed --force

# Enlaza storage para las fotos de perfil
php artisan storage:link
```

> ⚠️ **Nunca** corras `php artisan migrate:fresh` en el servidor: borra datos.
> `--seed` incluye un admin de prueba (`admin@kina.local`). **Cámbialo** antes de exponer (sección 11).

---

## 8. Permisos y SELinux (el paso que casi todos olvidan)

Fedora/Bazzite trae **SELinux en enforcing**. Sin contextos correctos, Nginx dará 403/500.

```bash
# [BAZZITE]  Contextos SELinux (se define UNA vez; luego basta 'restorecon')
sudo semanage fcontext -a -t httpd_sys_content_t    "/var/www/kina(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/kina/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/kina/bootstrap/cache(/.*)?"
sudo restorecon -Rv /var/www/kina

# Permisos (sin 777): dueño tu usuario, grupo nginx con escritura en lo necesario
sudo chown -R "$USER":nginx /var/www/kina
find storage bootstrap/cache -type d -exec chmod 2775 {} \;
find storage bootstrap/cache -type f -exec chmod 0664 {} \;
```

> Como MariaDB es local por socket, no necesitas `httpd_can_network_connect_db`.
> Si algún día conectas la DB por TCP a otra máquina: `sudo setsebool -P httpd_can_network_connect_db 1`.

---

## 9. Prueba rápida antes de Nginx

```bash
# [BAZZITE]  Sirve temporalmente para confirmar que la app arranca
php artisan serve --host=0.0.0.0 --port=8000
# Desde otra PC de la LAN: http://TU_IP_LAN:8000
# Ctrl+C para detener cuando confirmes que responde.
```

---

## 10. Servir con Nginx

```bash
# [BAZZITE]
sudo cp deploy/nginx/kina.local.conf /etc/nginx/conf.d/kina.local.conf
sudo nano /etc/nginx/conf.d/kina.local.conf   # ajusta server_name a tu IP/host

sudo nginx -t                 # valida la config
sudo systemctl reload nginx
```

### Firewall (firewalld) — solo LAN, nunca 3306

```bash
# [BAZZITE]  Permite HTTP SOLO desde tu subred LAN (ajusta el rango a tu red)
sudo firewall-cmd --permanent --add-rich-rule='rule family="ipv4" source address="192.168.1.0/24" service name="http" accept'
sudo firewall-cmd --reload

# Comprueba que 3306 NO está permitido (no debe aparecer)
sudo firewall-cmd --list-all
```

### Acceso desde la LAN

```bash
# [BAZZITE]  Averigua tu IP local
ip -4 addr show | grep inet
```

Desde otra PC en la misma red: `http://TU_IP_LAN`.
Opcional, para usar `http://kina.local` añade en cada equipo cliente (o en tu router/DNS):

```
# En /etc/hosts (Linux/Mac) o C:\Windows\System32\drivers\etc\hosts (Windows)
192.168.1.50   kina.local
```

### Healthcheck

```bash
# [BAZZITE]
./deploy/scripts/bazzite-healthcheck.sh
```

---

## 11. Acceso remoto seguro (opcional) — Tailscale o Cloudflare Tunnel

**No abras el 80/443 del router.** Elige una de estas dos:

### Opción A — Tailscale (VPN privada, la más simple)

```bash
# [BAZZITE]
sudo rpm-ostree install tailscale && sudo systemctl reboot   # si no estaba
sudo systemctl enable --now tailscaled
sudo tailscale up
# Accede desde tus dispositivos en el tailnet por la IP 100.x o MagicDNS.
# HTTPS automático dentro del tailnet:
sudo tailscale serve --bg http://127.0.0.1:80
```

### Opción B — Cloudflare Tunnel (expone un dominio con HTTPS, sin abrir puertos)

```bash
# [BAZZITE]
sudo rpm-ostree install cloudflared && sudo systemctl reboot   # si no estaba
cloudflared tunnel login
cloudflared tunnel create kina
# Enruta tu dominio al túnel y apúntalo a http://127.0.0.1:80, luego:
sudo cloudflared service install
```

Con HTTPS activo, en `.env` pon `SESSION_SECURE_COOKIE=true` y `APP_URL=https://tu-dominio`.

---

## 12. Actualizar el servidor (deploys posteriores)

```bash
# [BAZZITE]
cd /var/www/kina
./deploy/scripts/bazzite-deploy.sh
```

Hace `git pull`, dependencias, build, `migrate --force` (sin fresh), caches, permisos y recarga servicios.

---

## 13. Worker de colas (opcional)

Con `QUEUE_CONNECTION=sync` (por defecto) **no** hace falta. Si pasas a `database`:

```bash
# [BAZZITE]
sudo cp deploy/systemd/kina-queue.service /etc/systemd/system/
sudo nano /etc/systemd/system/kina-queue.service   # ajusta User= a tu usuario
sudo systemctl daemon-reload
sudo systemctl enable --now kina-queue.service
journalctl -u kina-queue.service -f
```

---

## ✅ Checklist antes de encender acceso remoto

No enciendas Tailscale/Cloudflare hasta cumplir **todo** esto:

- [ ] **Cambiar credenciales del admin sembrado** (`admin@kina.local` / `password`): entra, cámbialas o crea tu admin real y borra/edita el de prueba.
- [ ] **Desactivar o cambiar los usuarios de prueba** (`test@kina.local`, etc.).
- [ ] `APP_DEBUG=false` en `.env`.
- [ ] `APP_ENV=staging` (o `production` si endureces todo).
- [ ] **DB con `kina_user`, no root**; contraseña fuerte y única.
- [ ] **Firewall activo** (firewalld) y limitado a la LAN; **puerto 3306 cerrado**.
- [ ] MariaDB escuchando solo en `127.0.0.1` (no `0.0.0.0`).
- [ ] **Backups listos** (ver Fase 8: script de dump de DB + storage).
- [ ] **`.env` NO está en git** (confírmalo: `git status` no debe listarlo).
- [ ] **Permisos revisados**: `storage/` y `bootstrap/cache` escribibles por `nginx`, sin `chmod 777`.
- [ ] Contextos **SELinux** aplicados (`restorecon` ok).
- [ ] **Tests y build corridos** antes del deploy (`php artisan test`, `npm run build`).
- [ ] Acceso externo **solo** vía Tailscale/Cloudflare (nunca puerto abierto en el router).
- [ ] Con HTTPS: `SESSION_SECURE_COOKIE=true`.

---

## ❌ Qué NO hacer

- ❌ Abrir el puerto 80/443 del router directo a internet sin HTTPS + firewall.
- ❌ Abrir el 3306 (base de datos) a la red.
- ❌ Usar `root`/`root` para la base de datos.
- ❌ `chmod -R 777` sobre el proyecto.
- ❌ `php artisan migrate:fresh` en el servidor (borra datos).
- ❌ Subir el `.env` real a git.
- ❌ Correr `composer install` como `root`.
- ❌ Dejar `APP_DEBUG=true` accesible desde la red.

---

## [VPS futuro] Diferencias al migrar a cloud (adelanto Fase 8)

- El sistema será mutable (`dnf`/`apt` normal), sin `rpm-ostree` ni reboot para instalar.
- Añades **HTTPS real** con Certbot/Let's Encrypt (o el proxy del proveedor).
- Firewall del proveedor (Security Groups) + firewalld local.
- `APP_ENV=production`, `APP_DEBUG=false`, y caches siempre activas.
- Backups automatizados fuera del servidor (S3/almacenamiento del proveedor).
- La app (código) es la misma; solo cambian infra y credenciales.
