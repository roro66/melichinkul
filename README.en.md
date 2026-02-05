# Melichinkul

<p align="center"><img src="https://raw.githubusercontent.com/roro66/melichinkul/main/public/images/logo.png" alt="Melichinkul" width="280"></p>

Fleet management system for vehicles, trucks and machinery. Maintenance, drivers, certifications, alerts and reporting.

**Stack:** Laravel 12 · Livewire 4 · PHP 8.2 · PostgreSQL · Docker

**License:** [MIT](LICENSE)  
**Español:** [README.md](README.md)

---

## Quick start (Docker, no Sail)

1. **PostgreSQL on the host**  
   - Create database: `melichinkul_db`  
   - Configure in `.env`: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

2. **Environment**
   ```bash
   cp .env.example .env
   # Edit .env: DB_PASSWORD and the rest for your environment
   ```

3. **Containers**
   ```bash
   docker compose up -d --build
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   docker compose exec app php artisan storage:link
   ```

4. **Open**
   - App: http://localhost:8001  
   - Mailpit (test mail): http://localhost:8025  

---

## Git and GitHub

- Repo: `https://github.com/roro66/melichinkul.git`  
- For push/pull: set up authentication (PAT in remote URL or SSH).

```bash
git remote add origin https://[YOUR_PAT]@github.com/roro66/melichinkul.git
git add . && git commit -m "Laravel 12 + Docker + Mailpit, clean base"
git push -u origin main
```

---

## Documentation

Scope, environment and conventions documentation is kept outside the repository (not included in the public repo).

---

## Docker layout

| Service | Port  | Purpose        |
|---------|-------|----------------|
| nginx   | 8001  | Web app        |
| redis   | 6379  | Cache / queues |
| mailpit | 8025, 1025 | Mail in development |

PostgreSQL runs on the host; use `host.docker.internal` to connect from containers.
