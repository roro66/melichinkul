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

La contraseña de PostgreSQL había aparecido en commits anteriores en `docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md`. **El repositorio nunca fue público**, por lo que no hubo exposición externa.

Se **purgó el historial** con `git filter-branch`: en todos los commits se reemplazó la contraseña por `***`, se eliminaron los refs de backup y se hizo `git push --force origin main`. El historial en GitHub ya no contiene la contraseña.

### Antes de hacer el repo público

- **Rotar credenciales** (recomendado): cambiar contraseña de PostgreSQL en desarrollo y producción, actualizar `.env` y tu documentación local. Opcionalmente rotar PAT de GitHub y contraseña SSH del servidor.
- Con la purga hecha, no es necesario volver a reescribir historial.

Este archivo puede eliminarse o mantenerse como referencia interna; no contiene secretos.
