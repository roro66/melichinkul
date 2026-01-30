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
