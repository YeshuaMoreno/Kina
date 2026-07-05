# Checklist antes de enseñar la demo de Kina

Repasa esto **antes** de mostrarle Kina a alguien (inversor, amigo, tester). 10–15 min.

## 0. Preparación técnica

- [ ] `git status` limpio y en la rama correcta.
- [ ] `php artisan test` → **67+ en verde**.
- [ ] `npm run build` sin errores.
- [ ] Servidor arriba (`php artisan serve` o Nginx) y responde.
- [ ] `php artisan migrate --seed` corrido (hay intereses y etiquetas).
- [ ] `php artisan storage:link` hecho (las fotos se ven).

## 1. Crear usuarios demo

Necesitas **al menos 3** perfiles completos para que "Descubrir" y el chat tengan vida:

- [ ] Regístrate y completa el **onboarding** con una cuenta principal (la que mostrarás).
- [ ] Crea 2–3 cuentas más (o siémbralas con tinker) con intereses, intención y ciudad,
      para que aparezcan en Descubrir con **Áreas de Sintonía**.
- [ ] Deja **una solicitud pendiente** entrante y **una conexión ya aceptada** con mensajes,
      para mostrar solicitudes y chat sin improvisar.

```bash
# Atajo opcional para sembrar un perfil demo (ajusta datos)
php artisan tinker --execute="
  \$u = App\Models\User::factory()->create(['name'=>'Ana','is_adult_confirmed'=>true]);
  \$u->profile()->create(['display_name'=>'Ana','looking_for'=>'amistad','social_battery'=>'media','city'=>'CDMX','profile_visibility'=>'publico','onboarding_completed'=>true]);
  \$u->profile->interests()->attach(App\Models\Interest::inRandomOrder()->take(4)->pluck('id'));
  echo 'ok';
"
```

## 2. Recorrido de la demo (el guion)

- [ ] **Landing** (`/`): hero, "Áreas de Sintonía", privacidad, se ve bien en el proyector.
- [ ] **Registro/Login** y **onboarding** de 7 pasos (muestra el 18+ y el consentimiento sensible).
- [ ] **Dashboard**: hero cálido, resumen del usuario, accesos.
- [ ] **Descubrir**: tarjetas con Áreas de Sintonía, botón Conectar.
- [ ] **Solicitar → Aceptar**: enviar solicitud con una cuenta, aceptar con otra.
- [ ] **Chat**: escribir de ida y vuelta; mostrar el "Leído".
- [ ] **Bloquear / Reportar** desde un perfil.
- [ ] **Admin** (`/admin`): stats, suspender/reactivar, revisar un reporte.

## 3. Responsive / móvil

- [ ] Abre en el móvil (o DevTools responsive): landing, onboarding, descubrir y chat
      se ven **mobile-first**, sin desbordes horizontales.
- [ ] Menú hamburguesa de la navegación funciona.

## 4. Seguridad y limpieza (si la demo sale de tu localhost)

- [ ] **Cambiar la contraseña del admin sembrado** (`admin@kina.local` / `password`).
- [ ] **Cambiar o eliminar los usuarios de prueba** obvios (`test@kina.local`).
- [ ] `APP_DEBUG=false` en `.env` (no muestres stacktraces).
- [ ] Si es por LAN/remoto: firewall activo, **3306 cerrado**, acceso vía Tailscale/Cloudflare.
- [ ] Verifica que **no haya archivos ajenos** colados en git:
```bash
git ls-files | grep -iE '\.(xlsx|xls|csv|zip)$'   # no debería devolver nada del proyecto
git status --short                                # sin sorpresas
```
> Nota: `*.xlsx` ya está en `.gitignore`. Si aparecen hojas de cálculo sueltas en la raíz
> (p. ej. `rol_grupo_*.xlsx`), no son de Kina: bórralas o muévelas fuera del repo.

## 5. Plan B (por si algo falla en vivo)

- [ ] Ten un **backup reciente** (`backup-kina.sh`) por si necesitas restaurar rápido.
- [ ] Ten capturas o un video corto del flujo, por si la red falla.
- [ ] Sabes reiniciar servicios: `sudo systemctl reload nginx php-fpm` (o relanzar `php artisan serve`).

---

**Mensaje de una línea para presentar Kina:**
> *"Kina es un espacio para conectar a tu propio ritmo: pensado para personas neurodivergentes
> e introvertidas, sin swipes, con privacidad real y compatibilidad por afinidad, no por foto."*
