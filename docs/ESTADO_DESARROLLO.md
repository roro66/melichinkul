# Estado del Desarrollo - Melichinkul

**Última actualización:** 2026-02-01  
**Último commit:** feat(conductores): documentos con nombre legible + fix validación Livewire; feat(notificaciones): notificaciones in-app (Fase 3)

---

## 📋 Resumen del Proyecto

Sistema de gestión de mantenimiento de flotas vehiculares desarrollado con:
- **Laravel 12.x**
- **Livewire 4.x**
- **PHP 8.4+**
- **PostgreSQL**
- **Docker** (sin Sail)
- **Tailwind CSS** (modo oscuro completo)
- **DataTables** (estándar para todas las tablas)
- **Chart.js** (gráficos en dashboard)
- **SweetAlert2** (confirmaciones y mensajes)
- **Spatie Laravel-Permission** (roles y permisos granulares)

---

## ✅ Módulos Completados

### 1. **Dashboard** ✅
- Cards de métricas (vehículos, mantenimientos, costo del mes, alertas)
- Gráficos Chart.js (costos últimos 6 meses, gastos por tipo)
- Widgets: Mantenimientos en Curso, Próximos Mantenimientos, Vehículos que Requieren Atención, **Alertas activas** (vehículo y stock)
- Top 5 vehículos por costo
- Modo oscuro con contraste adecuado

### 2. **Módulo Vehículos** ✅
- CRUD completo, DataTables, exportación (Excel, CSV)
- Ficha del vehículo con tabs: Resumen, Mantenimientos, Estadísticas, Certificaciones, Alertas
- Validación de patentes, categorías

### 3. **Módulo Mantenimientos** ✅
- CRUD completo (Livewire form), DataTables, exportación
- Estados: scheduled, in_progress, completed, pending_approval, cancelled
- Evidencia (factura/foto), aprobación por costo (umbral configurable)
- **Repuestos utilizados**: sección en ficha para agregar/quitar repuestos; al completar o aprobar se descuenta stock y se registran movimientos tipo "uso"

### 4. **Plantillas de Mantenimiento** ✅
- CRUD de plantillas (nombre, tipo, descripción, repuestos con cantidad)
- DataTables en listado (server-side, búsqueda, eliminar vía AJAX)
- Aplicar plantilla al crear mantenimiento: pre-llena tipo y descripción; al guardar copia repuestos al mantenimiento y redirige a ficha
- Menú "Plantillas" (permiso maintenances.view)

### 5. **Módulo Conductores** ✅
- CRUD completo, DataTables, validación RUT y licencia
- Asignaciones (driver_assignments), integración con vehículos

### 6. **Módulo Certificaciones** ✅
- CRUD por vehículo, documentos (archivos), vencimientos
- Enlace desde ficha del vehículo

### 7. **Sistema de Alertas** ✅
- Tabla con DataTables (vehículo o repuesto según tipo)
- Generación automática: certificados por vencer/vencidos, licencias, mantenimientos vencidos, **stock bajo/agotado**
- Cierre y posponer (modal), notificación email para alertas críticas
- Comando programado: `alerts:generate` (diario)

### 8. **Módulo Inventario de Repuestos** ✅
- **Catálogo repuestos**: CRUD, DataTables, columnas Stock/Mín/Estado stock, ficha con stock actual y últimos movimientos
- **Proveedores**: CRUD, DataTables
- **Compras**: CRUD (borrador → recibido), ítems dinámicos, acción "Recibir" (actualiza stock y movimientos), **exportación Excel/CSV**
- **Stock**: editar min_stock y location en ficha repuesto; ajustes manuales (entrada/salida)
- **Movimientos de inventario**: listado con DataTables, filtro por repuesto
- **Repuestos en mantenimiento**: pivot maintenance_spare_parts; al completar mantenimiento se descuenta stock y se crean movimientos tipo "uso"
- **Alertas de stock**: stock_empty (crítica), stock_below_min (advertencia); cierre automático cuando stock OK

### 9. **Permisos por rol** ✅
- **Spatie Laravel-Permission**: roles (administrator, supervisor, administrativo, technician, viewer) y permisos granulares por recurso (vehicles.*, maintenances.*, drivers.*, alerts.*, spare_parts.*, suppliers.*, purchases.*, inventory.view_movements, certifications.*, reports.view, users.manage)
- Rutas protegidas con middleware `permission:...`
- Menú y botones (aprobar, editar, cerrar/posponer alertas) con `@can`
- Seeder `RolesAndPermissionsSeeder` sincroniza usuarios existentes con Spatie
- Documentación en CONVENCIONES.md

