# Resumen para continuar – Melichinkul

**Usa este texto como prompt cuando retomes el desarrollo.** Cópialo y pégalo al inicio del chat para dar contexto.

---

## Proyecto

**Melichinkul**: sistema de gestión de mantenimiento de flotas (empresa contratista Lipigas).  
**BD:** `melichinkul_db` (PostgreSQL en host, conexión vía `host.docker.internal`).

**Stack:** Laravel 12.x, Livewire 4.x, PHP 8.2+, PostgreSQL, Docker (sin Sail), Mailpit.  
**Referencia:** `docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md`.

---

## Lo que ya está hecho

### 1. Infraestructura
- **Docker:** `Dockerfile`, `docker-compose.yml` (app, nginx:8080, redis, mailpit:8025/1025). PostgreSQL en host.
- **`.env.example`:** PostgreSQL, Mailpit, Redis, `APP_LOCALE=es`.
- **Docs:** `docs/CONFIGURACION_ENTORNO.md` (Docker, Git, GitHub, Mailpit).

### 2. Migraciones (todas creadas)
- `add_rol_to_users` (rol: administrador, supervisor, administrativo, tecnico, visualizador)
- `categorias_vehiculos`, `conductores`, `vehiculos`, `certificaciones`, `mantenimientos`
- `documentos` (polimórfico, para evidencia de mantenimientos)
- `repuestos` (stock_actual, stock_minimo_manual), `movimientos_inventario` (cantidad decimal), `mantenimiento_repuestos` (cantidad decimal)
- `asignaciones_conductores`, `alertas` (con snooze: snoozed_until, snoozed_by, snoozed_reason)

**Ejecutar:** `docker compose run --rm app php artisan migrate --force`  
**Seeder:** `CategoriaVehiculoSeeder` (4 categorías) + `DatabaseSeeder` (User con rol administrador).  
`php artisan db:seed --force`

### 3. Modelos (con relaciones)
- `User` (rol), `CategoriaVehiculo`, `Conductor`, `Vehiculo`, `Certificacion`, `Mantenimiento`, `Documento`, `Repuesto`, `MovimientoInventario`, `MantenimientoRepuesto`, `AsignacionConductor`, `Alerta`.

### 4. Servicios
- **StockCriticoService:** stock crítico manual vs dinámico (≥90 días historial → promedio consumo × 1.5).
- **ChileanValidationHelper:** `validarRut`, `normalizarRut`, `validarPatente`, `normalizarPatente`, `ruleRut()`, `rulePatente()`.
- **Traits:** `ValidaRutChileno`, `ValidaPatenteChilena`.
- **AlertaService:** `snoozar()`, `limpiarSnooze()`. `Alerta::scopeVigentes()`, `scopeSnoozed()`.
- **MantenimientoService:** `validarCierre()`, `puedeCerrar()`, `cerrar()` — no permite cerrar correctivo sin evidencia (factura/foto).
- **BloqueoAsignacionService:** `validarPuedeAsignar()` — impide asignar si licencia vencida o Revisión Técnica del vehículo caducada.
- **AsignacionService:** `asignar()` — valida bloqueo, cierra asignación anterior, crea nueva, actualiza `vehiculo.conductor_actual_id`.

### 5. Traducciones
- `lang/es/chile.php` (RUT, patente), `lang/es/alerta.php`, `lang/es/mantenimiento.php`, `lang/es/asignacion.php`.

---

## Siguiente paso: Fase 1 (desarrollo de la app)

Según **sección 11 – Fase 1 (MVP)** y que el **orden 13.6 está completo**:

1. **Autenticación:** login/logout, rutas `auth`, redirección a dashboard, middleware `auth`.
2. **Layout base:** menú, uso de `rol`, estructura para módulos.
3. **Dashboard mínimo:** entrada tras login.
4. **Módulo Vehículos (Livewire 4):** `VehicleTable`, `VehicleForm`, CRUD, categorías, validación patente (`ChileanValidationHelper` / `ValidaPatenteChilena`).
5. **Módulo Mantenimientos (Livewire 4):** `MaintenanceTable`, `MaintenanceForm`, CRUD, estados (programado, en_proceso, completado), relación vehículo.
6. **Opcional:** DataTables (yajra) para tablas.

**Orden sugerido:** Auth + layout + rutas → Dashboard → Vehículos → Mantenimientos.

---

## Rutas y archivos de referencia

- Plan detallado: `docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md` (sec. 11 Fase 1, 4.1 modelo de datos, 13 implementación integral).
- Config entorno: `docs/CONFIGURACION_ENTORNO.md`.
- Servicios: `app/Services/` (StockCritico, Alerta, Mantenimiento, BloqueoAsignacion, Asignacion).
- Validaciones Chile: `app/Support/ChileanValidationHelper.php`, `app/Traits/ValidaRutChileno.php`, `app/Traits/ValidaPatenteChilena.php`.
- Modelos: `app/Models/`. `Vehiculo` usa `revisionTecnicaVigente()`, `soapVigente()`. `Conductor` usa `licenciaVencida()`, `licenciaVigente()`, `licenciaPorVencer()`.

---

## Comandos útiles

```bash
# Migrar
docker compose run --rm app php artisan migrate --force

# Seed
docker compose run --rm app php artisan db:seed --force

# Levantar app (cuando existan rutas/vistas)
docker compose up -d
# App: http://localhost:8080   Mailpit: http://localhost:8025
```

---

## Prompt sugerido para mañana

Puedes copiar y pegar algo así:

> Continuamos con Melichinkul. Lee `docs/RESUMEN_PARA_CONTINUAR.md` para el contexto. Necesito implementar la **Fase 1 (MVP)**: autenticación (login/logout, rutas auth, redirección a dashboard), layout base con menú y roles, dashboard mínimo, y los módulos de **Vehículos** y **Mantenimientos** con Livewire 4 (tablas y formularios, CRUD). Usar las validaciones de patente ya implementadas y los servicios de mantenimiento y asignación cuando corresponda. [Añade aquí si quieres empezar por auth, por vehículos, o por otro punto concreto.]

---

*Documento generado para retomar el desarrollo. Actualizar este resumen si se completan más fases o se cambian decisiones.*
