# Melichinkul

Sistema de control de flotas de vehículos, camiones y maquinaria. Gestión de mantenimientos, conductores, certificaciones, alertas y reportes.

**Stack:** Laravel 12 · Livewire 4 · PHP 8.2 · PostgreSQL · Docker

**Licencia:** [MIT](LICENSE)  
**English:** [README.en.md](README.en.md)

---

## Arranque rápido (Docker, sin Sail)

1. **PostgreSQL en el host**  
   - Crear BD: `melichinkul_db`  
   - Configurar en `.env`: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

2. **Variables de entorno**
   ```bash
   cp .env.example .env
   # Editar .env: DB_PASSWORD y el resto según tu entorno
   ```

3. **Contenedores**
   ```bash
   docker compose up -d --build
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   docker compose exec app php artisan storage:link
   ```

4. **Abrir**
   - App: http://localhost:8001  
   - Mailpit (correos de prueba): http://localhost:8025  

---

## Git y GitHub

- Repo: `https://github.com/roro66/melichinkul.git`  
- Para push/pull: configurar autenticación (PAT en la URL del remoto o SSH).

```bash
git remote add origin https://[TU_PAT]@github.com/roro66/melichinkul.git
git add . && git commit -m "Laravel 12 + Docker + Mailpit, base limpia"
git push -u origin main
```

---

## Documentación

La documentación de alcance, entorno y convenciones se mantiene fuera del repositorio (no incluida en el repo público).

---

## Estructura Docker

| Servicio | Puerto | Función        |
|----------|--------|----------------|
| nginx    | 8001   | App web        |
| redis    | 6379   | Cache / colas  |
| mailpit  | 8025, 1025 | Correo en desarrollo |

PostgreSQL se usa el del host vía `host.docker.internal`.