### 10. **Checklist de Mantenimiento** ✅
- CRUD de ítems de checklist (nombre, tipo preventive/corrective/inspection o todos, obligatorio, orden)
- Ítems se muestran en la ficha del mantenimiento según el tipo del mantenimiento
- Marcar/desmarcar ítem completado (toggle) con registro de quién y cuándo
- Validación: no se puede completar ni aprobar un mantenimiento sin tener todos los ítems obligatorios marcados
- Menú "Checklist" (permiso maintenances.view)

### 11. **Auditoría** ✅
- Tabla `audit_logs` (user_id, action, model, model_id, description, old_values, new_values, ip_address, user_agent, created_at)
- Modelo `AuditLog` y servicio `AuditService` para registrar acciones críticas
- Registro automático en: eliminar vehículo, aprobar mantenimiento, eliminar mantenimiento, cerrar alerta
- Vista "Auditoría" con DataTables (listado por fecha, usuario, acción, modelo, descripción)
- Permiso `audit.view` (solo administrator y supervisor), menú "Auditoría"

### 12. **Búsqueda rápida por patente (header)** ✅
- Ruta `GET /vehiculos-buscar?q=` devuelve JSON con hasta 10 vehículos (id, license_plate, brand, model).
- Input en el header (visible si el usuario tiene `vehicles.view`) con debounce 300 ms; dropdown con resultados; clic en resultado lleva a la ficha del vehículo.

### 13. **Badge de alertas en navegación (polling)** ✅
- Ruta `GET /alertas-resumen` devuelve JSON `{ total, criticas }` (alertas no cerradas).
- Badge junto al enlace "Alertas" en el menú: muestra total pendientes; fondo rojo si hay críticas.
- Polling cada 30 segundos para actualizar el contador (fallback sin WebSockets).

### 14. **Notificaciones in-app (Fase 3)** ✅
- Tabla `notifications` (Laravel database channel): notificaciones por usuario, marcar como leída.
- **Alertas críticas:** al generar alertas críticas (`alerts:generate`), se notifica por correo y se guarda notificación in-app para administradores y supervisores (`CriticalAlertsDigestNotification` con canales `mail`, `database` y `broadcast`).
- **Mantenimiento pendiente de aprobación:** cuando un mantenimiento supera el umbral de costo y queda en `pending_approval`, se envía notificación in-app a administradores y supervisores (`MaintenancePendingApprovalNotification` con canales `database` y `broadcast`).
- **Campana en el header:** icono de campana con contador de no leídas; dropdown con últimas 15 notificaciones, enlace a "Ver" (marca como leída y redirige a alertas o ficha del mantenimiento); opción "Marcar todas leídas".
- Rutas: `GET /notificaciones/{id}/leer`, `POST /notificaciones/marcar-todas-leidas`.

### 15. **Notificaciones en tiempo real (Laravel Reverb + Echo)** ✅
- **Laravel Reverb:** servidor WebSockets (Pusher-compatible). Servicio `reverb` en Docker, puerto expuesto 8002.
- **Broadcasting:** notificaciones (alertas críticas, mantenimiento pendiente aprobación) se emiten por canal `broadcast` además de `database` y `mail`.
- **Laravel Echo + pusher-js:** en el frontend se suscribe al canal privado `App.Models.User.{id}` y escucha eventos `.notification`. Al recibir una notificación: se actualiza el contador de la campana y se muestra un toast (SweetAlert2).
- **Config:** `BROADCAST_CONNECTION=reverb`, variables `REVERB_*` en `.env`; en Docker el app usa `REVERB_HOST=reverb`; el navegador usa `VITE_REVERB_*` (puerto 8002). Rutas de broadcasting en `AppServiceProvider` (`Broadcast::routes()`).

### 16. **Reportes avanzados** ✅
- Ruta `GET /reportes` (permiso `reports.view`). Menú "Reportes" visible para roles con ese permiso (technician, viewer, administrativo, supervisor, administrator).
- **Estadísticas (últimos 12 meses):**
  - **Fallas por vehículo:** gráfico de barras horizontal (top 15) con cantidad de mantenimientos correctivos completados por vehículo.
  - **Fallas por conductor:** gráfico de barras horizontal (top 15) con cantidad de correctivos donde el conductor estaba asignado al mantenimiento.
  - **Tendencia de costos:** gráfico de líneas por mes (total, preventivo, correctivo, inspección).
  - **Distribución por tipo:** gráfico doughnut con cantidad de mantenimientos completados por tipo (preventivo, correctivo, inspección).
  - **Top 10 vehículos por costo total:** tabla con enlace a ficha del vehículo.
- Cards de resumen: total fallas, costo por fallas, mantenimientos completados, costo total del período.
- Chart.js (CDN) con soporte modo oscuro. Vista `reportes/index.blade.php`, controlador `ReportController`.

