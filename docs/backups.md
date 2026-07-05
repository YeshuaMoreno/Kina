# Backups y restore de Kina

Cómo respaldar y restaurar Kina en el servidor **Bazzite** (o cualquier Linux con la app).
Todo se hace desde la **raíz del proyecto** (donde están `artisan` y `.env`).

## Qué contiene un backup

- ✅ **Base de datos** completa (`database.sql`) — usuarios, perfiles, conexiones, mensajes, reportes…
- ✅ **`storage/app/public`** — las fotos de perfil subidas.
- ⚠️ **`.env`** — solo si usas `--with-env`. Es **sensible** (contiene la contraseña de la DB y `APP_KEY`).

## Qué NO contiene (a propósito)

- `vendor/` y `node_modules/` → se regeneran con `composer install` / `npm ci`.
- `public/build/` → se regenera con `npm run build`.
- El código → ya está en git.

---

## Hacer un backup

```bash
# [BAZZITE]  desde /var/www/kina
./deploy/scripts/backup-kina.sh              # DB + storage
./deploy/scripts/backup-kina.sh --with-env   # + .env (guárdalo en lugar seguro)
```

Genera `backups/kina-backup-YYYYmmdd-HHMMSS.tar.gz`. No borra backups anteriores.
Las credenciales se leen del `.env` y **nunca** se imprimen (se usa un `my.cnf` temporal con permisos `600`).

## Restaurar un backup

> ⚠️ **Sobreescribe** la base de datos y `storage/app/public` actuales. Pide confirmación.

```bash
# [BAZZITE]  desde /var/www/kina
./deploy/scripts/restore-kina.sh backups/kina-backup-YYYYmmdd-HHMMSS.tar.gz
# Escribe 'restaurar' cuando lo pida.

# Después:
php artisan optimize:clear
php artisan storage:link
```

El restore **nunca** ejecuta `migrate:fresh` ni borra la base entera: solo importa el dump
(que reemplaza las tablas) y descomprime el storage.

---

## Dónde guardar los backups (regla 3-2-1 simplificada)

Un backup en la misma Lenovo **no** te salva si se muere el disco. Copia el `.tar.gz` **fuera**:

```bash
# [DEV/Windows]  Traer el backup a tu PC por SSH/scp (ajusta IP/usuario)
scp usuario@192.168.1.50:/var/www/kina/backups/kina-backup-*.tar.gz  D:\backups-kina\

# o con Tailscale (usa el nombre del tailnet)
scp usuario@lenovo-kina:/var/www/kina/backups/kina-backup-*.tar.gz  ./
```

Guarda además una copia en un disco externo o almacenamiento en la nube personal.
**No** subas backups a git ni a repos públicos (contienen datos personales).

## Periodicidad recomendada (staging)

- **Diario** mientras haya usuarios de prueba activos.
- **Antes de cada `bazzite-deploy.sh`** (por si una migración sale mal).
- Automatizable con cron/systemd-timer (ejemplo diario 3am):

```bash
# [BAZZITE]  crontab -e
0 3 * * *  cd /var/www/kina && ./deploy/scripts/backup-kina.sh >> storage/logs/backup.log 2>&1
```

---

## Probar un restore en local (ensayo, sin tocar el servidor)

La mejor forma de confiar en un backup es **restaurarlo en otro lado**:

```bash
# [DEV/Windows o cualquier Linux]  en una copia limpia del repo
cp .env.example .env && php artisan key:generate
# crea una base vacía 'kina_test' y apunta el .env a ella
./deploy/scripts/restore-kina.sh ruta/al/kina-backup-XXXX.tar.gz
php artisan storage:link
php artisan serve   # revisa que los datos y fotos aparezcan
```

## Advertencias de seguridad

- El backup contiene **datos personales reales**: trátalo como confidencial.
- Con `--with-env` incluyes secretos (`APP_KEY`, contraseña DB): cífralo o guárdalo offline.
- Verifica permisos del directorio `backups/` (no lo dejes legible por todo el mundo).
- `backups/` está en `.gitignore`: **nunca** se sube a git.
