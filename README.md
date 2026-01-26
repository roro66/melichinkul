# Melichinkul

Sistema de gestión de flotas para contratista de Lipigas (Chile).  
**Laravel 12 · Livewire 4 · PHP 8.2 · PostgreSQL · Docker**

---

## Arranque rápido (Docker, sin Sail)

1. **PostgreSQL en el host**  
   - Crear BD: `melichinkul_db`  
   - Misma usuario/contraseña que Sover3MP (ver `docs/CONFIGURACION_ENTORNO.md`).

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
- PAT: usar el mismo que Sover3MP (ver `docs/CONFIGURACION_ENTORNO.md`).

```bash
git remote add origin https://[TU_PAT]@github.com/roro66/melichinkul.git
git add . && git commit -m "Laravel 12 + Docker + Mailpit, base limpia"
git push -u origin main
```

---

## Documentos

- `docs/CONFIGURACION_ENTORNO.md` — Entorno, Docker, Git, GitHub, Mailpit.  
- `docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md` — Alcance y diseño.

---

## Estructura Docker

| Servicio | Puerto | Función        |
|----------|--------|----------------|
| nginx    | 8001   | App web        |
| redis    | 6379   | Cache / colas  |
| mailpit  | 8025, 1025 | Correo en desarrollo |

PostgreSQL se usa el del host vía `host.docker.internal`.
