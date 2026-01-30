# Convenciones del proyecto Melichinkul

## Idioma

- **Backend (código):** inglés. Tablas, columnas, modelos, servicios, métodos, nombres de clases y archivos siguen estándares Laravel y se escriben en inglés.
- **Interfaz de usuario:** español. Textos visibles al usuario (etiquetas, mensajes, validaciones, correos) se sirven desde `lang/es/` y vistas en español.

## Ejemplos

| Ámbito        | Inglés (código)     | Español (solo UI)        |
|---------------|---------------------|---------------------------|
| Tabla         | `alerts`, `driver_assignments` | — |
| Modelo        | `Alert`, `DriverAssignment`   | — |
| Servicio      | `AlertService`, `MaintenanceService` | — |
| Método        | `snooze()`, `validateClosing()` | — |
| Mensaje usuario | —                  | `__('alerta.snooze_cerrada')` |

Referencia: `docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md`, stack Laravel 12.

## Permisos y roles

- **Paquete:** Spatie Laravel-Permission (`spatie/laravel-permission`). Roles y permisos granulares por recurso.
- **Roles (nombres en inglés):** `administrator`, `supervisor`, `administrativo`, `technician`, `viewer`.
- **Permisos:** formato `recurso.accion` (ej. `vehicles.view`, `maintenances.approve`). Definidos en `RolesAndPermissionsSeeder`.
- **Rutas:** protegidas con middleware `permission:nombre`. El menú y botones usan `@can('permiso')` para ocultar acciones no permitidas.
- **Compatibilidad:** la columna `users.role` se mantiene; el accessor `User::rol` devuelve el primer rol Spatie o el valor de `role`. Usuarios existentes se sincronizan con Spatie al ejecutar `RolesAndPermissionsSeeder`.

## Git

- **Commit por módulo:** después de terminar cada módulo o entrega lógica (feature), hacer un commit con mensaje claro, por ejemplo: `feat: módulo X (descripción breve)`.
- Facilita el historial, el rollback y la revisión por cambios.
