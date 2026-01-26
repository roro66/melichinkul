# PLAN DE DEMO FUNCIONAL - MELICHINKUL

**Objetivo:** Crear un prototipo funcional en 1-2 semanas para mostrar al cliente la dirección del proyecto.

**Enfoque:** Priorizar funcionalidades visuales e impactantes que demuestren el valor del sistema.

---

## FUNCIONALIDADES PRIORITARIAS PARA EL DEMO

### 1. Dashboard Atractivo (ALTA PRIORIDAD - MÁS IMPACTO VISUAL)
**Tiempo estimado:** 2-3 días

**Componentes:**
- Cards con métricas clave:
  - Total de vehículos
  - Mantenimientos en curso
  - Alertas críticas
  - Vehículos que requieren atención
- Gráfico simple de costos (últimos 6 meses) - puede ser estático o con datos de ejemplo
- Lista de alertas críticas destacadas
- Lista de próximos mantenimientos

**Tecnología:**
- Livewire component `Dashboard`
- Chart.js o similar para gráficos
- Tailwind CSS o Bootstrap para diseño moderno

**Datos:**
- Seeders con datos de ejemplo realistas (10-15 vehículos, 20-30 mantenimientos)

---

### 2. CRUD de Vehículos (ALTA PRIORIDAD)
**Tiempo estimado:** 2-3 días

**Funcionalidades:**
- Listado de vehículos con DataTables (búsqueda, filtros, paginación)
- Formulario crear/editar vehículo
- Vista detalle de vehículo con pestañas:
  - Resumen
  - Mantenimientos
  - Certificaciones (básico)
  - Historial

**Tecnología:**
- Livewire `VehicleTable` con DataTables
- Livewire `VehicleForm`
- Validación de patentes chilenas (básica)

**Datos:**
- Seeders con vehículos de ejemplo (diferentes categorías)

---

### 3. Sistema de Alertas Básico (ALTA PRIORIDAD - MUY VISUAL)
**Tiempo estimado:** 2 días

**Funcionalidades:**
- Listado de alertas con badges de severidad (rojo, amarillo, azul)
- Filtros por severidad y tipo
- Cerrar alerta (con modal de confirmación)
- Generación automática básica:
  - Alertas de mantenimientos próximos (job simple)
  - Alertas de documentos por vencer (simulado)

**Tecnología:**
- Livewire `AlertTable`
- Jobs programados básicos
- Badges visuales con colores

**Datos:**
- Seeders con alertas de ejemplo

---

### 4. CRUD de Mantenimientos Básico (MEDIA PRIORIDAD)
**Tiempo estimado:** 2-3 días

**Funcionalidades:**
- Listado de mantenimientos
- Formulario crear/editar mantenimiento
- Estados básicos: programado, en_proceso, completado
- Asociación vehículo → mantenimiento
- Registro básico de costos

**Tecnología:**
- Livewire `MaintenanceTable`
- Livewire `MaintenanceForm`

**Datos:**
- Seeders con mantenimientos de ejemplo asociados a vehículos

---

### 5. Autenticación y Roles Básicos (MEDIA PRIORIDAD)
**Tiempo estimado:** 1 día

**Funcionalidades:**
- Login/Logout
- Roles básicos: admin, supervisor, tecnico
- Middleware de permisos básico
- Usuario demo: admin@demo.com / password

**Tecnología:**
- Laravel Breeze o Jetstream (rápido)
- Spatie Permission (básico)

---

### 6. Certificaciones Básicas (BAJA PRIORIDAD - PERO VISUAL)
**Tiempo estimado:** 1-2 días

**Funcionalidades:**
- Listado de certificaciones por vehículo
- Formulario crear certificación
- Badges de estado (vigente, por vencer, vencido)
- Campo para archivo adjunto (simulado o básico)

**Tecnología:**
- Livewire component básico
- Storage local para archivos

---

## ESTRUCTURA MÍNIMA DE BASE DE DATOS

**Tablas esenciales:**
1. `users` (Laravel estándar)
2. `vehiculos` (campos básicos: patente, marca, modelo, año, categoria, estado)
3. `mantenimientos` (campos básicos: vehiculo_id, tipo, estado, fecha_programada, fecha_fin, costo_total)
4. `alertas` (campos básicos: vehiculo_id, tipo, severidad, estado, titulo, mensaje)
5. `certificaciones` (campos básicos: vehiculo_id, tipo, fecha_vencimiento, estado)

**Nota:** No necesitamos todas las tablas del plan maestro para el demo. Solo las esenciales.

---

## PLAN DE DESARROLLO RÁPIDO (10-12 días)

### Día 1-2: Setup y Estructura Base
- [ ] Instalar Laravel en Docker
- [ ] Configurar PostgreSQL (crear base de datos `melichinkul_db`)
- [ ] Instalar Livewire, DataTables, Tailwind/Bootstrap
- [ ] Crear migraciones básicas (users, vehiculos, mantenimientos, alertas, certificaciones)
- [ ] Seeders básicos con datos de ejemplo

