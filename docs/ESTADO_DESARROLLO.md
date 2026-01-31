# Estado del Desarrollo - Melichinkul

**Última actualización:** 2026-01-31  
**Último commit:** (pendiente) - feat: módulo Auditoría (audit_logs, AuditService, integración en vehículos/mantenimientos/alertas)

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
- **Spatie Laravel-Permission**: roles (administrator, supervisor, administrativo, technician, viewer) y permisos granulares por recurso (vehicles.*, maintenances.*, drivers.*, alerts.*, spare_parts.*, suppliers.*, purchases.*, inventory.view_movements, certifications.*, users.manage)
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

---

## 🔧 Configuraciones Técnicas

- **DataTables:** server-side, exportación (Excel, CSV, Print), column visibility, modo oscuro
- **SweetAlert2:** confirmaciones, mensajes, modo oscuro
- **Convenciones:** código en inglés, UI en español (docs/CONVENCIONES.md)

---

## 🚧 Pendientes (Plan Maestro – Fase 3 y posteriores)

- **Notificaciones en tiempo real:** Laravel Echo + Broadcasting (Pusher/Redis), notificaciones push (badge en navegación ya implementado con polling 30 s)
- **Reportes avanzados** de inventario/compras (opcional)
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
