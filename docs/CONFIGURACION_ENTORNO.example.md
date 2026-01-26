# Configuración del Entorno - Melichinkul

Sistema de gestión de flotas (contratista Lipigas, Chile).  
**Stack:** Laravel 12, Livewire 4, PHP 8.2, PostgreSQL, Docker.  
**No se usa Laravel Sail;** se usa `docker-compose` propio.

**Uso:** Copiar este archivo a `CONFIGURACION_ENTORNO.md` y completar los valores sensibles (PAT, contraseña de BD) en local. **No subir `CONFIGURACION_ENTORNO.md` a GitHub** (está en `.gitignore`).

---

## 1. Requisitos

- **Ubuntu 24.04** (u otra distro con Docker)
- **Docker** y **Docker Compose**
- **PostgreSQL** en el host (o accesible en `host.docker.internal:5432`)
- **Git**

---

## 2. PostgreSQL

La app se conecta al PostgreSQL del **host** (no a un contenedor).

- **Host desde el contenedor:** `host.docker.internal`
- **Puerto:** `5432`
- **Base de datos:** `melichinkul_db`
- **Usuario:** `admin`
- **Contraseña:** `[Definir en .env como DB_PASSWORD; no incluir aquí]`

Crear la base de datos:

```bash
# En el host (o donde corra PostgreSQL)
psql -U admin -h localhost -c "CREATE DATABASE melichinkul_db;"
```

---

## 3. Docker (sin Sail)

### Servicios

| Servicio  | Puerto (host) | Uso                          |
|-----------|----------------|------------------------------|
| nginx     | 8001           | App: http://localhost:8001   |
| redis     | 6379           | Cache y colas                |
| mailpit   | 8025 (web), 1025 (SMTP) | Ver correos de prueba |

**PostgreSQL** no está en Docker; se usa el del host.

### Comandos

```bash
# Construir y levantar
docker compose up -d --build

# Composer (instalar deps, Livewire, etc.)
docker compose exec app composer install

# .env y clave
cp .env.example .env
# Editar .env: DB_PASSWORD= y el resto según tu entorno
docker compose exec app php artisan key:generate

# Migraciones
docker compose exec app php artisan migrate

# Enlace de storage (subida de archivos)
docker compose exec app php artisan storage:link
```

### Mailpit

- **Web:** http://localhost:8025  
- **SMTP (desde la app):** `mailpit:1025`  
En desarrollo, todos los correos (alertas de vencimiento, etc.) se capturan en Mailpit y no se envían por correo real.

---

## 4. Git y GitHub

### Repositorio

- **URL:** `https://github.com/roro66/melichinkul.git`

### PAT (Personal Access Token)

**No incluir el PAT en el repositorio.** Configurarlo solo en local:

```bash
cd /ruta/a/melichinkul

# Añadir o actualizar remoto con PAT (reemplazar [TU_PAT] por tu token):
git remote add origin https://[TU_PAT]@github.com/roro66/melichinkul.git
# o, si ya existe:
git remote set-url origin https://[TU_PAT]@github.com/roro66/melichinkul.git

git remote -v
```

Origen del PAT (en este entorno): mismo que en Sover3MP; ver `Sover3MP/docs/CONFIGURACION_ENTORNO.md` si se usa el mismo.

### Comandos habituales

```bash
git status
git add .
git commit -m "Descripción"
git push -u origin main
git pull origin main
```

---

## 5. Archivos que no se suben a GitHub

- **`.env`:** DB_PASSWORD, MAIL, APP_KEY, etc.
- **`docs/CONFIGURACION_ENTORNO.md`:** copia local con PAT y contraseña de BD; en `.gitignore`.

---

## 6. Documentos de referencia

- **Plan maestro y alcance:** `docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md`