### Día 3-4: Autenticación y Dashboard
- [ ] Instalar Laravel Breeze
- [ ] Configurar roles básicos
- [ ] Crear componente Livewire `Dashboard`
- [ ] Cards con métricas (pueden ser estáticas o con datos de ejemplo)
- [ ] Lista de alertas críticas

### Día 5-6: CRUD de Vehículos
- [ ] Componente `VehicleTable` con DataTables
- [ ] Componente `VehicleForm`
- [ ] Validación básica de patentes
- [ ] Vista detalle de vehículo
- [ ] Seeders con vehículos de ejemplo

### Día 7-8: Sistema de Alertas
- [ ] Componente `AlertTable`
- [ ] Badges de severidad (colores)
- [ ] Job básico para generar alertas (simulado)
- [ ] Cerrar alerta con modal
- [ ] Seeders con alertas de ejemplo

### Día 9-10: CRUD de Mantenimientos
- [ ] Componente `MaintenanceTable`
- [ ] Componente `MaintenanceForm`
- [ ] Estados básicos
- [ ] Asociación vehículo → mantenimiento
- [ ] Seeders con mantenimientos de ejemplo

### Día 11-12: Certificaciones y Pulido
- [ ] CRUD básico de certificaciones
- [ ] Badges de estado
- [ ] Pulir diseño y UX
- [ ] Agregar más datos de ejemplo
- [ ] Testing básico de flujos principales

---

## DATOS DE EJEMPLO REALISTAS

**Vehículos (10-15):**
- Utilitarios: Fiat Berlingo, Peugeot Partner
- Camionetas: Nissan Navara, Ford F150
- Camiones con grúa: Iveco, Mercedes
- Maquinaria: Bobcat

**Mantenimientos (20-30):**
- Mezcla de preventivos y correctivos
- Diferentes estados
- Asociados a diferentes vehículos
- Costos variados

**Alertas (15-20):**
- Mantenimientos próximos
- Mantenimientos vencidos
- Certificados por vencer
- Diferentes severidades

**Certificaciones:**
- 2-3 por vehículo
- Diferentes estados (vigente, por vencer, vencido)

---

## LO QUE NO INCLUIR EN EL DEMO (para ahorrar tiempo)

- ❌ Sistema completo de inventario (solo mencionar que estará)
- ❌ Gestión completa de conductores (solo mencionar)
- ❌ Notificaciones por email (solo mencionar)
- ❌ Exportaciones a Excel/PDF (solo mencionar)
- ❌ Jobs complejos de cálculo
- ❌ Sistema de aprobaciones
- ❌ Checklist de mantenimiento
- ❌ Evidencia/fotos

**Nota:** Mencionar estas funcionalidades como "próximas implementaciones" en el demo.

---

## PRESENTACIÓN DEL DEMO

**Estructura sugerida:**
1. **Dashboard** - Mostrar métricas y alertas (impresiona visualmente)
2. **Vehículos** - Mostrar listado y crear uno nuevo
3. **Mantenimientos** - Mostrar historial y crear uno nuevo
4. **Alertas** - Mostrar sistema de alertas y cerrar una
5. **Certificaciones** - Mostrar gestión de documentos

**Mensajes clave:**
- "Este es el prototipo funcional que muestra la dirección del proyecto"
- "Las funcionalidades mostradas son las básicas, el sistema completo incluirá..."
- "El diseño final será más pulido, esto es para validar funcionalidad"

---

## CONSIDERACIONES TÉCNICAS

**Stack mínimo:**
- Laravel 11
- Livewire 3
- DataTables (yajra/laravel-datatables)
- Tailwind CSS o Bootstrap
- PostgreSQL
- Docker (ya configurado)

**Prioridad de código:**
- Funcionalidad > Perfección
- Datos de ejemplo > Lógica compleja
- Visual > Backend complejo

**Nota:** El código puede ser mejorado después, lo importante es mostrar funcionalidad.

---

## CRITERIOS DE ÉXITO DEL DEMO

✅ El cliente puede navegar por el sistema
✅ Ve datos realistas y relevantes
✅ Entiende la dirección del proyecto
✅ Puede interactuar con las funcionalidades básicas
✅ Se siente confiado de que el proyecto va por buen camino

---

## PRÓXIMOS PASOS DESPUÉS DEL DEMO

1. Recopilar feedback del cliente
2. Ajustar prioridades según feedback
3. Continuar con desarrollo completo según plan maestro
4. Implementar funcionalidades faltantes en fases

---

**Tiempo total estimado:** 10-12 días de desarrollo
**Resultado:** Demo funcional que muestra la dirección del proyecto y genera confianza en el cliente.
