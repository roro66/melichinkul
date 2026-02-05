# Auditoría de secretos (repo público)

**Fecha:** 2026-01-30  
**Objetivo:** Asegurar que no se suban credenciales a GitHub al hacer el repositorio público.

## Cambios realizados

1. **`docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md`**  
   Se reemplazó la contraseña real de PostgreSQL por el placeholder `***` y texto indicando configurar en `.env`. La contraseña había aparecido en 3 lugares en ese archivo.

2. **`.env.example`**  
   El comentario de `DB_PASSWORD` ya no hace referencia a otro documento; indica configurar según entorno sin valores reales.

3. **`README.md`**  
   Se eliminaron referencias a `docs/CONFIGURACION_ENTORNO.md` para credenciales y PAT, ya que ese archivo está en `.gitignore` y no existe en el repo público.

## Archivos que nunca se suben (correcto)

- **`.env`** — en `.gitignore`; contiene `DB_PASSWORD`, `APP_KEY`, etc.
- **`docs/CONFIGURACION_ENTORNO.md`** — en `.gitignore`; contiene IP del servidor, contraseña SSH, PAT de GitHub, contraseña PostgreSQL.

## Historial de Git

La contraseña de PostgreSQL **sí estuvo** en commits anteriores del archivo `docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md` (desde el commit inicial). Si el repo ya se ha pusheado a GitHub, esa contraseña queda en el historial remoto.

### Recomendaciones

1. **Rotar credenciales** (recomendado si el repo es o será público):
   - Cambiar la contraseña del usuario PostgreSQL en desarrollo y producción.
   - Actualizar `.env` y `docs/CONFIGURACION_ENTORNO.md` (local) con la nueva contraseña.
   - Considerar rotar el PAT de GitHub (revocar el actual y crear uno nuevo) y la contraseña SSH del servidor si quieres máxima precaución.

2. **Purgar el historial** (opcional, para que la contraseña antigua no aparezca en clones):
   - Instalar [git-filter-repo](https://github.com/newren/git-filter-repo): `pip install git-filter-repo`.
   - Crear un archivo `replacements.txt` con una línea: `@Postgresql1966#==>***`
   - Ejecutar:  
     `git filter-repo --replace-text replacements.txt --path docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md --force`
   - **Importante:** Esto reescribe el historial. Si ya hay pusheado a GitHub, hará falta `git push --force origin main`. Coordinar con cualquier otro colaborador.

Este archivo puede eliminarse o mantenerse como referencia interna; no contiene secretos.