### 17. **Flujo: programar mantenimiento preventivo y aviso al mecánico** ✅
- **Programar mantenimiento preventivo:** (1) Desde **Mantenimientos** → **Nuevo Mantenimiento**, o (2) desde la ficha del **Vehículo** → pestaña Mantenimientos → **Nuevo Mantenimiento** (el vehículo queda pre-seleccionado). Tipo **Preventivo**, estado **Programado**, fecha programada y descripción obligatorios; opcional: técnico responsable, conductor asignado. Guardar.
- **Aviso al mecánico:** al crear un mantenimiento en estado **Programado**, se envía notificación in-app (y email si el usuario tiene notificaciones activadas) a todos los usuarios con rol **Técnico** y al **técnico responsable** asignado si es otro usuario (`MaintenanceScheduledNotification`). La campana del header y el toast en tiempo real (Reverb) muestran el aviso.
- **Dónde ver lo programado:** listado **Mantenimientos** (filtro por estado "Programado"), **Dashboard** (bloque "Próximos Mantenimientos"), **Calendario** (`/mantenimientos/calendario`), ficha del vehículo (pestaña Mantenimientos).

### 18. **Pendientes prioritarios implementados** ✅
- **Logs de acceso:** tabla `access_logs`, middleware `LogAccess` (solo GET no-AJAX), vista `/accesos` (permiso `audit.view`), menú "Accesos".
- **Exportación PDF:** `barryvdh/laravel-dompdf`. Historial por vehículo (ficha vehículo → "Historial PDF"), Estado flota y Dashboard ejecutivo (Reportes → botones PDF).
- **Calendario de mantenimientos:** `/mantenimientos/calendario`, menú "Calendario", vista mensual con enlaces a ficha del mantenimiento.
- **Reportes automáticos por email:** comando `reports:send-monthly` (día 1 a las 07:00) a administrator y supervisor; notificación `MonthlyReportNotification`.

---

## 🔧 Configuraciones Técnicas

- **DataTables:** server-side, exportación (Excel, CSV, Print), column visibility, modo oscuro
- **SweetAlert2:** confirmaciones, mensajes, modo oscuro
- **Convenciones:** código en inglés, UI en español (docs/CONVENCIONES.md)

---

## 🚧 Pendientes (Plan Maestro – Fase 3 y posteriores)

- **Reportes avanzados** de inventario/compras (opcional; reportes de mantenimiento/fallas ya implementados)
- **Fase 4:** Caché inteligente, jobs asíncronos, análisis avanzados de costos, backup automático (búsqueda por patente en header ya implementada)
- **Fase 5:** Optimizaciones BD, testing, documentación técnica y de usuario, preparación API REST

---

## 📁 Estructura de Archivos Clave

```
app/
├── Http/Controllers/
│   ├── AlertController.php ✅
│   ├── DashboardController.php ✅
│   ├── DriverController.php ✅
│   ├── MaintenanceController.php ✅
│   ├── MaintenanceTemplateController.php ✅
│   ├── MaintenanceChecklistItemController.php ✅
│   ├── PurchaseController.php ✅
│   ├── SparePartController.php ✅
│   ├── StockController.php ✅
│   ├── SupplierController.php ✅
│   ├── VehicleController.php ✅
│   ├── CertificationController.php ✅
│   ├── InventoryMovementController.php ✅
│   ├── NotificationController.php ✅
│   ├── ReportController.php ✅
│   └── AuditLogController.php ✅
├── Services/
│   ├── AlertService.php ✅
│   ├── AuditService.php ✅
│   └── ...
├── Exports/
│   ├── MaintenancesExport.php ✅
│   ├── PurchasesExport.php ✅
│   └── VehiclesExport.php ✅
├── Models/
│   ├── Alert.php ✅
│   ├── Maintenance.php ✅ (+ MaintenanceSparePart)
│   ├── MaintenanceTemplate.php ✅
│   ├── MaintenanceChecklistItem.php, MaintenanceChecklistCompletion.php ✅
│   ├── Purchase.php, PurchaseItem.php, Stock.php, InventoryMovement.php ✅
│   ├── SparePart.php, Supplier.php ✅
│   ├── Vehicle.php ✅
│   └── AuditLog.php ✅
├── Notifications/
│   ├── CriticalAlertsDigestNotification.php ✅ (mail + database)
│   └── MaintenancePendingApprovalNotification.php ✅ (database)
├── Console/Commands/
│   └── GenerateAlertsCommand.php ✅ (incluye stock)
└── database/seeders/
    └── RolesAndPermissionsSeeder.php ✅
```

---

## 💡 Recordatorios

- DataTables estándar para nuevas tablas
- SweetAlert2 para confirmaciones
- Contraste en modo oscuro
- Convenciones: inglés código, español UI

---

**¡Buen trabajo! 🚀**
