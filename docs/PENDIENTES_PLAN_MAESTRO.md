# Pendientes del Plan Maestro – Melichinkul

**Referencia:** `docs/PLAN_MAESTRO_SISTEMA_MANTENIMIENTO_FLOTAS.md`  
**Estado actual:** `docs/ESTADO_DESARROLLO.md`

---

## ✅ Ya implementado (resumen)

- **Módulos:** Dashboard, Vehículos, Mantenimientos, Plantillas, Checklist, Conductores, Certificaciones, Alertas, Inventario (repuestos, proveedores, compras, stock, movimientos), Permisos por rol, Auditoría, Reportes avanzados (fallas por vehículo/conductor, tendencias, top costos), Gestión de usuarios.
- **Exportación:** Excel y CSV en vehículos, mantenimientos, compras; DataTables con exportar/imprimir.
- **Notificaciones:** In-app (campana), Reverb/Echo tiempo real, email (alertas críticas, mantenimiento pendiente aprobación, **mantenimiento programado** para técnicos).
- **Validaciones Chile:** RUT, patentes (traits/helpers).
- **Bloqueo asignación:** No asignar vehículo a conductor con licencia vencida (`BlockAssignmentService`); revisión técnica del vehículo según plan.
- **Evidencia obligatoria:** No cerrar correctivo sin factura/foto.
- **Stock crítico:** Servicio `StockCriticoService` (umbral manual y dinámico ≥90 días historial).
- **Documentos conductores:** CRUD documentos por conductor, ver/descargar.

---

## 🚧 Pendiente (prioritario / corto plazo)

### 1. **Exportación a PDF** ✅
- Historial por vehículo: ficha vehículo → "Historial PDF". Estado flota y Dashboard ejecutivo: Reportes → botones PDF. Paquete `barryvdh/laravel-dompdf`; ejecutar `composer update` si no está instalado.

### 2. **Vista calendario / agenda de mantenimientos**
- Ver mantenimientos programados por semana o mes.
- Enlace desde cada evento a la ficha del mantenimiento.

*Hoy: listado Mantenimientos (filtro por estado) y bloque “Próximos mantenimientos” en Dashboard.*

### 3. **Logs de acceso / seguridad** (Plan §11.5)
- Tabla tipo `accesos_sistema`: usuario, IP, User-Agent, timestamp, página/acción.
- Opcional: middleware o evento de login para registrar cada acceso.

*Hoy: auditoría de acciones críticas (eliminar vehículo, aprobar mantenimiento, etc.), no registro de cada login/página.*

### 4. **Reportes automáticos por email** ✅
- Comando `reports:send-monthly` (día 1 a las 07:00) a administrator y supervisor. `MonthlyReportNotification`.

---

## 📊 Pendiente (reportes y análisis)

### 5. **Reportes avanzados de inventario/compras** ✅
- Reportes de compras por proveedor, por período: en Reportes → Inventario y compras (tabla compras por proveedor, gráfico compras por mes, tabla movimientos por tipo).
- Análisis de inventario (movimientos por tipo en el período).

*Reportes de mantenimiento/fallas ya están.*

### 6. **Análisis de costos por conductor** (Plan §11.8) ✅
- Top 10 conductores por costo (correctivos) con fallas, costo total, costo promedio y enlace al conductor.
- Gráfico de tendencia de costos correctivos por conductor (top 5) últimos 12 meses.

### 7. **Predicción de costos** (Plan §11.9)
- Estimación costo anual por vehículo según historial.
- Alertas si costos superan promedios históricos.

### 8. **Comparativa de proveedores** (Plan §11.10)
- Costos por proveedor, tiempos de entrega, calidad (devoluciones/incidencias).

---

## 🔧 Pendiente (infraestructura y calidad)

### 9. **Fase 4 – Caché, colas, backup**
- Caché donde aporte (ej. resúmenes dashboard, reportes pesados).
- Jobs en cola para reportes/PDF sin bloquear al usuario.
- Backup automático de BD y/o documentos (Plan §11.6).

### 10. **Fase 5 – Testing y API**
- Tests (Unit/Feature) para reglas críticas (alertas, asignaciones, costos).
- Documentación técnica y de usuario.
- Preparación o implementación de API REST (versionado `/api/v1/`) para app móvil o integraciones.

---

## 🔮 Futuro / opcional (Plan)

- **SMS/WhatsApp:** estructura de notificaciones lista; falta canal concreto.
- **App móvil para mecánicos:** backend API-first, modo offline (Plan §12.2.3, §11.13).
- **Múltiples ubicaciones de stock** (§11.11): bodegas, transferencias.
- **Lotes y vencimientos de repuestos** (§11.12): FIFO, alertas por vencimiento.
- **Integración facturación/ERP** (§11.14): campos y API para sincronizar compras.
- **Multi-tenant** (§12.3.3): si se requiere varias organizaciones/sucursales.
- **Internacionalización (i18n)** (§12.3.6): cadenas traducibles, locale.

---

## Resumen rápido

| Área              | Pendiente principal                                      |
|-------------------|----------------------------------------------------------|
| **Exportación**   | PDF (historial por vehículo, certificaciones, estado flota, dashboard) |
| **UX**            | Calendario/agenda de mantenimientos                      |
| **Seguridad**     | Logs de acceso (login/páginas)                           |
| **Reportes**      | Automáticos por email; inventario/compras; predicción costos; proveedores |
| **Infraestructura** | Caché, colas, backup, tests, API REST                 |
| **Futuro**        | SMS/WhatsApp, app móvil, múltiples bodegas, lotes        |

Si quieres, el siguiente paso puede ser priorizar 2–3 ítems (por ejemplo: PDF + calendario + logs de acceso) y bajar a tareas concretas en el código.
