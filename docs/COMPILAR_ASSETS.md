# Compilación de Assets con Vite

## Problema

Laravel 12 usa Vite para compilar assets (CSS/JS). Si no se compilan, aparece el error:
```
Vite manifest not found at: /var/www/html/public/build/manifest.json
```

## Solución Definitiva (Según Laravel)

Node.js está **instalado dentro del contenedor Docker** (v20.20.0). Todos los comandos de npm se ejecutan dentro del contenedor.

### 1. Instalar Dependencias

```bash
cd /home/rodrigo/Projects/laravel/melichinkul
docker compose exec app npm install
```

### 2. Compilar Assets

**Para producción (recomendado):**
```bash
docker compose exec app npm run build
```
Este comando compila y optimiza los assets para producción. Los archivos se generan en `public/build/`.

**Para desarrollo (con hot reload):**
```bash
docker compose exec app npm run dev
```
Este comando debe ejecutarse en una terminal separada y mantenerse corriendo mientras desarrollas. Permite ver cambios en tiempo real sin recargar la página.

### 3. Verificar Compilación

Después de compilar, verifica que existan:
```bash
docker compose exec app ls -la public/build/
```

Deberías ver:
- `manifest.json`
- `assets/app-*.css` (con hash)
- `assets/app-*.js` (con hash)

## Estado Actual

✅ **Node.js v20.20.0** instalado en el contenedor  
✅ **Dependencias instaladas** (`npm install` ejecutado)  
✅ **Assets compilados** (`npm run build` ejecutado)  
✅ **Manifest.json generado** correctamente

## Comandos Útiles

**Recompilar después de cambios en CSS/JS:**
```bash
docker compose exec app npm run build
```

**Ver versión de Node.js:**
```bash
docker compose exec app node --version
```

**Ver versión de npm:**
```bash
docker compose exec app npm --version
```

## Nota sobre Docker

Los assets se compilan **dentro del contenedor Docker** donde está instalado Node.js. Los archivos compilados se sincronizan automáticamente con el host a través del volumen montado (`./:/var/www/html`).
