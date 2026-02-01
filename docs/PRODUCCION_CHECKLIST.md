# Checklist para pasar a producción – Melichinkul

Resumen de lo que **ya está bien** y lo que **debes hacer** antes de poner la app en producción.

---

## Lo que ya está bien

- **Permisos y roles:** Spatie; rutas protegidas por middleware; menú según permisos.
- **Contraseñas:** Hash con bcrypt (BCRYPT_ROUNDS en .env).
- **CSRF:** Laravel usa tokens en formularios y peticiones AJAX.
- **Config por entorno:** `APP_DEBUG` y `APP_ENV` se leen de `.env`; por defecto Laravel usa `production` y `false` si no se definen.
- **Secrets:** No hay claves ni contraseñas hardcodeadas en el código; `.env` está en `.gitignore`.
- **Logs:** Configuración estándar; en producción conviene usar `LOG_LEVEL=error` o `warning`.
- **Base de datos:** Migraciones definidas; conexión PostgreSQL configurable vía `.env`.
- **Cola y caché:** Configurados para Redis; en producción usar Redis real (no solo local).
- **Scheduler:** Comandos `alerts:generate` (diario 06:00) y `reports:send-monthly` (día 1 a las 07:00) programados en `routes/console.php`.

---

## Obligatorio antes de producción

### 1. Variables de entorno en el servidor

En el `.env` de producción (o en el panel de tu hosting) configura **al menos**:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

LOG_LEVEL=error
```

- **APP_DEBUG=false** es crítico: evita que se muestren stack traces y datos sensibles.
- **APP_URL** debe ser la URL pública final (con HTTPS).

### 2. HTTPS y cookies de sesión

Si la app se sirve por HTTPS (recomendado):

```env
SESSION_SECURE_COOKIE=true
```

Así la cookie de sesión solo se envía por HTTPS. Si no usas HTTPS, déjalo en `false` o sin definir.

### 3. Base de datos

- Crea la base de datos PostgreSQL en el servidor de producción.
- Configura `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` en `.env`.
- Ejecuta migraciones (solo una vez o en cada despliegue si añades nuevas):

```bash
php artisan migrate --force
```

- Opcional pero recomendado la primera vez: ejecutar el seeder de roles y permisos:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan permission:cache-reset
```

### 4. Scheduler (cron)

Para que se ejecuten las alertas diarias y el reporte mensual por email, el servidor debe ejecutar el scheduler de Laravel cada minuto:

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Ajusta `/ruta/al/proyecto` a la ruta real de la aplicación en producción.

### 5. Cola de jobs (notificaciones por email, etc.)

Si usas `QUEUE_CONNECTION=redis` (o `database`), un worker debe estar corriendo para procesar la cola; si no, los emails y jobs se quedan en cola. Por ejemplo con Supervisor:

```ini
[program:melichinkul-worker]
command=php /ruta/al/proyecto/artisan queue:work redis --sleep=3 --tries=3
directory=/ruta/al/proyecto
autostart=true
autorestart=true
```

O en Docker, el servicio `queue` que ya tienes en `docker-compose.yml` hace este papel.

### 6. Almacenamiento y enlace simbólico

```bash
php artisan storage:link
```

Así `public/storage` apunta a `storage/app/public` (subida de archivos, evidencias, etc.).

### 7. Optimización de Laravel (recomendado)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Hacerlo después de cada despliegue si no usas `php artisan optimize` (que incluye estas tres).

### 8. Assets (Vite)

Genera los assets para producción:

```bash
npm ci
npm run build
```

En producción se sirven los archivos de `public/build`; no hace falta Vite en modo dev.

---

## Recomendado

- **Backups:** Copias automáticas de la base de datos y, si aplica, de `storage/app` (documentos subidos).
- **Mail en producción:** Sustituir Mailpit por un SMTP real (SendGrid, Mailgun, SMTP del proveedor, etc.) y configurar `MAIL_*` en `.env`.
- **Reverb (WebSockets):** Si usas notificaciones en tiempo real en producción, desplegar Reverb (o un servicio equivalente) con HTTPS y configurar `REVERB_*` y `VITE_REVERB_*` para la URL pública.
- **Redis:** Si usas Redis para cola/caché, usar una instancia dedicada (no solo la del Docker de desarrollo) y, si aplica, `REDIS_PASSWORD`.
- **Monitoreo y logs:** Revisar `storage/logs` o enviar logs a un servicio (Papertrail, Logtail, etc.) según `config/logging.php`.
- **Rate limiting:** Laravel incluye throttling; en APIs o rutas sensibles conviene revisar que esté aplicado donde corresponda.

---

## Resumen rápido

| Tarea                              | Obligatorio |
|------------------------------------|-------------|
| APP_ENV=production, APP_DEBUG=false | Sí          |
| APP_URL con HTTPS                  | Sí          |
| SESSION_SECURE_COOKIE=true (con HTTPS) | Sí       |
| Base de datos creada y migrada     | Sí          |
| Cron para `schedule:run`           | Sí (si usas alertas/reportes) |
| Cola (queue worker) si usas cola  | Sí          |
| storage:link                       | Sí          |
| config/route/view cache            | Recomendado |
| npm run build                     | Sí          |
| Backups y mail real               | Recomendado |

Si cumples lo obligatorio y revisas lo recomendado, la app está **lista para moverla a producción** desde el punto de vista de configuración y seguridad básica. Ajusta rutas, dominios y servicios (BD, Redis, mail, Reverb) según tu entorno real.
