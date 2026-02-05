# PLAN MAESTRO - SISTEMA DE GESTIÓN DE MANTENIMIENTO DE FLOTAS

**Nombre de la Aplicación:** Melichinkul  
**Base de Datos:** `melichinkul_db`

**Versiones y requisitos técnicos (único referente):**
- **Laravel 12.x** (PHP 8.2+)
- **Livewire 4.x**
- **PostgreSQL**
- **Docker** (Ubuntu 24.04, PostgreSQL en host, Mailpit para correos de prueba)

---

## 1. VISIÓN GENERAL DEL SISTEMA

### 1.1. Problema que Resuelve

La empresa contratista de Lipigas necesita un sistema centralizado para:

- **Control preventivo**: Anticipar y programar mantenimientos antes de que ocurran fallas críticas
- **Gestión de documentos legales**: Monitorear y alertar sobre vencimientos de documentos obligatorios chilenos (Permiso de Circulación, Revisión Técnica, SOAP, Análisis de Gases, certificados especiales). **CRÍTICO**: Estos documentos pueden ser solicitados en cualquier momento por inspectores o policía. Un vehículo sin documentos vigentes NO puede circular legalmente.
- **Trazabilidad operativa**: Registrar cada evento de mantenimiento con evidencia y responsabilidades
- **Reducción de costos**: Evitar fallas en campo que paralizan operaciones críticas (especialmente en camiones con grúa para estanques). Evitar multas e inmovilizaciones por documentos vencidos.
- **Cumplimiento normativo**: Mantener historial completo para auditorías y seguros. Cumplir con legislación chilena de vehículos en circulación.
- **Optimización de recursos**: Planificar mantenimientos agrupados y coordinar disponibilidad de mecánicos/talleres

### 1.2. A Quién Va Dirigido

**Usuarios primarios:**
- **Administrador/Coordinador de Flota**: Gestiona vehículos, programa mantenimientos, revisa alertas, configuración del sistema
- **Personal Administrativo**: Ingresa documentación de vehículos y conductores, gestiona inventario de repuestos (compras, stock, proveedores), administra catálogo de repuestos
- **Mecánicos/Técnicos**: Registran ejecución de trabajos, reportan condiciones, actualizan estados, utilizan repuestos del inventario en mantenimientos
- **Supervisores/Gerentes**: Monitorean estado general de la flota, reciben alertas críticas, analizan costos, aprueban mantenimientos
- **Conductores** (futuro): Podrían reportar incidencias menores y ver estado de su vehículo asignado

### 1.3. Límites Claros (Qué NO Pretende Resolver)

**Excluido del alcance inicial:**
- Gestión completa de conductores (datos personales, permisos, etc.) - Solo registro básico de asignaciones
- **Control de combustible**: La empresa utiliza tarjetas Copec que ya proporcionan un sistema web completo de gestión de combustible. Este sistema no necesita controlar consumo, gastos de combustible, o tracking de cargas. **Nota:** El campo `tipo_combustible` en vehículos se mantiene como dato característico del vehículo (gasolina, diesel, etc.), no para control de gastos.
- Control de gastos operacionales generales (excepto costos de mantenimiento que sí se registran)
- Planificación de rutas y logística de entrega
- Integración con sistemas externos de GPS o telemetría (preparar estructura para futuro)
- Facturación o módulo financiero completo

**Incluido en el alcance:**
- **Gestión de inventario de repuestos**: Control de stock, compras, proveedores, movimientos de entrada/salida, y administración de repuestos utilizados en mantenimientos
- Gestión de talleres externos (aunque se puede registrar quién ejecutó el trabajo)

**Preparado para expansión futura:**
- API REST para integraciones
- Posible módulo de reportes avanzados y BI
- Notificaciones por SMS/WhatsApp (estructura de notificaciones preparada)
- App móvil para mecánicos (backend API-first)


## 2. STACK TECNOLÓGICO Y HERRAMIENTAS

### 2.1. Stack Principal

**Backend:**
- **Laravel 12.x**: Framework PHP para API y lógica de negocio (requiere **PHP 8.2+**)
- **PostgreSQL**: Base de datos relacional
- **Laravel Sanctum** (o Passport si se necesita OAuth): Autenticación API
- **Laravel Queues**: Jobs asíncronos para alertas y notificaciones

**Frontend:**
- **Laravel Blade**: Templates base
- **Livewire 4.x**: Componentes interactivos sin necesidad de JavaScript complejo
- **DataTables**: Tablas interactivas con búsqueda, filtrado, paginación y ordenamiento
- **Alpine.js**: JavaScript ligero para interactividad (complementa Livewire)
- **Tailwind CSS** (o Bootstrap): Framework CSS para estilos
- **JavaScript/Vanilla JS**: Para funcionalidades específicas si es necesario

**Herramientas de Desarrollo:**
- **Docker**: Contenedorización para desarrollo local (entorno Ubuntu 24.04)
  - Contenedor PHP/Laravel (PHP-FPM, PHP 8.2+)
  - PostgreSQL en host local (conexión vía `host.docker.internal`)
  - Contenedor Nginx (servidor web)
  - Contenedor Redis (para queues y cache, opcional)
  - **Mailpit**: Contenedor para capturar correos de alertas (Revisión Técnica, SOAP, Licencias) sin enviar correos reales en desarrollo/pruebas
  - Docker Compose para orquestación

**Exportación de Datos:**
- **Laravel Excel (Maatwebsite/Excel)**: Exportación a Excel (.xlsx, .xls, .csv)
- **DomPDF o wkhtmltopdf**: Exportación a PDF
- **Barryvdh Laravel-DomPDF**: Alternativa para PDFs simples
- Posibilidad de exportar: Excel, PDF, CSV según necesidad

### 2.2. Integración de Livewire

**Uso de Livewire:**
- Componentes Livewire para:
  - Tablas interactivas (listados de vehículos, mantenimientos, alertas, conductores)
  - Formularios dinámicos (crear/editar vehículos, mantenimientos)
  - Búsqueda y filtrado en tiempo real
  - Actualización de datos sin recargar página completa
  - Dashboard con datos en tiempo real

**Ventajas:**
- Interactividad sin necesidad de escribir JavaScript extenso
- Actualizaciones reactivas del servidor
- Mejor experiencia de usuario sin recargas completas de página
- Mantenimiento más simple del código

**Componentes Livewire esperados:**
- `VehicleTable`: Tabla de vehículos con DataTables integrado
- `MaintenanceTable`: Tabla de mantenimientos con filtros
- `AlertTable`: Tabla de alertas con actualización en tiempo real
- `DriverTable`: Tabla de conductores
- `VehicleForm`: Formulario de crear/editar vehículo
- `MaintenanceForm`: Formulario de crear/editar mantenimiento
- `Dashboard`: Panel con métricas en tiempo real

### 2.3. Integración de DataTables

**Uso de DataTables:**
- Tablas interactivas para:
  - Listados de vehículos (búsqueda, filtrado, paginación, ordenamiento)
  - Historial de mantenimientos (filtros avanzados por fecha, tipo, vehículo)
  - Lista de alertas (filtros por severidad, estado, vehículo)
  - Historial de asignaciones de conductores
  - Lista de conductores

**Características:**
- Búsqueda en tiempo real
- Filtrado avanzado (por columnas específicas)
- Paginación del lado del servidor (para grandes volúmenes)
- Ordenamiento por múltiples columnas
- Exportación a Excel/PDF desde la misma tabla
- Responsive (adaptable a móviles)

**Integración con Livewire:**
- DataTables server-side processing con Livewire
- Actualización reactiva de datos sin recargar tabla completa
- Filtros dinámicos que actualizan la tabla automáticamente

### 2.4. Funcionalidades de Exportación

**Exportación requerida:**

1. **Exportar a Excel:**
   - Listados de vehículos con todos sus datos
   - Historial completo de mantenimientos por vehículo
   - Historial completo de mantenimientos por rango de fechas
   - Lista de alertas (activas, cerradas, por tipo)
   - Historial de asignaciones de conductores
   - Lista de conductores con estadísticas
   - Reporte de costos de mantenimiento

2. **Exportar a PDF:**
   - Historial completo de mantenimientos por vehículo (documento oficial)
   - Certificaciones y documentos de un vehículo
   - Reporte de estado de flota
   - Dashboard ejecutivo (resumen de KPIs)

3. **Exportar a CSV:**
   - Para análisis en herramientas externas (Excel, herramientas BI)
   - Datos tabulares simples

**Ubicación de exportaciones:**
- Botones de exportación en cada vista de tabla/listado
- Filtros aplicados se mantienen en la exportación
- Formato consistente con headers y estilos apropiados

### 2.5. Docker para Desarrollo

**Configuración Existente:**
- **Sistema Operativo**: Ubuntu 24.04 Desktop
- **Docker**: Ya instalado y funcional
- **PostgreSQL**: Ya instalado bajo Docker con conexión vía `host.docker.internal`

**Configuración PostgreSQL:**
- **Host**: `host.docker.internal` (para conectar desde contenedores al PostgreSQL del host)
- **Puerto**: `5432`
- **Base de datos**: `melichinkul_db` (nueva base de datos exclusiva para la aplicación)
- **Usuario**: `admin`
- **Contraseña**: configurar en `.env` (no incluir credenciales reales en documentación)

**Nota importante**: Se creará una nueva base de datos `melichinkul_db` en el PostgreSQL existente. El contenedor de Laravel debe conectarse usando `host.docker.internal` en lugar de un contenedor PostgreSQL separado.

**Servicios requeridos para Docker Compose:**
1. **app** (PHP/Laravel):
   - PHP con extensiones necesarias (PostgreSQL, GD, etc.)
   - Composer instalado
   - Permisos correctos para storage y bootstrap/cache
   - **Configuración `.env`**: Usar credenciales PostgreSQL existentes (`DB_HOST=host.docker.internal`)

2. **nginx**:
   - Servidor web para servir Laravel
   - Configuración para PHP-FPM
   - Soporte para archivos grandes (upload de escaneos)

3. **redis** (opcional):
   - Cache de Laravel
   - Queue driver para jobs asíncronos
   - Puede estar en contenedor separado o usar servicio del host si existe

4. **Mailpit** (recomendado en desarrollo):
   - Captura todos los correos enviados por Laravel sin enviarlos realmente
   - UI web (puerto 8025) para revisar alertas de vencimiento (Revisión Técnica, SOAP, Licencias)
   - Configurar en `.env`: `MAIL_MAILER=smtp`, `MAIL_HOST=mailpit`, `MAIL_PORT=1025`

**Variables de entorno `.env` para Laravel:**
```
DB_CONNECTION=pgsql
DB_HOST=host.docker.internal
DB_PORT=5432
DB_DATABASE=melichinkul_db
DB_USERNAME=admin
DB_PASSWORD=***  # Configurar en .env; no subir credenciales al repositorio
```

**Ventajas:**
- Entorno de desarrollo consistente para todo el equipo
- Configuración rápida (solo `docker-compose up`)
- Aislamiento de dependencias del sistema local (solo Laravel en contenedor, PostgreSQL ya está)
- Reutilización de PostgreSQL existente
- Volúmenes para persistencia de código y storage de Laravel

**Archivos necesarios:**
- `docker-compose.yml`: Configuración de servicios (app, nginx, redis opcional)
- `Dockerfile`: Imagen PHP/Laravel
- `.env`: Variables de entorno con credenciales PostgreSQL existentes
- `.env.example`: Template sin credenciales sensibles
- `README.md`: Documentación de uso (instrucciones de setup con Docker)

**Consideraciones:**
- Asegurar que `host.docker.internal` funcione correctamente en Ubuntu 24.04
- Si no funciona `host.docker.internal`, usar IP del host o configurar red bridge
- Verificar conectividad desde contenedor a PostgreSQL del host en puerto 5432
- Crear base de datos `melichinkul_db` en PostgreSQL antes de ejecutar migraciones
- Backup de base de datos antes de migraciones importantes

### 2.6. Dependencias y Paquetes Laravel

**Paquetes principales:**
- **Laravel Livewire 4.x**: Componentes interactivos
- **Maatwebsite Laravel-Excel**: Exportación a Excel
- **Barryvdh Laravel-DomPDF** o **dompdf/dompdf**: Exportación a PDF
- **Spatie Laravel-Permission**: Gestión de roles y permisos (ya mencionado)
- **Laravel Horizon** (opcional): Dashboard para queues Redis
- **Laravel Telescope** (solo desarrollo): Debug y profiling
- **Laravel Debugbar** (solo desarrollo): Debug en desarrollo

**DataTables:**
- **yajra/laravel-datatables**: Integración server-side de DataTables con Laravel
- **yajra/laravel-datatables-livewire**: Integración con Livewire
- DataTables JavaScript library (CDN o npm)


## 3. CLASIFICACIÓN DE VEHÍCULOS

### 2.1. Tipología Propuesta

**Categoría 1: Vehículos Utilitarios Ligeros**
- Ejemplos: Fiat Berlingo, Peugeot Partner
- Características:
  - Uso urbano frecuente
  - Mantenimientos basados principalmente en kilometraje
  - Criticidad: Media-Baja
  - Remplazo rápido posible si falla

**Categoría 2: Camionetas Medianas y Grandes**
- Ejemplos: Nissan Navara, Ford F150
- Características:
  - Uso mixto urbano/rural
  - Mantenimientos por kilometraje o tiempo (lo que ocurra primero)
  - Algunas pueden ser vehículos gerenciales (uso menos intensivo pero más exigente en presentación)
  - Criticidad: Media
  - Capacidad de carga variable

**Categoría 3: Camiones con Grúa Pluma**
- Ejemplos: Camiones con grúa para movimiento de estanques
- Características:
  - **USO CRÍTICO**: Parálisis de este vehículo afecta operaciones comerciales directamente
  - Mantenimientos por kilometraje + horas de uso del equipo (grúa)
  - Requiere inspecciones periódicas obligatorias (normativa)
  - Criticidad: **ALTA**
  - Costos de mantenimiento superiores
  - Requiere certificaciones y documentos legales

**Categoría 4: Maquinaria Especial**
- Ejemplos: Bobcat
- Características:
  - Mantenimientos principalmente por horas de uso (horómetro), no kilometraje
  - Puede tener uso estacional o esporádico
  - Requiere inspecciones pre-uso y mantenimientos especializados
  - Criticidad: Alta (cuando se necesita, no puede fallar)
  - Consideraciones ambientales y de seguridad específicas

### 2.2. Diferencias Operativas Relevantes

| Aspecto | Utilitarios | Camionetas | Camiones Grúa | Maquinaria |
|---------|-------------|------------|---------------|------------|
| **Medición principal** | Kilometraje | Kilometraje/Tiempo | Km + Horas Grúa | Horómetro |
| **Criticidad** | Baja-Media | Media | **ALTA** | Alta (cuando se usa) |
| **Frecuencia mantención** | Alta (uso intensivo) | Media-Alta | Media (pero crítica) | Variable/Baja |
| **Inspecciones legales** | No | No | **Sí** | **Sí** |
| **Documentación requerida** | Básica obligatoria* | Básica obligatoria* | Básica + certificados especiales | Básica + certificados especiales |
| | *Todos requieren: Permiso circulación, Revisión técnica, SOAP, Análisis gases | | | |
| **Vida útil estimada** | Menor | Media | Mayor | Mayor |

### 2.3. Impacto en el Modelo de Datos

**Necesidad de flexibilidad:**
- Los vehículos deben poder tener múltiples contadores (kilometraje, horómetro, horas de grúa, etc.)
- Los tipos de mantenimiento varían según categoría
- Los umbrales de alerta son diferentes según criticidad
- Algunos vehículos requieren campos adicionales (certificados, números de serie de equipos especiales)


## 4. MODELO DE DOMINIO (ALTO NIVEL)

### 3.1. Entidades Principales

#### 3.1.1. **Vehiculo**
- Representa cada unidad de la flota (auto, camioneta, camión, maquinaria)
- Atributos clave: patente, marca, modelo, año, tipo, categoría, estado operativo
- Relacionado con: mantenimientos, alertas, contadores de uso

#### 3.1.2. **TipoVehiculo / CategoriaVehiculo**
- Clasificación estándar (Utilitario, Camioneta, Camión Grúa, Maquinaria)
- Define reglas de negocio por categoría
- Usado para determinar qué mantenimientos aplican y cómo se miden

#### 3.1.3. **Mantenimiento**
- Representa un evento de mantenimiento (realizado o programado)
- Atributos clave: tipo (preventivo/correctivo), fecha programada, fecha ejecutada, descripción, costo, proveedor/taller, técnico responsable
- Relacionado con: vehículo, tipo de mantenimiento, repuestos utilizados

#### 3.1.4. **TipoMantenimiento**
- Catálogo de servicios estándar (cambio de aceite, revisión de frenos, inspección grúa, etc.)
- Define frecuencia sugerida (cada X km o Y meses)
- Puede ser específico para ciertas categorías de vehículos

#### 3.1.5. **Alertas**
- Notificaciones generadas automáticamente o manualmente
- Tipos: por kilometraje, por tiempo, por vencimiento de certificado, por condición crítica
- Estados: pendiente, vista, cerrada
- Relacionado con: vehículo, mantenimiento asociado

#### 3.1.6. **ContadorUso**
- Registro histórico de uso del vehículo (kilometraje, horómetro, horas de grúa)
- Permite calcular próximos mantenimientos basados en uso real
- Timestamps para trazabilidad

#### 3.1.7. **Certificacion / DocumentoVehiculo**
- **Documentos legales obligatorios según legislación chilena** que deben portar todos los vehículos en circulación:
  - Permiso de Circulación (obligatorio anual)
  - Revisión Técnica (obligatoria anual)
  - SOAP - Seguro Obligatorio de Accidentes Personales (obligatorio anual)
  - Análisis de Gases / Certificado de Emisiones (según tipo de vehículo y año)
  - Certificados específicos según tipo de vehículo (ej: certificado grúa para camiones con grúa)
- Fechas de vencimiento para generar alertas automáticas
- **Copia digital escaneada obligatoria**: Cada documento debe tener archivo escaneado almacenado (PDF, JPG, PNG)
  - Soporte para documentos con anverso y reverso (2 archivos)
  - Almacenamiento permanente para acceso rápido y respaldo
  - Visualización y descarga desde el sistema
- **Crítico**: Estos documentos pueden ser solicitados por inspectores o policía en cualquier momento. Los escaneos digitales permiten acceso inmediato sin necesidad del documento físico.

#### 3.1.8. **Repuesto / Insumo**
- Catálogo de repuestos o insumos utilizados en mantenimientos
- Atributos clave: código, descripción, marca, precio de referencia, estado activo
- Relacionado con: mantenimientos (uso), compras, movimientos de inventario, stock

#### 3.1.8.1. **Proveedor**
- Proveedores de repuestos e insumos
- Atributos clave: nombre, RUT, contacto, dirección, estado activo
- Relacionado con: compras de repuestos

#### 3.1.8.2. **Compra / Orden de Compra**
- Registro de compras de repuestos a proveedores
- Atributos clave: proveedor, fecha de compra, número de factura/orden, total, estado (pendiente, recibida, cancelada)
- Relacionado con: proveedor, items de compra (repuestos comprados)

#### 3.1.8.3. **ItemCompra**
- Detalle de repuestos comprados en una orden de compra
- Atributos clave: compra, repuesto, cantidad, precio unitario, subtotal
- Relacionado con: compra, repuesto

#### 3.1.8.4. **MovimientoInventario**
- Registro de movimientos de entrada y salida de repuestos del inventario
- Tipos: entrada (compra, ajuste positivo), salida (uso en mantenimiento, ajuste negativo, vencimiento)
- Atributos clave: repuesto, tipo, cantidad, fecha, referencia (compra_id, mantenimiento_id, o manual), usuario responsable
- Relacionado con: repuesto, compra (opcional), mantenimiento (opcional), usuario

#### 3.1.8.5. **Stock**
- Stock actual de cada repuesto en inventario
- Atributos clave: repuesto, cantidad disponible, cantidad mínima (umbral de alerta), ubicación (opcional)
- Relacionado con: repuesto
- **Actualización**: Se actualiza automáticamente con cada movimiento de inventario

#### 3.1.9. **Conductor**
- Representa a un conductor que puede usar vehículos de la flota
- Atributos clave: nombre, rut (identificación), contacto, estado, **licencia de conducir**
- **Control de licencia**: Clase de licencia, fecha de vencimiento, archivo escaneado de la licencia
- Relacionado con: asignaciones a vehículos, mantenimientos correctivos (para detectar patrones)
- **Validación crítica**: No se puede asignar vehículo a conductor con licencia vencida

#### 3.1.10. **AsignacionConductor**
- Registro histórico de asignaciones conductor → vehículo
- Permite rastrear quién usó cada vehículo en cada período
- Atributos clave: conductor, vehículo, fecha_asignacion, fecha_fin, observaciones
- Relacionado con: conductor, vehículo

#### 3.1.11. **Usuario**
- Usuarios del sistema con roles y permisos
- Relacionado con: mantenimientos (responsable), alertas (notificaciones)

#### 3.1.12. **Auditoria / LogActividad**
- Registro de acciones críticas para trazabilidad
- Quién, qué, cuándo, desde dónde

### 3.2. Relaciones Clave

- **Vehiculo 1:N Mantenimiento**: Un vehículo tiene muchos mantenimientos
- **Vehiculo 1:N Alertas**: Un vehículo puede generar múltiples alertas
- **Vehiculo 1:N ContadorUso**: Historial de uso en el tiempo
- **Vehiculo 1:N Certificacion**: Múltiples documentos por vehículo
- **Vehiculo 1:N AsignacionConductor**: Un vehículo tiene historial de asignaciones de conductores
- **Vehiculo N:1 Conductor**: Un vehículo tiene un conductor asignado actualmente (relación directa)
- **Conductor 1:N AsignacionConductor**: Un conductor puede estar asignado a múltiples vehículos (en diferentes períodos)
- **Conductor 1:N Mantenimiento**: Un conductor puede estar asociado a mantenimientos correctivos (para análisis de patrones)
- **Mantenimiento N:1 TipoMantenimiento**: Cada mantenimiento es de un tipo específico
- **Mantenimiento N:M Repuesto**: Un mantenimiento puede usar múltiples repuestos
- **Repuesto 1:N MovimientoInventario**: Un repuesto tiene múltiples movimientos (entradas y salidas)
- **Repuesto 1:1 Stock**: Cada repuesto tiene un registro de stock actual
- **Proveedor 1:N Compra**: Un proveedor puede tener múltiples compras
- **Compra 1:N ItemCompra**: Una compra tiene múltiples items (repuestos)
- **ItemCompra N:1 Repuesto**: Cada item de compra corresponde a un repuesto
- **Compra 1:N MovimientoInventario**: Una compra genera movimientos de entrada al inventario
- **Mantenimiento 1:N MovimientoInventario**: Un mantenimiento puede generar movimientos de salida del inventario
- **Mantenimiento N:1 Conductor**: Un mantenimiento correctivo puede estar asociado al conductor responsable (cuando aplica)
- **Usuario 1:N Mantenimiento**: Un usuario puede ser responsable de varios mantenimientos

### 3.3. Conceptos Importantes

**Mantenimiento Preventivo:**
- Basado en tiempo o uso programado
- Objetivo: evitar fallas
- Ejemplo: "Cambio de aceite cada 10,000 km"

**Mantenimiento Correctivo:**
- Realizado después de una falla o incidencia
- Objetivo: reparar
- Debe tener asociada una incidencia o motivo

**Alerta:**
- Notificación de que algo requiere atención
- Puede ser informativa (próximo mantenimiento en 15 días) o crítica (mantenimiento vencido hace 5 días)

**Criticidad:**
- Nivel de impacto operativo si el vehículo falla
- Determina prioridad de alertas y frecuencia de revisiones

**Contador de Uso:**
- Medición que avanza con el uso del vehículo
- Puede ser: odómetro (km), horómetro (horas motor), horas de equipo específico

**Asignación de Conductor:**
- Registro de quién fue responsable del vehículo en un período específico
- Permite análisis de patrones: detectar si ciertos conductores generan más mantenimientos correctivos
- Importante para trazabilidad y responsabilidad operativa


## 5. MODELO DE DATOS (PROPUESTA)

### 4.1. Tablas Principales

#### 4.1.1. **vehiculos**
```sql
- id (bigint, primary key)
- patente (string, unique, index) -- Patente chilena (formato actual autos: 4 letras + 2 dígitos, solo letras permitidas: B,C,D,F,G,H,J,K,L,P,R,S,T,V,W,X,Y,Z; motos: 3 letras + 2 dígitos). Normalizar a mayúsculas y sin espacios.
- marca (string) -- Ej: "Fiat", "Nissan"
- modelo (string) -- Ej: "Berlingo", "Navara"
- año (integer) -- Ej: 2020
- numero_motor (string, nullable, index) -- Para identificación única
- numero_chasis (string, nullable, index)
- categoria_id (foreign key -> categorias_vehiculos)
- tipo_combustible (enum: 'gasolina', 'diesel', 'electrico', 'hibrido', 'gnv') -- Dato característico del vehículo, NO para control de consumo/gastos (se usa sistema Copec)
- estado (enum: 'activo', 'en_mantenimiento', 'inactivo', 'baja') -- default: 'activo'
- conductor_actual_id (foreign key -> conductores, nullable, index) -- Conductor asignado actualmente
- fecha_incorporacion (date) -- Cuándo ingresó a la flota
- valor_compra (bigint, nullable) -- Para depreciación futura (en CLP, sin decimales)
- observaciones (text, nullable)
- created_at, updated_at (timestamps)
- deleted_at (timestamp, nullable) -- Soft deletes
```

**Índices recomendados:**
- `idx_vehiculos_patente` (unique)
- `idx_vehiculos_categoria` 
- `idx_vehiculos_estado`
- `idx_vehiculos_conductor_actual` (conductor_actual_id)

#### 4.1.2. **categorias_vehiculos**
```sql
- id (bigint, primary key)
- nombre (string, unique) -- "Utilitario Ligeros", "Camionetas", "Camiones Grúa", "Maquinaria Especial"
- slug (string, unique) -- "utilitarios", "camionetas", "camiones-grua", "maquinaria"
- descripcion (text, nullable)
- contador_principal (enum: 'kilometraje', 'horometro', 'mixto') -- Cómo se mide el uso principal
- criticidad_default (enum: 'baja', 'media', 'alta') -- default: 'media'
- requiere_certificaciones (boolean, default: true) -- TODOS los vehículos requieren documentos básicos legales en Chile
- requiere_certificaciones_especiales (boolean, default: false) -- Si requiere certificados adicionales (ej: grúa, transporte peligroso)
- activo (boolean, default: true)
- created_at, updated_at
```

**Valores iniciales sugeridos:**
1. Utilitarios Ligeros (kilometraje, media, sí documentos básicos obligatorios)
2. Camionetas (kilometraje, media, sí documentos básicos obligatorios)
3. Camiones Grúa (mixto, alta, sí documentos básicos + certificados especiales)
4. Maquinaria Especial (horómetro, alta, sí documentos básicos + certificados especiales)

**Nota sobre documentos:** TODOS los vehículos en Chile requieren documentos legales básicos (Permiso de Circulación, Revisión Técnica, SOAP, Análisis de Gases). Los vehículos especiales requieren documentos adicionales.

#### 4.1.3. **contadores_uso**
```sql
- id (bigint, primary key)
- vehiculo_id (foreign key -> vehiculos, index)
- tipo_contador (enum: 'kilometraje', 'horometro', 'horas_grua', 'horas_equipo') -- Flexibilidad
- valor (decimal(10,2)) -- El valor actual del contador
- fecha_registro (date, index) -- Cuándo se registró este valor
- observaciones (text, nullable) -- Ej: "Registrado en taller", "Lectura manual"
- registrado_por (foreign key -> users, nullable)
- created_at, updated_at
```

**Estrategia:**
- Registrar un nuevo contador cada vez que cambia significativamente (cada mantenimiento, cada mes, etc.)
- El último registro por vehículo+tipo es el valor actual
- Permite histórico completo para análisis

**Índices:**
- `idx_contadores_vehiculo_tipo_fecha` (vehiculo_id, tipo_contador, fecha_registro DESC)
- Última lectura rápida: `SELECT ... WHERE vehiculo_id = X AND tipo_contador = 'kilometraje' ORDER BY fecha_registro DESC LIMIT 1`

#### 4.1.4. **tipos_mantenimiento**
```sql
- id (bigint, primary key)
- nombre (string) -- "Cambio de aceite", "Revisión de frenos", "Inspección Grúa"
- descripcion (text, nullable)
- categoria_vehiculo_id (foreign key -> categorias_vehiculos, nullable) -- NULL = aplica a todas
- tipo (enum: 'preventivo', 'correctivo', 'inspeccion') -- default: 'preventivo'
- frecuencia_kilometraje (integer, nullable) -- Cada cuántos km (NULL si no aplica)
- frecuencia_meses (integer, nullable) -- Cada cuántos meses (NULL si no aplica)
- frecuencia_horas (integer, nullable) -- Cada cuántas horas de uso (NULL si no aplica)
- criticidad (enum: 'baja', 'media', 'alta') -- Para determinar prioridad de alertas
- activo (boolean, default: true)
- created_at, updated_at
```

**Ejemplos:**
- "Cambio de aceite" → preventivo, 10000 km, 6 meses, baja
- "Inspección Grúa" → inspección, NULL, 3 meses, alta (para camiones grúa)

#### 4.1.5. **mantenimientos**
```sql
- id (bigint, primary key)
- vehiculo_id (foreign key -> vehiculos, index)
- tipo_mantenimiento_id (foreign key -> tipos_mantenimiento)
- tipo (enum: 'preventivo', 'correctivo', 'inspeccion') -- Redundante pero útil para queries
- estado (enum: 'programado', 'en_revision', 'esperando_repuestos', 'pendiente_aprobacion', 'en_proceso', 'pausado', 'completado', 'rechazado', 'cancelado') -- default: 'programado'
- fecha_programada (date, index) -- Cuándo debería ejecutarse
- fecha_inicio (date, nullable) -- Cuándo se inició
- fecha_fin (date, nullable, index) -- Cuándo se completó (para historial)
- kilometraje_en_mantenimiento (decimal(10,2), nullable) -- Km al momento del mantenimiento
- horometro_en_mantenimiento (decimal(10,2), nullable) -- Horas al momento del mantenimiento
- descripcion_trabajo (text) -- Qué se hizo
- observaciones (text, nullable) -- Notas adicionales
- costo_repuestos (bigint, default: 0) -- Costo de repuestos e insumos utilizados en CLP (sin decimales)
- horas_trabajadas (decimal(5,2), nullable) -- Horas de mano de obra trabajadas (puede ser NULL)
- costo_mano_obra (bigint, default: 0) -- Costo de mano de obra en CLP (sin decimales) - calculado o manual
- costo_total (bigint, default: 0) -- Costo total calculado: repuestos + mano_obra (calculado automáticamente)
- umbral_aprobacion (bigint, nullable) -- Umbral configurable: si costo_total > umbral, requiere aprobación
- requiere_aprobacion (boolean, default: false) -- Si requiere aprobación por costo alto
- moneda (string, default: 'CLP') -- Para futuro multi-moneda
- motivo_ingreso (text, nullable) -- Motivo por el cual el vehículo ingresó al taller (especialmente para correctivos)
- plantilla_id (foreign key -> plantillas_mantenimiento, nullable) -- Si se creó desde una plantilla
- taller_proveedor (string, nullable) -- Nombre del taller o "Taller Interno"
- tecnico_responsable_id (foreign key -> users, nullable) -- Quién lo hizo
- aprobado_por_id (foreign key -> users, nullable) -- Quién lo aprobó/verificó
- conductor_asignado_id (foreign key -> conductores, nullable, index) -- Conductor que trajo el vehículo al taller (seleccionado manualmente al recibir)
- incidencia_previo (text, nullable) -- Si es correctivo, qué falló
- proxima_fecha_sugerida (date, nullable) -- Calculada automáticamente
- created_at, updated_at
- deleted_at (soft deletes)
```

**Índices:**
- `idx_mantenimientos_vehiculo_estado` (vehiculo_id, estado)
- `idx_mantenimientos_fecha_programada` (fecha_programada) -- Para alertas
- `idx_mantenimientos_fecha_fin` (fecha_fin DESC) -- Para historial reciente
- `idx_mantenimientos_conductor` (conductor_asignado_id) -- Para análisis de patrones por conductor

#### 4.1.6. **solicitudes_repuestos**
```sql
- id (bigint, primary key)
- mantenimiento_id (foreign key -> mantenimientos, index)
- estado (enum: 'pendiente', 'en_compra', 'recibida', 'entregada', 'cancelada') -- default: 'pendiente'
- solicitado_por_id (foreign key -> users, index) -- Mecánico que solicita
- recibido_por_id (foreign key -> users, nullable) -- Administrativo que recibe la solicitud
- fecha_solicitud (timestamp, index) -- Cuándo se solicitó
- fecha_entrega (timestamp, nullable) -- Cuándo se entregaron los repuestos al mecánico
- observaciones (text, nullable) -- Notas de la solicitud
- created_at, updated_at
```

**Nota:** Esta tabla permite rastrear el flujo de solicitud de repuestos desde el mecánico hasta la entrega.

#### 4.1.6.1. **solicitud_repuesto_items**
```sql
- id (bigint, primary key)
- solicitud_repuesto_id (foreign key -> solicitudes_repuestos, index, on delete cascade)
- repuesto_id (foreign key -> repuestos, nullable, index) -- NULL si no está en catálogo
- codigo_repuesto (string, nullable) -- Si no está en catálogo, código manual
- descripcion (string) -- Descripción del repuesto solicitado
- cantidad_solicitada (integer) -- Cantidad solicitada
- cantidad_entregada (integer, default: 0) -- Cantidad realmente entregada (puede ser diferente)
- observaciones (text, nullable)
- created_at, updated_at
```

#### 4.1.7. **mantenimiento_repuestos** (tabla pivot)
```sql
- id (bigint, primary key)
- mantenimiento_id (foreign key -> mantenimientos, on delete cascade)
- repuesto_id (foreign key -> repuestos, nullable) -- NULL si no está en catálogo
- codigo_repuesto (string, nullable) -- Si no está en catálogo, código manual
- descripcion (string) -- Descripción del repuesto usado
- cantidad (decimal(8,2), default: 1) -- Cantidad puede tener decimales (ej: 2.5 litros)
- precio_unitario (bigint) -- Precio unitario en CLP (sin decimales)
- subtotal (bigint) -- cantidad * precio_unitario (redondeado según reglas chilenas)
- created_at, updated_at
```

**Nota:** Permite registrar repuestos sin necesidad de tener catálogo completo inicialmente. Los repuestos se registran cuando el mecánico los usa realmente en la reparación.

**Nota sobre Insumos:** Los insumos (aceites, líquidos, etc.) se gestionan como repuestos en el inventario. No hay tabla separada para insumos, se registran en `mantenimiento_repuestos` igual que los repuestos. La diferencia es solo conceptual (insumos son consumibles, repuestos son piezas).

#### 4.1.8. **repuestos** (catálogo)
```sql
- id (bigint, primary key)
- codigo (string, unique, index) -- Código interno o del fabricante
- descripcion (string)
- marca (string, nullable)
- categoria (enum: 'repuesto', 'insumo', 'herramienta', 'consumible') -- default: 'repuesto'
- precio_referencia (bigint, nullable) -- Precio de referencia en CLP (sin decimales)
- tiene_vencimiento (boolean, default: false) -- Si requiere control de fecha de vencimiento
- activo (boolean, default: true)
- created_at, updated_at
```

#### 4.1.7.1. **proveedores**
```sql
- id (bigint, primary key)
- nombre (string, index)
- rut (string, unique, index) -- RUT del proveedor (validación chilena)
- contacto_nombre (string, nullable) -- Nombre de contacto
- telefono (string, nullable)
- email (string, nullable)
- direccion (text, nullable)
- activo (boolean, default: true)
- created_at, updated_at
```

#### 4.1.7.2. **compras**
```sql
- id (bigint, primary key)
- proveedor_id (foreign key -> proveedores, index)
- numero_factura (string, nullable, index) -- Número de factura u orden de compra
- numero_orden (string, nullable, index) -- Número de orden interna
- fecha_compra (date, index) -- Fecha de la compra
- fecha_recepcion (date, nullable) -- Fecha en que se recibió físicamente
- total (bigint, default: 0) -- Total en CLP (sin decimales)
- estado (enum: 'pendiente', 'recibida', 'cancelada', 'parcial') -- default: 'pendiente'
- observaciones (text, nullable)
- creado_por_id (foreign key -> users, index) -- Usuario que creó la compra (administrativo)
- recibido_por_id (foreign key -> users, nullable) -- Usuario que recibió la compra
- created_at, updated_at
- deleted_at (soft deletes)
```

#### 4.1.7.3. **compra_items**
```sql
- id (bigint, primary key)
- compra_id (foreign key -> compras, index)
- repuesto_id (foreign key -> repuestos, index)
- cantidad (integer) -- Cantidad comprada
- precio_unitario (bigint) -- Precio unitario en CLP (sin decimales)
- subtotal (bigint) -- cantidad * precio_unitario (calculado, redondeado según reglas chilenas)
- created_at, updated_at
```

#### 4.1.7.4. **movimientos_inventario**
```sql
- id (bigint, primary key)
- repuesto_id (foreign key -> repuestos, index)
- tipo (enum: 'entrada_compra', 'entrada_ajuste', 'salida_mantenimiento', 'salida_ajuste', 'salida_vencimiento') -- index
- cantidad (integer) -- Positivo para entradas, negativo para salidas (o siempre positivo con tipo)
- fecha (date, index) -- Fecha del movimiento
- compra_id (foreign key -> compras, nullable, index) -- Si es entrada por compra
- mantenimiento_id (foreign key -> mantenimientos, nullable, index) -- Si es salida por mantenimiento
- referencia_manual (string, nullable) -- Referencia manual si no hay compra/mantenimiento
- observaciones (text, nullable)
- usuario_id (foreign key -> users, index) -- Usuario responsable del movimiento
- created_at, updated_at
```

**Nota:** Para salidas, la cantidad se almacena como valor positivo, pero el tipo indica que es salida. Alternativamente, se puede usar cantidad negativa para salidas y positiva para entradas.

#### 4.1.7.5. **stock**
```sql
- id (bigint, primary key)
- repuesto_id (foreign key -> repuestos, unique, index) -- Un registro por repuesto
- cantidad_disponible (integer, default: 0) -- Stock actual
- cantidad_minima (integer, default: 0) -- Umbral de alerta (cuando stock < cantidad_minima)
- ubicacion (string, nullable) -- Ubicación física del repuesto (estante, bodega, etc.)
- created_at, updated_at
```

**Nota:** Esta tabla se actualiza automáticamente mediante triggers o eventos cuando hay movimientos de inventario. La cantidad_disponible se calcula sumando todas las entradas y restando todas las salidas.

**Futuro:** Considerar tabla `ubicaciones_stock` para múltiples bodegas/ubicaciones si la operación crece.

#### 4.1.7.6. **lotes_repuestos** (futuro - para control de vencimientos)
```sql
- id (bigint, primary key)
- repuesto_id (foreign key -> repuestos, index)
- numero_lote (string, nullable) -- Número de lote del fabricante
- fecha_vencimiento (date, nullable, index) -- Fecha de vencimiento del lote
- cantidad (integer) -- Cantidad en este lote
- ubicacion (string, nullable)
- created_at, updated_at
```

**Nota:** Para repuestos/insumos con `tiene_vencimiento = true`. Permite control FIFO (First In, First Out) y alertas de vencimiento.

#### 4.1.9. **plantillas_mantenimiento**
```sql
- id (bigint, primary key)
- nombre (string) -- Ej: "Cambio de aceite estándar", "Revisión completa grúa"
- descripcion (text, nullable)
- tipo_mantenimiento_id (foreign key -> tipos_mantenimiento, nullable)
- categoria_vehiculo_id (foreign key -> categorias_vehiculos, nullable) -- NULL = aplica a todas
- activo (boolean, default: true)
- created_at, updated_at
```

#### 4.1.9.1. **plantilla_repuestos**
```sql
- id (bigint, primary key)
- plantilla_mantenimiento_id (foreign key -> plantillas_mantenimiento, on delete cascade)
- repuesto_id (foreign key -> repuestos, index)
- cantidad (decimal(8,2), default: 1) -- Cantidad sugerida
- obligatorio (boolean, default: true) -- Si es obligatorio o opcional en la plantilla
- created_at, updated_at
```

**Nota:** Permite crear "recetas" de mantenimientos frecuentes con repuestos predefinidos. Al crear un mantenimiento desde plantilla, se pre-llenan los repuestos sugeridos.

#### 4.1.10. **checklist_mantenimiento**
```sql
- id (bigint, primary key)
- tipo_mantenimiento_id (foreign key -> tipos_mantenimiento, nullable) -- NULL = checklist general
- item (string) -- Descripción del item del checklist
- orden (integer, default: 0) -- Orden de visualización
- obligatorio (boolean, default: true) -- Si es obligatorio completar
- activo (boolean, default: true)
- created_at, updated_at
```

#### 4.1.10.1. **mantenimiento_checklist_items**
```sql
- id (bigint, primary key)
- mantenimiento_id (foreign key -> mantenimientos, on delete cascade, index)
- checklist_item_id (foreign key -> checklist_mantenimiento, nullable) -- NULL si es item personalizado
- item (string) -- Descripción del item (puede ser del checklist o personalizado)
- completado (boolean, default: false)
- completado_por_id (foreign key -> users, nullable)
- fecha_completado (timestamp, nullable)
- observaciones (text, nullable)
- created_at, updated_at
```

**Nota:** Permite que el mecánico marque items completados durante el mantenimiento. Asegura que no se olviden pasos críticos.

#### 4.1.11. **evidencia_mantenimiento**
```sql
- id (bigint, primary key)
- mantenimiento_id (foreign key -> mantenimientos, on delete cascade, index)
- tipo (enum: 'foto', 'documento', 'video') -- default: 'foto'
- descripcion (string, nullable) -- Descripción de la evidencia
- archivo_path (string) -- Path al archivo almacenado
- subido_por_id (foreign key -> users, index)
- created_at, updated_at
```

**Nota:** Permite subir fotos, documentos o videos como evidencia del trabajo realizado o del problema encontrado. Útil para seguros, auditorías y seguimiento.

#### 4.1.12. **mantenimiento_costos_historial**
```sql
- id (bigint, primary key)
- mantenimiento_id (foreign key -> mantenimientos, index)
- costo_repuestos_anterior (bigint, nullable)
- costo_repuestos_nuevo (bigint, nullable)
- costo_mano_obra_anterior (bigint, nullable)
- costo_mano_obra_nuevo (bigint, nullable)
- costo_total_anterior (bigint, nullable)
- costo_total_nuevo (bigint, nullable)
- motivo_cambio (text, nullable) -- Justificación del cambio
- modificado_por_id (foreign key -> users, index)
- created_at
```

**Nota:** Auditoría de cambios en costos después de completar un mantenimiento. Permite rastrear quién modificó qué y cuándo.

#### 4.1.13. **alertas**
```sql
- id (bigint, primary key)
- vehiculo_id (foreign key -> vehiculos, index)
- mantenimiento_id (foreign key -> mantenimientos, nullable) -- Si está asociada a un mantenimiento
- tipo (enum: 'proximo_mantenimiento', 'mantenimiento_vencido', 'certificado_por_vencer', 'certificado_vencido', 'licencia_por_vencer', 'licencia_vencida', 'stock_bajo', 'stock_agotado', 'condicion_critica', 'manual')
- severidad (enum: 'informativa', 'advertencia', 'critica') -- default: 'informativa'
- titulo (string)
- mensaje (text)
- fecha_generada (timestamp, index) -- Cuándo se creó la alerta
- fecha_limite (date, nullable, index) -- Cuándo debe resolverse
- estado (enum: 'pendiente', 'vista', 'cerrada') -- default: 'pendiente'
- cerrada_por_id (foreign key -> users, nullable)
- fecha_cierre (timestamp, nullable)
- metadata (jsonb, nullable) -- Datos adicionales flexibles (km actual, km objetivo, etc.)
- created_at, updated_at
```

**Índices:**
- `idx_alertas_vehiculo_estado` (vehiculo_id, estado)
- `idx_alertas_severidad_estado` (severidad, estado) -- Para dashboard crítico
- `idx_alertas_fecha_limite` (fecha_limite) -- Para notificaciones

#### 4.1.14. **certificaciones**
```sql
- id (bigint, primary key)
- vehiculo_id (foreign key -> vehiculos, index)
- tipo (enum: 
    'permiso_circulacion',      -- Permiso de Circulación (obligatorio anual)
    'revision_tecnica',         -- Revisión Técnica Vehicular (obligatoria anual)
    'soap',                     -- Seguro Obligatorio de Accidentes Personales (obligatorio anual)
    'analisis_gases',           -- Análisis de Gases / Certificado de Emisiones (obligatorio según tipo/año)
    'seguro_adicional',         -- Seguro adicional no obligatorio (ej: todo riesgo)
    'certificado_grua',         -- Certificado de grúa (para camiones con grúa)
    'certificado_carga',        -- Certificado de carga/peso (para vehículos de carga)
    'certificado_transporte',   -- Certificados especiales de transporte (ej: transporte de materiales peligrosos)
    'otro'                      -- Otros documentos legales o técnicos
  )
- nombre (string) -- "Permiso de Circulación 2024", "Revisión Técnica", "SOAP", etc.
- numero_certificado (string, nullable) -- Número del documento (ej: número de póliza, número de certificado)
- fecha_emision (date, nullable) -- Fecha de emisión del documento
- fecha_vencimiento (date, index) -- CLAVE para alertas - Fecha de vencimiento (CRÍTICO para documentos obligatorios)
- proveedor (string, nullable) -- Compañía de seguros, municipalidad, entidad emisora, etc.
- costo (bigint, nullable) -- Costo de renovación/emisión en CLP (sin decimales)
- archivo_adjunto (string, nullable) -- Path al archivo escaneado del documento (ej: PDF, JPG, PNG)
- archivo_adjunto_2 (string, nullable) -- Path opcional a segundo archivo si el documento tiene anverso y reverso
- observaciones (text, nullable) -- Notas adicionales sobre el documento
- obligatorio (boolean, default: true) -- Si es documento obligatorio por ley (todos los básicos lo son)
- activo (boolean, default: true) -- Si el documento está vigente (para historial mantener versiones anteriores)
- created_at, updated_at
```

**Documentos obligatorios básicos para TODOS los vehículos en Chile:**
- **Permiso de Circulación**: Obligatorio anual, emitido por municipalidad
- **Revisión Técnica**: Obligatoria anual, realizada en estaciones autorizadas
- **SOAP**: Seguro Obligatorio de Accidentes Personales, obligatorio anual
- **Análisis de Gases**: Obligatorio según tipo de vehículo, año y normativa vigente

**Importancia crítica:**
- Estos documentos pueden ser solicitados en cualquier momento por inspectores o policía
- Un vehículo sin documentos vigentes NO puede circular legalmente
- Las multas por documentos vencidos o faltantes son significativas
- Un vehículo sin documentos puede ser retenido o inmovilizado

**Almacenamiento de copias digitales (escaneos):**
- **CRÍTICO**: Todos los documentos legales deben tener copia digital escaneada almacenada en el sistema
- Formato: PDF, JPG o PNG (preferir PDF para mejor calidad)
- Los documentos que tienen anverso y reverso deben escanearse ambos lados
- Al renovar un documento, se debe mantener el escaneo del documento anterior en historial y agregar el nuevo
- **Beneficios:**
  - Acceso rápido a documentos desde cualquier lugar (no depender de documentos físicos en el vehículo)
  - Respuesta rápida si son solicitados por inspectores o policía
  - Respaldo en caso de pérdida o deterioro del documento físico
  - Verificación inmediata de datos (números, fechas) sin necesidad del documento original
  - Auditoría y cumplimiento normativo

**Índices:**
- `idx_certificaciones_vencimiento` (fecha_vencimiento) -- Para alertas de vencimiento

#### 4.1.15. **conductores**
```sql
- id (bigint, primary key)
- rut (string, unique, index) -- RUT chileno válido (formato con dígito verificador K o 0-9, algoritmo Módulo 11), ej: "12345678-9"
- nombre_completo (string) -- "Juan Pérez"
- telefono (string, nullable)
- email (string, nullable)
- licencia_numero (string, nullable) -- Número de la licencia de conducir
- licencia_clase (string, nullable) -- Clase de licencia (A1, A2, B, C, D, E, F, etc.)
- licencia_fecha_emision (date, nullable) -- Fecha de emisión de la licencia
- licencia_vencimiento (date, nullable, index) -- Fecha de vencimiento de la licencia (CRÍTICO para alertas)
- licencia_archivo (string, nullable) -- Path al archivo escaneado de la licencia (front y reverso)
- activo (boolean, default: true) -- Si está activo en la empresa
- observaciones (text, nullable) -- Notas sobre el conductor
- created_at, updated_at
- deleted_at (timestamp, nullable) -- Soft deletes
```

**Índices:**
- `idx_conductores_rut` (unique)
- `idx_conductores_activo` (activo) -- Para filtrar conductores activos
- `idx_conductores_licencia_vencimiento` (licencia_vencimiento) -- Para alertas de vencimiento

**Importante:**
- **Control de licencia es CRÍTICO**: Un conductor con licencia vencida NO puede ser asignado a vehículos
- La licencia puede ser solicitada en cualquier momento por inspectores o policía
- Si un conductor con licencia vencida maneja un vehículo, puede recibir multas e infracciones
- Se debe mantener escaneo actualizado de la licencia (anverso y reverso si es necesario)

**Reglas de negocio:**
- Al asignar conductor a vehículo, sistema debe validar que licencia esté vigente
- Sistema debe generar alertas automáticas cuando licencia esté por vencer o vencida
- No permitir asignación si licencia está vencida (mostrar error explicativo)

#### 4.1.16. **asignaciones_conductores**
```sql
- id (bigint, primary key)
- conductor_id (foreign key -> conductores, index)
- vehiculo_id (foreign key -> vehiculos, index)
- fecha_asignacion (date, index) -- Cuándo se asignó el conductor
- fecha_fin (date, nullable, index) -- Cuándo terminó la asignación (NULL = asignación activa)
- asignado_por_id (foreign key -> users, nullable) -- Quién hizo la asignación
- motivo_fin (enum: 'renovacion', 'cambio_conductor', 'fin_temporal', 'otro', nullable) -- Por qué terminó
- observaciones (text, nullable) -- Notas sobre la asignación
- created_at, updated_at
```

**Estrategia:**
- Cuando se asigna un conductor a un vehículo:
  1. Se cierra la asignación anterior (si existe) con fecha_fin = fecha_asignacion nueva
  2. Se crea nueva asignación con fecha_fin = NULL (activa)
  3. Se actualiza conductor_actual_id en tabla vehiculos
- Esto mantiene historial completo de todas las asignaciones
- Permite consultar quién manejaba un vehículo en una fecha específica

**Índices:**
- `idx_asignaciones_conductor_vehiculo` (conductor_id, vehiculo_id)
- `idx_asignaciones_vehiculo_fecha` (vehiculo_id, fecha_asignacion DESC) -- Para obtener asignación actual
- `idx_asignaciones_activas` (vehiculo_id, fecha_fin) WHERE fecha_fin IS NULL -- Para asignaciones activas

**Reglas de negocio:**
- Un vehículo solo puede tener UNA asignación activa a la vez (fecha_fin IS NULL)
- Un conductor puede tener múltiples asignaciones activas (a diferentes vehículos) si aplica
- Al asignar nuevo conductor, se debe cerrar asignación anterior automáticamente

#### 4.1.12. **usuarios** (extensión de Laravel Auth)
```sql
- Usar tabla users estándar de Laravel
- Agregar campos adicionales:
  - rol (enum: 'administrador', 'supervisor', 'administrativo', 'tecnico', 'visualizador') -- default: 'visualizador'
  - nombre_completo (string)
  - email_notificaciones (boolean, default: true) -- Si recibe emails automáticos
  - telefono (string, nullable)
  - activo (boolean, default: true)
```

#### 4.1.17. **auditoria** (logs de acciones críticas)
```sql
- id (bigint, primary key)
- usuario_id (foreign key -> users, nullable) -- NULL si fue sistema
- accion (string) -- "crear_vehiculo", "cerrar_alerta", "eliminar_mantenimiento"
- modelo (string) -- "Vehiculo", "Mantenimiento", etc.
- modelo_id (bigint, nullable) -- ID del registro afectado
- descripcion (text) -- Qué se hizo
- datos_anteriores (jsonb, nullable) -- Snapshot antes del cambio (para auditoría)
- datos_nuevos (jsonb, nullable) -- Snapshot después del cambio
- ip_address (string, nullable)
- user_agent (string, nullable)
- created_at (timestamp, index)
```

**Índices:**
- `idx_auditoria_usuario_fecha` (usuario_id, created_at DESC)
- `idx_auditoria_modelo` (modelo, modelo_id)

### 4.2. Consideraciones PostgreSQL

**Tipos de datos recomendados:**
- **Valores monetarios (CLP - Pesos Chilenos)**: `BIGINT` (enteros, SIN decimales)
  - **Regla importante**: En Chile no se usan decimales para manejo de dinero
  - Todos los precios, costos y valores monetarios se almacenan como enteros
  - **Redondeo chileno**: Si un precio termina en 5 o más, se redondea al siguiente entero. Si termina en 4 o menos, se baja al anterior.
    - Ejemplos: $1.234 → $1.234 (sin cambio), $1.235 → $1.240, $1.236 → $1.240, $1.239 → $1.240
    - Ejemplos: $1.240 → $1.240, $1.241 → $1.240, $1.244 → $1.240, $1.245 → $1.250
  - El sistema debe implementar lógica de redondeo automática al ingresar precios/costos
- **Valores numéricos no monetarios**: 
  - `DECIMAL(10,2)` para contadores (kilometraje, horómetro) que pueden tener decimales
  - `DECIMAL(8,2)` para cantidades (ej: litros de aceite) que pueden tener decimales
- **Fechas**: `DATE` para fechas simples, `TIMESTAMP` para timestamps exactos
- **Enums**: Usar `ENUM` de PostgreSQL o strings con check constraints (Laravel lo maneja con enums nativos)
- **JSONB**: Para metadata flexible en alertas (permitir evolución sin migraciones)

**Constraints importantes:**
- `CHECK (fecha_fin >= fecha_inicio)` en mantenimientos
- `CHECK (fecha_vencimiento > fecha_emision)` en certificaciones
- `CHECK (licencia_vencimiento > licencia_fecha_emision)` en conductores (si ambas están presentes)
- `CHECK (valor >= 0)` en contadores
- `CHECK (costo_total >= 0)` en mantenimientos
- `CHECK (precio_unitario >= 0)` en repuestos
- `CHECK (subtotal >= 0)` en repuestos
- Todos los valores monetarios deben ser enteros no negativos
- **Validación de patente chilena** (aplicación/DB): Formato autos 4 letras + 2 dígitos, motos 3 letras + 2 dígitos, letras permitidas (B,C,D,F,G,H,J,K,L,P,R,S,T,V,W,X,Y,Z), mayúsculas, sin espacios.
- **Validación de RUT chileno** (aplicación): Formato con dígito verificador K/0-9, algoritmo Módulo 11 para validar DV, almacenar normalizado sin puntos y con guion.
- Foreign keys con `ON DELETE RESTRICT` o `CASCADE` según el caso

**Soft deletes:**
- Usar `deleted_at` en tablas críticas (vehiculos, mantenimientos) para mantener historial
- No usar en tablas de log o transaccionales puras

### 4.3. Integridad y Trazabilidad

**Reglas de negocio a nivel DB:**
1. No se puede eliminar un vehículo con mantenimientos activos
2. Un mantenimiento programado debe tener fecha_programada
3. Un mantenimiento completado debe tener fecha_fin
4. Las alertas críticas requieren acción (no pueden cerrarse automáticamente)
5. Un vehículo solo puede tener UNA asignación de conductor activa a la vez (fecha_fin IS NULL)
6. Al asignar nuevo conductor, se debe cerrar automáticamente la asignación anterior
7. El campo conductor_actual_id en vehiculos debe estar sincronizado con asignaciones_conductores activa
8. **Documentos obligatorios vencidos**: Sistema debe generar alerta CRÍTICA inmediatamente al vencer (día 0)
9. **Validación de documentos**: Un vehículo con documentos obligatorios vencidos debe estar marcado para no circular
10. **Licencias de conductores**: Un conductor con licencia vencida NO puede ser asignado a vehículos (validación obligatoria)
11. **Alertas de licencias**: Sistema debe generar alertas automáticas para licencias por vencer y vencidas
12. **Redondeo monetario chileno**: Todos los valores monetarios deben aplicarse redondeo antes de almacenar
    - Si termina en 5 o más → redondea al siguiente entero
    - Si termina en 4 o menos → baja al anterior entero
    - Se aplica automáticamente en: subtotales, totales, precios unitarios al ingresar

**Triggers (opcional, futuro):**
- Actualizar contador actual automáticamente al insertar nuevo registro
- Generar alertas automáticas al completar mantenimiento si se acerca próximo


## 6. GESTIÓN DE MANTENIMIENTOS

**Nota importante:** El mantenimiento de vehículos incluye no solo el mantenimiento mecánico, sino también la **gestión de documentos legales obligatorios** requeridos por la legislación chilena. Estos documentos son parte integral del mantenimiento operativo y deben ser monitoreados constantemente.

### 5.1. Tipos de Mantenimiento

#### 5.1.1. **Mantenimiento Preventivo**
**Definición:** Trabajo programado basado en tiempo o uso para evitar fallas.

**Características:**
- Se programa automáticamente o manualmente
- Basado en umbrales (km, meses, horas)
- Puede ser recurrente (cada X km/meses)
- Ejemplos: cambio de aceite, revisión de frenos, cambio de filtros

**Flujo:**
1. Sistema calcula próximo mantenimiento basado en último mantenimiento del mismo tipo + frecuencia
2. Se genera alerta cuando se acerca la fecha/km
3. Supervisor programa la fecha
4. Mecánico ejecuta y registra
5. Se actualiza contador de uso al momento del mantenimiento
6. Sistema recalcula próximo mantenimiento

#### 5.1.2. **Mantenimiento Correctivo**
**Definición:** Reparación después de una falla o incidencia.

**Características:**
- No es recurrente (se origina por incidencia)
- Debe tener asociada descripción de la falla
- Puede ser de emergencia (crítico) o programado (falla menor)
- Ejemplos: reparación de motor, cambio de transmisión, reparación de grúa

**Flujo:**
1. Se reporta incidencia (manual o automático)
2. Se crea mantenimiento correctivo asociado
3. **Se asocia conductor asignado al vehículo** (para trazabilidad y análisis de patrones)
4. Se programa según urgencia
5. Se ejecuta y registra
6. Puede generar mantenimientos preventivos adicionales si se detectó algo

**Importante:** Los mantenimientos correctivos deben registrar el conductor asignado al momento de la incidencia. Esto permite:
- Detectar conductores con malas prácticas de manejo
- Analizar patrones: ¿ciertos conductores generan más mantenimientos correctivos?
- Responsabilidad y trazabilidad operativa

#### 5.1.3. **Inspección**
**Definición:** Verificación de condiciones sin necesariamente hacer trabajo.

**Características:**
- Puede ser obligatoria por normativa (grúas, maquinaria)
- Puede detectar necesidad de mantenimiento correctivo
- Ejemplos: inspección pre-operacional, revisión técnica obligatoria, inspección grúa

**Flujo:**
1. Se programa por tiempo o normativa
2. Técnico realiza inspección
3. Registra condiciones encontradas
4. Puede generar mantenimientos correctivos derivados

### 5.2. Cómo se Programan

**Métodos de programación:**

1. **Automática** (preventivos):
   - Sistema calcula basado en último mantenimiento + frecuencia
   - Se programa automáticamente cuando se completa un mantenimiento
   - El supervisor puede ajustar fecha según disponibilidad

2. **Manual** (todos los tipos):
   - Usuario crea manualmente basado en necesidad
   - Puede copiar desde tipo de mantenimiento existente
   - Útil para correctivos o inspecciones especiales

3. **Desde alerta**:
   - Cuando se genera alerta de mantenimiento próximo/vencido
   - Se puede crear mantenimiento directamente desde la alerta

### 5.3. Cómo se Registran

**Proceso de registro:**

1. **Inicio del mantenimiento:**
   - Técnico/mecánico cambia estado a "en_proceso"
   - Se registra fecha_inicio
   - Se captura contador actual (km/horas)

2. **Ejecución:**
   - Se registra descripción del trabajo realizado
   - Se anotan repuestos utilizados (opcional pero recomendado)
   - Se registran observaciones/condiciones encontradas
   - Se capturan fotos si es necesario (futuro: adjuntos)

3. **Cierre:**
   - Se registra fecha_fin
   - Se actualiza contador final (km/horas después del trabajo)
   - Se registra costo total
   - Se marca como completado
   - Se genera próxima fecha sugerida automáticamente (si es preventivo)

4. **Aprobación** (opcional para críticos):
   - Supervisor revisa y aprueba
   - Se registra aprobado_por_id

### 5.4. Datos Mínimos de Cada Evento

**Datos obligatorios:**
- Vehículo
- Tipo de mantenimiento (o descripción si es manual)
- Estado
- Fecha programada (al menos)
- Fecha de ejecución (al completar)
- Contador al momento (km/horas)

**Datos recomendados:**
- Descripción del trabajo
- Costo (para análisis futuro)
- Técnico responsable
- Taller/proveedor
- **Conductor asignado** (obligatorio para correctivos, recomendado para otros tipos)

**Datos opcionales:**
- Repuestos utilizados
- Observaciones
- Fotos/documentos
- Incidencia previa (para correctivos)

### 5.5. Gestión de Documentos Legales (Parte del Mantenimiento)

**Los documentos legales obligatorios son parte integral del mantenimiento de flotas en Chile:**

**Documentos obligatorios para TODOS los vehículos:**
1. **Permiso de Circulación**: Obligatorio anual, emitido por municipalidad. Debe renovarse antes de vencer.
2. **Revisión Técnica**: Obligatoria anual, realizada en estaciones autorizadas. El vehículo debe pasar la inspección.
3. **SOAP (Seguro Obligatorio de Accidentes Personales)**: Obligatorio anual. Seguro básico requerido por ley.
4. **Análisis de Gases / Certificado de Emisiones**: Obligatorio según tipo de vehículo, año y normativa vigente. Verifica emisiones contaminantes.

**Documentos adicionales según tipo de vehículo:**
- **Camiones con grúa**: Certificado de grúa, certificado de carga
- **Vehículos de transporte especial**: Certificados específicos según carga (ej: materiales peligrosos)

**Proceso de gestión:**
1. Al dar de alta un vehículo, se deben registrar todos los documentos obligatorios con fechas de vencimiento
2. **OBLIGATORIO: Subir escaneo digital de cada documento**
   - Escanear documento en alta calidad (PDF preferido, o JPG/PNG de buena resolución)
   - Si el documento tiene anverso y reverso (ej: Permiso de Circulación), escanear ambos lados
   - El sistema debe validar que existe archivo adjunto antes de marcar como completo
3. Sistema genera alertas automáticas:
   - 60 días antes: Advertencia
   - 15 días antes: CRÍTICA
   - Al vencer: CRÍTICA inmediata (vehículo NO puede circular)
4. Al renovar documento:
   - Se marca el documento anterior como inactivo (pero se mantiene el escaneo en historial)
   - Se crea nuevo registro con nueva fecha de vencimiento
   - **OBLIGATORIO: Subir escaneo del nuevo documento**
   - Se cierran alertas relacionadas
5. Los documentos escaneados deben estar siempre disponibles en el sistema para:
   - Visualización rápida desde cualquier lugar
   - Descarga para mostrar a inspectores o policía
   - Respuesta inmediata a solicitudes de verificación

**Importancia crítica:**
- Un vehículo sin documentos vigentes NO puede circular legalmente
- Multas significativas por documentos vencidos
- Riesgo de retención o inmovilización del vehículo
- Pueden ser solicitados en cualquier momento por inspectores o policía
- Impacto operativo: un vehículo detenido por documentos vencidos paraliza operaciones


## 7. SISTEMA DE ALERTAS

### 6.1. Tipos de Alertas

#### 6.1.1. **Próximo Mantenimiento**
- **Cuándo:** X días/kilómetros antes de que venza un mantenimiento preventivo
- **Severidad:** Informativa o Advertencia
- **Generación:** Automática al acercarse fecha_programada o umbral de km
- **Ejemplo:** "Cambio de aceite de Navara ABCD12 programado en 15 días"

#### 6.1.2. **Mantenimiento Vencido**
- **Cuándo:** Cuando fecha_programada ya pasó y el mantenimiento sigue en "programado"
- **Severidad:** Advertencia o Crítica (según criticidad del vehículo)
- **Generación:** Automática diaria (job programado)
- **Ejemplo:** "Mantenimiento de frenos de Camión Grúa EFGH34 vencido hace 3 días"

#### 6.1.3. **Certificado por Vencer**
- **Cuándo:** X días antes de fecha_vencimiento de certificación
- **Severidad:** 
  - **Documentos obligatorios**: Advertencia a 60 días, CRÍTICA a 15 días
  - **Documentos opcionales**: Informativa a 30 días, Advertencia a 7 días
- **Generación:** Automática (job diario)
- **Ejemplo:** "Revisión Técnica de Berlingo IJKL56 vence en 30 días - RENOVAR PRONTO"
- **Importancia:** Los documentos obligatorios (Permiso de Circulación, Revisión Técnica, SOAP, Análisis de Gases) deben renovarse ANTES de vencer para evitar inmovilización del vehículo

#### 6.1.4. **Certificado Vencido**
- **Cuándo:** Cuando fecha_vencimiento ya pasó y certificado sigue activo
- **Severidad:** **CRÍTICA** (riesgo legal y operativo)
- **Generación:** Automática diaria
- **Ejemplo:** "Revisión Técnica de Navara MNOP78 vencida hace 5 días - ACCIÓN INMEDIATA"
- **Importancia:** 
  - Un vehículo con documentos obligatorios vencidos NO puede circular legalmente
  - Puede ser retenido por policía o inspectores
  - Multas significativas y riesgo de inmovilización
  - Para documentos obligatorios (Permiso de Circulación, Revisión Técnica, SOAP), la alerta debe ser CRÍTICA desde el día 0 de vencimiento

#### 6.1.5. **Licencia de Conductor por Vencer**
- **Cuándo:** X días antes de fecha_vencimiento de licencia de conducir
- **Severidad:** 
  - Advertencia a 60 días
  - **CRÍTICA a 30 días** (tiempo suficiente para renovar)
  - **CRÍTICA a 15 días** (riesgo inminente)
- **Generación:** Automática (job diario)
- **Ejemplo:** "Licencia de Juan Pérez (RUT 12345678-9) vence en 30 días - RENOVAR PRONTO"
- **Importancia:** Un conductor con licencia vencida NO puede manejar vehículos. Si lo hace, puede recibir multas e infracciones. El sistema debe prevenir asignación de vehículos a conductores con licencia vencida.

#### 6.1.6. **Licencia de Conductor Vencida**
- **Cuándo:** Cuando fecha_vencimiento de licencia ya pasó y el conductor sigue activo
- **Severidad:** **CRÍTICA** (riesgo legal y operativo)
- **Generación:** Automática diaria
- **Ejemplo:** "Licencia de Juan Pérez (RUT 12345678-9) vencida hace 5 días - NO ASIGNAR VEHÍCULOS"
- **Importancia:** 
  - Un conductor con licencia vencida NO puede manejar legalmente
  - Puede recibir multas e infracciones si es controlado
  - El sistema debe BLOQUEAR asignación de vehículos a este conductor
  - Se debe alertar a supervisores y administradores inmediatamente

#### 6.1.7. **Stock Bajo de Repuesto**
- **Cuándo:** Cuando stock disponible de un repuesto está por debajo del umbral mínimo configurado
- **Severidad:** **Advertencia** (riesgo operativo)
- **Generación:** Automática (al recibir compra, al usar repuesto en mantenimiento, job diario de verificación)
- **Ejemplo:** "Stock de Filtro de Aceite (código: FIL-001) está bajo: 3 unidades disponibles (mínimo: 5)"
- **Importancia:** 
  - Permite planificar compras antes de agotar stock
  - Evita interrupciones en mantenimientos por falta de repuestos
  - Se alerta a personal administrativo para gestionar compra

#### 6.1.8. **Stock Agotado de Repuesto**
- **Cuándo:** Cuando stock disponible de un repuesto llega a 0 o negativo
- **Severidad:** **CRÍTICA** (riesgo operativo inmediato)
- **Generación:** Automática (al usar repuesto en mantenimiento, job diario de verificación)
- **Ejemplo:** "Stock de Filtro de Aceite (código: FIL-001) está AGOTADO - COMPRA URGENTE"
- **Importancia:** 
  - Un repuesto agotado puede impedir realizar mantenimientos
  - Puede paralizar operaciones si es crítico
  - Se alerta inmediatamente a personal administrativo y supervisores

#### 6.1.9. **Condición Crítica**
- **Cuándo:** Manual o cuando un mantenimiento correctivo es de emergencia
- **Severidad:** **CRÍTICA**
- **Generación:** Manual o automática
- **Ejemplo:** "Camión Grúa QRST90 fuera de servicio por falla en grúa"

#### 6.1.10. **Alerta Manual**
- **Cuándo:** Cualquier usuario con permisos crea alerta personalizada
- **Severidad:** Definida por usuario
- **Generación:** Manual

### 6.2. Lógica de Generación de Alertas

**Jobs programados (Laravel Scheduler):**

1. **Job diario: "GenerarAlertasMantenimientos"**
   - Recorre mantenimientos programados no completados
   - Calcula días hasta fecha_programada
   - Genera alerta "próximo" si está en ventana (ej: 30-15 días antes)
   - Genera alerta "vencido" si fecha_programada pasó
   - Considera criticidad del vehículo para severidad

2. **Job diario: "GenerarAlertasCertificaciones"**
   - Recorre certificaciones activas
   - Calcula días hasta vencimiento
   - **Para documentos obligatorios** (Permiso de Circulación, Revisión Técnica, SOAP, Análisis de Gases):
     - Genera alerta ADVERTENCIA si vence en 60 días o menos
     - Genera alerta CRÍTICA si vence en 15 días o menos
     - Genera alerta CRÍTICA inmediatamente si fecha_vencimiento ya pasó (día 0)
   - **Para documentos opcionales**:
     - Genera alerta INFORMATIVA si vence en 30 días
     - Genera alerta ADVERTENCIA si vence en 7 días
     - Genera alerta ADVERTENCIA si fecha_vencimiento pasó
   - No duplicar alertas ya existentes del mismo tipo

3. **Job diario: "GenerarAlertasLicenciasConductores"**
   - Recorre conductores activos con licencia registrada
   - Calcula días hasta vencimiento de licencia (licencia_vencimiento)
   - Genera alerta ADVERTENCIA si vence en 60 días o menos
   - Genera alerta CRÍTICA si vence en 30 días o menos
   - Genera alerta CRÍTICA si vence en 15 días o menos
   - Genera alerta CRÍTICA inmediatamente si fecha_vencimiento ya pasó (día 0)
   - **Importante**: Si licencia está vencida, marca conductor como "no disponible para asignación"
   - No duplicar alertas ya existentes del mismo tipo para el mismo conductor

4. **Job diario: "GenerarAlertasStockRepuestos"**
   - Recorre repuestos activos con stock configurado
   - Verifica cantidad_disponible vs cantidad_minima en tabla stock
   - Si cantidad_disponible < cantidad_minima y cantidad_disponible > 0:
     - Genera alerta ADVERTENCIA "stock_bajo"
   - Si cantidad_disponible <= 0:
     - Genera alerta CRÍTICA "stock_agotado"
   - No duplicar alertas ya existentes del mismo tipo para el mismo repuesto
   - Se dispara también automáticamente al recibir compra o usar repuesto en mantenimiento

5. **Job al completar mantenimiento: "CalcularProximoMantenimiento"**
   - Al completar mantenimiento preventivo
   - Calcula próxima fecha basada en frecuencia
   - Crea nuevo mantenimiento programado
   - Puede generar alerta si la próxima fecha es cercana

**Cálculos específicos:**

- **Por kilometraje:** `fecha_proxima = fecha_actual + ((km_objetivo - km_actual) / km_promedio_diario)`
- **Por tiempo:** `fecha_proxima = fecha_ultimo_mantenimiento + frecuencia_meses`
- **Mixto:** Se toma la fecha más cercana entre ambas

**Consideraciones:**
- No duplicar alertas (verificar si ya existe alerta activa del mismo tipo)
- Cerrar alertas automáticamente cuando se resuelve (ej: al completar mantenimiento)

### 6.3. Diferencia entre Informativa vs Crítica

**Alerta Informativa:**
- Color: Azul/Verde
- Acción: Planificar
- Urgencia: Baja
- Notificación email: Opcional (solo a responsables directos)
- Ejemplos: Próximo mantenimiento en 30 días, certificado vence en 60 días

**Alerta Advertencia:**
- Color: Amarillo/Naranja
- Acción: Atender pronto
- Urgencia: Media
- Notificación email: Sí (a supervisores y responsables)
- Ejemplos: Mantenimiento próximo en 7 días, certificado vence en 15 días

**Alerta Crítica:**
- Color: Rojo
- Acción: Inmediata
- Urgencia: Alta
- Notificación email: Sí (a todos los usuarios relevantes, múltiples veces)
- Requiere cierre manual con justificación
- Ejemplos: Mantenimiento vencido, certificado vencido, licencia de conductor vencida, vehículo crítico fuera de servicio

### 6.4. Visualización en la App

**Dashboard principal mejorado:**
- **Panel con contadores:**
  - Total alertas pendientes, críticas, advertencias, informativas
  - Mantenimientos en curso (con progreso visual)
  - Mantenimientos esperando repuestos
  - Mantenimientos pendientes de aprobación
- **Lista de alertas críticas destacadas arriba** (actualización en tiempo real)
- **Widget de "Mantenimientos en curso":**
  - Lista de mantenimientos en estado "en_proceso" o "en_revision"
  - Barra de progreso si aplica
  - Tiempo transcurrido desde inicio
- **Gráfico de costos por vehículo** (últimos 6 meses):
  - Visualización de tendencia de gastos
  - Identificación de vehículos con costos altos
- **Lista de vehículos que requieren atención inmediata:**
  - Vehículos con alertas críticas
  - Vehículos con mantenimientos vencidos
  - Vehículos con documentos vencidos
- **Filtros:** por severidad, por vehículo, por tipo, por período

**Vista de alertas:**
- Tabla ordenable y filtrable
- Columnas: Severidad (badge color), Vehículo, Tipo, Título, Fecha límite, Estado
- Acciones: Ver detalle, Cerrar alerta, Crear mantenimiento desde alerta

**Badges visuales:**
- Rojo: Crítica
- Naranja: Advertencia
- Azul: Informativa

**Notificaciones en tiempo real:**
- **Laravel Echo + Broadcasting** (Pusher o Redis):
  - Notificaciones push cuando se genera alerta crítica
  - Notificación cuando mecánico solicita repuestos (para administrativos)
  - Notificación cuando stock llega a mínimo
  - Notificación cuando mantenimiento requiere aprobación
- **Badge en navegación con contador** de alertas pendientes (actualización automática)
- **Polling automático** para alertas críticas (cada 30 segundos como fallback si no hay WebSockets)
- **Notificaciones in-app** además de emails:
  - Tabla `notificaciones` en base de datos
  - Marcar como leída/no leída
  - Historial de notificaciones

### 6.5. Envío por Email

**Configuración SMTP Gmail:**
- Ya configurado según requisitos
- Usar Laravel Mail con templates personalizados

**Cuándo se envía email:**

1. **Al generar alerta crítica:**
   - Inmediatamente
   - A: Administradores, Supervisores, Técnico asignado (si existe)

2. **Al generar alerta de advertencia:**
   - Inmediatamente
   - A: Supervisores, Técnico asignado (si existe)

3. **Resumen diario** (opcional, configurable):
   - Job programado cada mañana
   - Lista de alertas pendientes por severidad
   - A: Usuarios que tienen `email_notificaciones = true`

**Contenido del email:**

- **Asunto:** `[CRÍTICA] Mantenimiento Vencido - Camión Grúa EFGH34`
- **Cuerpo:**
  - Severidad (badge visual)
  - Vehículo (con link a detalle)
  - Tipo de alerta
  - Mensaje descriptivo
  - Fecha límite
  - Acción sugerida (link directo a app)
  - Botón CTA: "Ver Alertas" o "Crear Mantenimiento"

**Notas:**
- Usuarios pueden desactivar emails en su perfil (excepto críticas para administradores)
- Rate limiting: No enviar más de X emails por hora por usuario (evitar spam)


## 8. ROLES Y USUARIOS

### 7.1. Tipos de Usuarios Esperables

#### 7.1.1. **Administrador**
**Responsabilidades:**
- Configuración completa del sistema
- Gestión de usuarios y permisos
- Configuración de tipos de vehículos y mantenimientos
- Acceso total a todos los datos
- Eliminación de registros (soft delete)

**Permisos:**
- CRUD completo en todas las entidades
- Gestión de usuarios
- Configuración del sistema
- Ver todos los reportes y estadísticas
- Cerrar cualquier alerta

**Acceso:**
- Todas las secciones sin restricciones

#### 7.1.2. **Supervisor**
**Responsabilidades:**
- Supervisar estado general de la flota
- Programar y aprobar mantenimientos
- Revisar y cerrar alertas
- Coordinar con mecánicos
- Ver reportes operativos

**Permisos:**
- Crear y editar vehículos
- Crear, editar y aprobar mantenimientos
- Ver todos los vehículos y mantenimientos
- Cerrar alertas
- Crear alertas manuales
- Ver reportes (no estadísticas financieras avanzadas)

**Restricciones:**
- No puede eliminar registros críticos
- No puede gestionar usuarios
- No puede modificar configuración del sistema

#### 7.1.2.1. **Administrativo**
**Responsabilidades:**
- Ingresar y mantener documentación de vehículos (datos generales, documentos legales)
- Ingresar y mantener información de conductores
- Gestionar inventario de repuestos (catálogo, compras, stock)
- Administrar proveedores
- Registrar compras de repuestos
- Realizar ajustes de inventario
- Ver reportes de inventario y compras

**Permisos:**
- Crear, editar y eliminar vehículos (datos administrativos)
- Crear, editar y eliminar conductores
- Crear, editar y eliminar certificaciones/documentos de vehículos
- Crear, editar y eliminar repuestos (catálogo)
- Crear, editar y eliminar proveedores
- Crear, editar y recibir compras de repuestos
- Ver y gestionar stock de repuestos
- Realizar ajustes de inventario (entradas y salidas manuales)
- Ver reportes de inventario, compras y stock
- Ver vehículos y mantenimientos (solo lectura, no puede aprobar)

**Restricciones:**
- No puede aprobar mantenimientos
- No puede ejecutar mantenimientos
- No puede cerrar alertas críticas (solo puede ver)
- No puede gestionar usuarios
- No puede modificar configuración del sistema

#### 7.1.3. **Técnico / Mecánico**
**Responsabilidades:**
- Ejecutar mantenimientos asignados
- Registrar trabajo realizado
- Actualizar contadores de uso
- Reportar condiciones encontradas

**Permisos:**
- Ver vehículos asignados o todos (configurable)
- Ver mantenimientos asignados o en proceso
- Actualizar estado de mantenimientos (a "en_proceso", "completado")
- Registrar contadores de uso
- Ver alertas relacionadas con sus mantenimientos
- Crear mantenimientos correctivos (reportar incidencias)
- **Usar repuestos del inventario** en mantenimientos (registrar consumo)
- Ver stock disponible de repuestos
- Ver catálogo de repuestos

**Restricciones:**
- No puede aprobar mantenimientos
- No puede cerrar alertas (excepto si las genera él)
- No puede eliminar registros
- No puede programar mantenimientos (solo ejecutar)
- **No puede comprar repuestos** (solo puede usarlos)
- **No puede modificar stock manualmente** (solo se descuenta al usar en mantenimiento)
- No puede crear o editar repuestos en el catálogo
- No puede gestionar proveedores

#### 7.1.4. **Visualizador**
**Responsabilidades:**
- Solo consulta de información
- Monitoreo pasivo

**Permisos:**
- Ver vehículos (sin editar)
- Ver mantenimientos (sin editar)
- Ver alertas (sin cerrar)
- Ver reportes básicos

**Restricciones:**
- Solo lectura en todo el sistema

### 7.2. Permisos a Alto Nivel

**Sistema de permisos Laravel (Spatie Permission o similar):**

**Permisos específicos:**
- `vehiculos.ver`, `vehiculos.crear`, `vehiculos.editar`, `vehiculos.eliminar`
- `mantenimientos.ver`, `mantenimientos.crear`, `mantenimientos.editar`, `mantenimientos.eliminar`, `mantenimientos.aprobar`, `mantenimientos.ejecutar`
- `alertas.ver`, `alertas.crear`, `alertas.cerrar`
- `certificaciones.ver`, `certificaciones.crear`, `certificaciones.editar`, `certificaciones.eliminar`
- `conductores.ver`, `conductores.crear`, `conductores.editar`, `conductores.eliminar`
- `usuarios.ver`, `usuarios.crear`, `usuarios.editar`, `usuarios.eliminar`
- `reportes.ver`, `reportes.avanzados`
- **Inventario:**
  - `repuestos.ver`, `repuestos.crear`, `repuestos.editar`, `repuestos.eliminar`
  - `repuestos.usar` (usar en mantenimientos, descuenta stock)
  - `proveedores.ver`, `proveedores.crear`, `proveedores.editar`, `proveedores.eliminar`
  - `compras.ver`, `compras.crear`, `compras.editar`, `compras.recibir`, `compras.eliminar`
  - `inventario.ver`, `inventario.ajustar` (ajustes manuales de stock)
  - `stock.ver`, `stock.editar` (editar umbrales mínimos)

**Roles predefinidos:**
- `administrador` → Todos los permisos
- `supervisor` → Todos excepto usuarios.* y algunos reportes.avanzados
- `administrativo` → Vehículos, conductores, certificaciones, inventario completo (repuestos, compras, stock), reportes de inventario
- `tecnico` → Ver y ejecutar mantenimientos, ver vehículos, usar repuestos (solo consumo, no compra)
- `visualizador` → Solo permisos de lectura

### 7.3. Acciones Críticas Auditadas

**Eventos que DEBEN quedar en tabla auditoria:**

1. **Gestión de vehículos:**
   - Crear vehículo (con todos los datos)
   - Editar datos críticos (patente, categoría, estado)
   - Cambiar estado a "baja" o "inactivo"
   - Eliminar vehículo (soft delete)

2. **Mantenimientos:**
   - Crear mantenimiento
   - Aprobar mantenimiento (especialmente críticos)
   - Completar mantenimiento (registrar datos finales)
   - Cancelar mantenimiento
   - Eliminar mantenimiento

3. **Alertas:**
   - Cerrar alerta crítica (registrar justificación)
   - Crear alerta manual

4. **Inventario de Repuestos:**
   - Crear compra de repuestos
   - Recibir compra (cambiar estado a recibida, genera movimientos de entrada)
   - Crear ajuste de inventario (entrada o salida manual)
   - Usar repuesto en mantenimiento (genera movimiento de salida)
   - Modificar stock mínimo (umbral de alerta)
   - Eliminar compra o movimiento

5. **Certificaciones:**
   - Crear o editar certificación (especialmente fechas de vencimiento)
   - Marcar como inactiva

5. **Asignaciones de Conductores:**
   - Asignar conductor a vehículo (con validación de licencia vigente)
   - Finalizar asignación
   - Cambiar conductor asignado

6. **Licencias de Conductores:**
   - Crear o editar datos de licencia de conductor
   - Actualizar fecha de vencimiento de licencia
   - Subir/actualizar imagen escaneada de licencia
   - Renovar licencia (marcar anterior como inactiva, crear nueva)

6. **Usuarios:**
   - Cualquier cambio de permisos
   - Activación/desactivación de usuarios
   - Cambios de rol

**Datos a capturar en auditoría:**
- Usuario que ejecutó la acción
- Timestamp exacto
- IP y User Agent (para seguridad)
- Datos anteriores y nuevos (snapshot JSON)
- Descripción legible de la acción


## 9. FLUJO DE USO PRINCIPAL

### 8.1. Alta de Vehículo

**Actor:** Administrador o Supervisor

**Pasos:**
1. Ir a sección "Vehículos" → "Nuevo Vehículo"
2. Completar datos básicos:
   - Patente (validación: formato chileno; autos 4 letras + 2 dígitos, motos 3 letras + 2 dígitos; letras permitidas B,C,D,F,G,H,J,K,L,P,R,S,T,V,W,X,Y,Z; mayúsculas, sin espacios)
   - Marca, modelo, año
   - Categoría (selección de lista)
   - Tipo de combustible
   - Número de motor/chasis (opcional)
   - Fecha de incorporación
3. Sistema asigna valores por defecto según categoría (criticidad, tipo de contador principal)
4. Guardar
5. Sistema crea registro en auditoría
6. **Sistema sugiere crear documentos legales obligatorios:**
   - Permiso de Circulación
   - Revisión Técnica
   - SOAP (Seguro Obligatorio)
   - Análisis de Gases (si aplica según tipo de vehículo)
   - Documentos especiales según categoría (ej: certificado grúa para camiones con grúa)
7. **Para cada documento creado, sistema requiere:**
   - Subir escaneo digital del documento (obligatorio)
   - Si el documento tiene anverso y reverso, subir ambos archivos
   - Validación de formato (PDF, JPG, PNG) y tamaño máximo

**Resultado:** Vehículo activo en el sistema. **IMPORTANTE:** Deben registrarse todos los documentos legales obligatorios CON SUS ESCANEOS DIGITALES antes de que el vehículo circule.

### 8.2. Programación de Mantenimiento

**Actor:** Supervisor

**Método 1: Automática**
1. Al completar un mantenimiento preventivo
2. Sistema calcula próxima fecha automáticamente
3. Crea mantenimiento programado
4. Supervisor recibe notificación y puede ajustar fecha

**Método 2: Manual**
1. Ir a vehículo → "Nuevo Mantenimiento"
2. Seleccionar tipo de mantenimiento (o crear manual)
3. Sistema sugiere fecha basada en último mantenimiento similar
4. Supervisor ajusta fecha según disponibilidad
5. Asigna técnico responsable (opcional en este momento)
6. Guardar

**Método 3: Desde Alerta**
1. Ver alerta de "próximo mantenimiento" o "vencido"
2. Click en "Crear Mantenimiento"
3. Sistema pre-llena tipo y fecha sugerida
4. Supervisor completa y guarda

**Resultado:** Mantenimiento en estado "programado", aparece en calendario/listado

### 8.2.1. Asignación de Conductor a Vehículo

**Actor:** Supervisor o Administrador

**Pasos:**
1. Ir a vehículo → "Asignar Conductor" o desde listado de conductores
2. Seleccionar conductor (o crear nuevo si no existe)
3. **Sistema valida automáticamente:**
   - Si el conductor tiene licencia registrada
   - Si la licencia está vigente (fecha_vencimiento >= fecha actual)
   - Si el conductor está activo
4. **Si licencia está vencida o faltante:**
   - Sistema BLOQUEA la asignación
   - Muestra error: "No se puede asignar vehículo: Conductor tiene licencia vencida o no registrada"
   - Muestra fecha de vencimiento si aplica
   - Sugiere renovar licencia primero
5. Si validación pasa, establecer fecha de asignación (por defecto: hoy)
6. Opcionalmente agregar observaciones
7. Guardar

**Lógica del sistema:**
1. **VALIDACIÓN CRÍTICA**: Verificar que licencia_vencimiento >= fecha actual
2. Si existe una asignación activa anterior (fecha_fin IS NULL):
   - Se cierra automáticamente con fecha_fin = fecha_asignacion nueva
   - Se registra motivo_fin = 'cambio_conductor'
3. Se crea nueva asignación con fecha_fin = NULL (activa)
4. Se actualiza conductor_actual_id en tabla vehiculos
5. Se registra en auditoría quién hizo la asignación

**Resultado:** Vehículo tiene conductor asignado, historial actualizado

**Validaciones de seguridad:**
- Un conductor SIN licencia registrada NO puede ser asignado
- Un conductor con licencia VENCIDA NO puede ser asignado
- El sistema previene infracciones y multas por conducir sin licencia vigente

**Nota:** También se puede finalizar asignación sin asignar nuevo conductor (vehículo queda sin conductor asignado).

### 8.3. Flujo Completo de Mantenimiento (Correctivo o Preventivo)

#### 8.3.1. Recepción del Vehículo en Taller

**Actor:** Técnico/Mecánico

**Pasos:**
1. Vehículo llega al taller (conductor lo trae)
2. Mecánico recibe el vehículo:
   - Busca el mantenimiento (programado o crea uno nuevo si es correctivo)
   - **Selecciona conductor** que trajo el vehículo (dropdown/select de conductores activos):
     - Campo `conductor_asignado_id` se llena manualmente
     - Sistema valida que el conductor tenga licencia vigente (muestra advertencia si no)
   - **Anota motivo de ingreso** en campo `motivo_ingreso`:
     - Para correctivos: describe qué falló o qué problema tiene el vehículo
     - Para preventivos: puede ser "Mantenimiento programado" o motivo específico
   - Cambia estado a **"en_revision"**
   - Sistema captura fecha/hora de recepción
   - Registra contador actual (kilometraje/horómetro) al ingreso

**Resultado:** Mantenimiento en estado "en_revision", conductor seleccionado, motivo de ingreso registrado

#### 8.3.2. Revisión y Solicitud de Repuestos

**Actor:** Técnico/Mecánico

**Pasos:**
1. Mecánico realiza revisión del vehículo
2. Identifica qué repuestos necesita
3. **Crea solicitud de repuestos:**
   - Ir a mantenimiento → "Solicitar Repuestos"
   - Agregar items (repuestos necesarios con cantidades)
   - Si el repuesto no está en catálogo, puede agregarlo manualmente con código y descripción
   - Agregar observaciones si es necesario
   - Enviar solicitud
4. Sistema:
   - Cambia estado del mantenimiento a **"esperando_repuestos"**
   - Crea registro en `solicitudes_repuestos` con estado "pendiente"
   - Notifica a personal administrativo (email o notificación in-app)

**Resultado:** Solicitud de repuestos creada, mantenimiento esperando repuestos

#### 8.3.3. Procesamiento de Solicitud de Repuestos

**Actor:** Personal Administrativo

**Pasos:**
1. Recibe notificación de solicitud de repuestos
2. Revisa solicitud:
   - Ver qué repuestos se necesitan
   - Verifica stock disponible
3. **Si hay stock disponible:**
   - Marca items como "disponibles"
   - Entrega repuestos al mecánico
   - Marca solicitud como "entregada"
   - Sistema cambia estado del mantenimiento a "en_proceso"
4. **Si NO hay stock o stock insuficiente:**
   - Crea orden de compra (ver sección 8.5.1)
   - Marca solicitud como "en_compra"
   - Cuando recibe la compra:
     - Genera movimientos de entrada al inventario
     - Entrega repuestos al mecánico
     - Marca solicitud como "entregada"
     - Sistema cambia estado del mantenimiento a "en_proceso"

**Resultado:** Repuestos entregados, mantenimiento listo para reparación

#### 8.3.4. Ejecución de Reparación y Registro

**Actor:** Técnico/Mecánico

**Pasos:**
1. Mecánico recibe repuestos
2. Procede con la reparación
3. **Registra trabajo realizado:**
   - Cambia estado a "en_proceso" (si no estaba ya)
   - Sistema captura fecha_inicio automáticamente
   - Registra descripción del trabajo en `descripcion_trabajo`
   - **Completa checklist de mantenimiento:**
     - Si hay checklist asociado, se muestra lista de items
     - Mecánico marca items completados durante la reparación
     - Puede agregar items personalizados si es necesario
     - Sistema valida items obligatorios antes de completar
   - **Sube evidencia (opcional pero recomendado):**
     - Fotos del problema (si es correctivo)
     - Fotos del trabajo realizado
     - Documentos relacionados
     - Videos si es necesario
   - **Registra repuestos utilizados:**
     - Selecciona repuestos de la solicitud (o agrega otros si usó diferentes)
     - Ingresa cantidades realmente usadas
     - Sistema calcula costo de repuestos automáticamente
   - **Registra insumos utilizados** (si aplica):
     - Los insumos (aceite, líquidos, etc.) se gestionan como repuestos en inventario
     - Se seleccionan del catálogo de repuestos igual que los repuestos
     - Se registran en `mantenimiento_repuestos` igual que los repuestos
     - Sistema calcula costo de repuestos e insumos automáticamente
   - **Registra mano de obra:**
     - Ingresa horas trabajadas (puede ser NULL si no aplica)
     - Ingresa costo de mano de obra (calculado o manual)
   - **Sistema calcula automáticamente:**
     - `costo_total = costo_repuestos + costo_mano_obra`
   - Toma notas/observaciones finales
4. **Validación antes de completar:**
   - Sistema verifica que todos los documentos obligatorios del vehículo estén vigentes
   - Si hay documentos vencidos o por vencer (menos de 15 días), muestra advertencia
   - Si `costo_total > umbral_aprobacion` (configurable), cambia estado a "pendiente_aprobacion"
   - Si hay items obligatorios del checklist sin completar, muestra advertencia
5. Al finalizar:
   - Cambia estado a "completado" (o "pendiente_aprobacion" si requiere aprobación)
   - Sistema captura fecha_fin automáticamente
   - Registra contador final (kilometraje/horómetro al salir)
   - Marca taller/proveedor (si fue taller externo o "Taller Interno")
6. Sistema:
   - Genera movimientos de salida del inventario para repuestos usados
   - Actualiza stock (descuenta cantidades)
   - Calcula próxima fecha sugerida (si es preventivo)
   - Cierra alertas relacionadas
   - Genera nuevo mantenimiento programado (si corresponde)
   - Actualiza contador de uso del vehículo
   - **Si es correctivo: sistema captura automáticamente conductor_actual_id del vehículo** (para trazabilidad)

**Resultado:** Mantenimiento completado, costos registrados, stock actualizado, historial actualizado

**Resultado:** Mantenimiento completado, historial actualizado, próximos mantenimientos generados

### 8.4. Generación y Cierre de Alertas

**Generación (Automática):**

1. **Job diario ejecuta:**
   - Revisa mantenimientos programados próximos/vencidos
   - Revisa certificaciones por vencer/vencidas
   - Crea alertas según criterios

2. **Al crear alerta:**
   - Sistema asigna severidad según tipo y criticidad del vehículo
   - Calcula fecha_limite
   - Envía email si corresponde

**Cierre (Manual o Automático):**

**Automático:**
- Al completar mantenimiento relacionado
- Al actualizar certificación (renovación)

**Manual:**
1. Usuario ve alerta en listado
2. Click en "Cerrar Alerta"
3. Sistema pide justificación (especialmente si es crítica)
4. Usuario completa y confirma
5. Sistema:
   - Cambia estado a "cerrada"
   - Registra quién cerró y cuándo
   - Crea registro en auditoría

### 8.5. Gestión de Inventario de Repuestos

#### 8.5.1. Compra de Repuestos

**Actor:** Personal Administrativo

**Pasos:**
1. Ir a "Inventario" → "Nueva Compra"
2. Seleccionar proveedor (o crear nuevo si no existe)
3. Completar datos de compra:
   - Número de factura u orden de compra
   - Fecha de compra
   - Fecha de recepción (puede ser futura si aún no se recibe)
4. Agregar items (repuestos):
   - Buscar repuesto en catálogo (o crear nuevo si no existe)
   - Ingresar cantidad comprada
   - Ingresar precio unitario
   - Sistema calcula subtotal (con redondeo chileno)
5. Sistema calcula total automáticamente
6. Guardar compra (estado: "pendiente")
7. Cuando se recibe físicamente:
   - Cambiar estado a "recibida"
   - Sistema genera automáticamente:
     - Movimientos de entrada al inventario para cada item
     - Actualización de stock disponible
     - Alertas si algún repuesto alcanza stock mínimo

**Resultado:** Compra registrada, stock actualizado, movimientos de inventario creados

#### 8.5.2. Uso de Repuestos en Mantenimiento

**Nota:** Este flujo está integrado en el flujo completo de mantenimiento (sección 8.3.4). Se detalla aquí para referencia.

**Actor:** Técnico / Mecánico

**Pasos:**
1. Durante la ejecución de reparación (mantenimiento en estado "en_proceso")
2. Ir a sección "Repuestos Utilizados" del mantenimiento
3. Agregar repuestos usados:
   - Puede seleccionar repuestos de la solicitud previa (si hubo)
   - O agregar nuevos repuestos directamente
   - Buscar en catálogo de repuestos disponibles
   - Seleccionar repuesto
   - Ingresar cantidad realmente utilizada
   - Sistema verifica stock disponible:
     - Si hay stock suficiente: permite continuar
     - Si no hay stock suficiente: muestra advertencia pero permite continuar (registra como "stock negativo" o requiere aprobación)
4. Sistema calcula costo del repuesto automáticamente:
   - Usa precio de referencia del catálogo
   - O último precio de compra si está disponible
   - Calcula subtotal por repuesto
   - Suma todos los subtotales en `costo_repuestos`
5. Agregar insumos (si aplica):
   - Los insumos se gestionan como repuestos en inventario
   - Se seleccionan del catálogo igual que los repuestos
   - Se registran en `mantenimiento_repuestos` igual que los repuestos
   - Se suman en `costo_repuestos` junto con los repuestos
6. Registrar mano de obra:
   - Horas trabajadas (puede ser NULL si no aplica)
   - Costo de mano de obra (calculado o manual)
   - Se suma en `costo_mano_obra`
7. Al completar el mantenimiento:
   - Sistema calcula automáticamente: `costo_total = costo_repuestos + costo_mano_obra`
   - Sistema genera automáticamente:
     - Movimientos de salida del inventario para cada repuesto/insumo usado
     - Actualización de stock (descuenta cantidades)
     - Registro en `mantenimiento_repuestos` (incluye repuestos e insumos)
8. Si stock queda por debajo del mínimo:
   - Sistema genera alerta automática para personal administrativo

**Resultado:** Repuestos e insumos registrados en mantenimiento, costos desglosados, stock actualizado, alertas generadas si aplica

#### 8.5.3. Ajuste Manual de Inventario

**Actor:** Personal Administrativo

**Pasos:**
1. Ir a "Inventario" → "Ajustes de Stock"
2. Seleccionar repuesto
3. Indicar tipo de ajuste:
   - Entrada (ajuste positivo): Ej: inventario físico encontró más unidades
   - Salida (ajuste negativo): Ej: pérdida, vencimiento, daño
4. Ingresar cantidad
5. Agregar observaciones (justificación del ajuste)
6. Guardar
7. Sistema genera:
   - Movimiento de inventario
   - Actualización de stock

**Resultado:** Stock ajustado, movimiento registrado, trazabilidad mantenida

#### 8.5.4. Administración de Catálogo de Repuestos

**Actor:** Personal Administrativo

**Pasos:**
1. Ir a "Inventario" → "Catálogo de Repuestos"
2. Crear nuevo repuesto:
   - Código (interno o del fabricante)
   - Descripción
   - Marca (opcional)
   - Precio de referencia (opcional)
   - Stock mínimo (umbral de alerta)
   - Ubicación física (opcional)
3. Editar repuesto existente
4. Ver stock actual y movimientos históricos
5. Configurar alertas de stock mínimo

**Resultado:** Catálogo actualizado, stock mínimo configurado, alertas configuradas

### 8.6. Consulta de Historial

**Actor:** Cualquier usuario con permisos de lectura

**Vista de Vehículo:**
1. Seleccionar vehículo de listado
2. Ver pestañas:
   - **Resumen:** Datos básicos, estado actual, contadores, próximos mantenimientos, conductor actual
   - **Mantenimientos:** Lista completa ordenada por fecha (más reciente primero)
     - Cada mantenimiento muestra:
       - Tipo (preventivo/correctivo)
       - Estado
       - Fechas (programada, inicio, fin)
       - Conductor que trajo el vehículo
       - Motivo de ingreso (especialmente para correctivos)
       - Costos desglosados: Repuestos e Insumos, Mano de Obra, Total
       - Horas trabajadas (si aplica)
       - Repuestos e insumos utilizados (lista)
       - Descripción del trabajo
       - Técnico responsable
   - **Estadísticas de Gasto:**
     - **Gasto Total del Vehículo:** Suma de todos los mantenimientos completados
     - **Gasto por Tipo:**
       - Total en mantenimientos preventivos
       - Total en mantenimientos correctivos
       - Total en inspecciones
     - **Gasto Desglosado:**
       - Total en repuestos e insumos (suma de `costo_repuestos`)
       - Total en mano de obra (suma de `costo_mano_obra`)
     - **Gasto por Período:**
       - Por mes (últimos 12 meses)
       - Por año
       - Gráfico de tendencia
     - **Gasto por Mantenimiento Individual:**
       - Lista de cada mantenimiento con su costo total y desglose
       - Filtros: por tipo, por rango de fechas, por técnico
     - **Promedio de Costo:**
       - Costo promedio por mantenimiento preventivo
       - Costo promedio por mantenimiento correctivo
       - Costo promedio mensual
     - **Exportación:**
       - Exportar estadísticas a Excel
       - Exportar detalle de gastos a PDF
   - **Alertas:** Alertas activas y recientes cerradas
   - **Certificaciones:** Documentos legales y sus estados
     - Lista de todos los documentos del vehículo (activos e históricos)
     - Estado de cada documento (vigente, por vencer, vencido)
     - Visualización y descarga de escaneos digitales de cada documento
     - Botón para ver/descargar archivo escaneado
     - Indicador visual si falta escaneo de algún documento obligatorio
   - **Contadores:** Histórico de uso (gráfico si es posible)
   - **Conductores:** Historial de asignaciones de conductores (nuevo)
   - **Solicitudes de Repuestos:** Historial de solicitudes de repuestos para este vehículo

**Filtros disponibles:**
- Por tipo de mantenimiento
- Por rango de fechas
- Por estado
- Por costo (para reportes)
- Por conductor (para análisis de patrones)

**Exportación:**
- **Botón "Exportar a Excel"**: Exporta lista de mantenimientos con todos los datos
- **Botón "Exportar a PDF"**: Genera PDF con historial completo del vehículo (formato oficial)
- **Filtros aplicados**: Los filtros activos se mantienen en la exportación
- **Formato**: Headers claros, estilos apropiados, datos formateados

**Vista de Conductor (nuevo):**
1. Seleccionar conductor de listado
2. Ver información:
   - **Datos básicos:** RUT, nombre, contacto
   - **Licencia de Conducir:**
     - Número de licencia
     - Clase de licencia (A1, A2, B, C, etc.)
     - Fecha de emisión
     - **Fecha de vencimiento** (destacada con badge según estado)
     - **Estado de licencia:** Vigente / Por vencer / Vencida (con color según estado)
     - **Archivo escaneado:** Visualización y descarga de imagen de licencia
     - Alertas activas relacionadas con licencia
   - **Asignaciones actuales:** Vehículos que tiene asignados (con validación de licencia vigente)
   - **Historial de asignaciones:** Todas las asignaciones pasadas
   - **Mantenimientos asociados:** Mantenimientos correctivos de vehículos que manejó (para análisis de patrones)
   - **Estadísticas:** 
     - Cantidad de mantenimientos correctivos durante sus asignaciones
     - Costo promedio de mantenimientos correctivos
     - Comparación con otros conductores (futuro)

**Reporte de Análisis de Conductores (futuro):**
- Listado de conductores con estadísticas de mantenimientos correctivos
- Identificación de conductores con mayor frecuencia de problemas
- Comparativa de costos de mantenimiento por conductor
- Gráficos de tendencias


## 10. ARQUITECTURA LARAVEL

### 10.1. Enfoque Recomendado

**Arquitectura: Service Layer + Repository Pattern (simplificado)**

**Justificación:**
- Laravel ya provee Eloquent como capa de abstracción, no necesitamos Repository completo
- Service Layer para lógica de negocio compleja (cálculos, validaciones cruzadas)
- Actions para operaciones específicas reutilizables
- Mantener controladores delgados (thin controllers)

**Estructura:**

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── VehicleController.php           (CRUD básico, delega a Services)
│   │   ├── MaintenanceController.php
│   │   ├── AlertController.php
│   │   ├── DashboardController.php
│   │   └── ExportController.php            (Exportaciones a Excel, PDF, CSV)
│   ├── Requests/
│   │   ├── StoreVehicleRequest.php         (Validaciones)
│   │   ├── UpdateVehicleRequest.php
│   │   └── ...
│   └── Middleware/
│       └── LogActivity.php                 (Middleware para auditoría)
├── Livewire/                               (Componentes Livewire)
│   ├── VehicleTable.php                    (Tabla de vehículos con DataTables)
│   ├── VehicleForm.php                     (Formulario crear/editar vehículo)
│   ├── MaintenanceTable.php                (Tabla de mantenimientos)
│   ├── MaintenanceForm.php                 (Formulario crear/editar mantenimiento)
│   ├── AlertTable.php                      (Tabla de alertas con actualización en tiempo real)
│   ├── DriverTable.php                     (Tabla de conductores)
│   ├── DriverForm.php                      (Formulario crear/editar conductor)
│   └── Dashboard.php                       (Panel con métricas en tiempo real)
├── Models/
│   ├── Vehicle.php
│   ├── Maintenance.php
│   ├── Alert.php
│   ├── Driver.php
│   └── ...
├── Services/
│   ├── VehicleService.php                  (Lógica de negocio de vehículos)
│   ├── MaintenanceService.php              (Cálculos de fechas, programación)
│   ├── AlertService.php                    (Generación y gestión de alertas)
│   ├── DriverService.php                   (Gestión de conductores y asignaciones)
│   ├── NotificationService.php             (Envío de emails)
│   └── ExportService.php                   (Servicio para exportaciones)
├── Actions/
│   ├── CreateMaintenanceFromType.php
│   ├── CalculateNextMaintenance.php
│   ├── GenerateMaintenanceAlert.php
│   ├── CloseAlert.php
│   └── AssignDriverToVehicle.php           (Cierra asignación anterior y crea nueva)
├── Jobs/
│   ├── GenerateMaintenanceAlerts.php
│   ├── GenerateCertificationAlerts.php
│   ├── GenerateLicenseAlerts.php
│   └── SendDailyAlertSummary.php
├── Mail/
│   ├── CriticalAlertMail.php
│   ├── WarningAlertMail.php
│   └── DailySummaryMail.php
├── Events/
│   ├── MaintenanceCompleted.php
│   ├── AlertGenerated.php
│   └── VehicleCreated.php
├── Listeners/
│   ├── CalculateNextMaintenanceListener.php
│   ├── SendEmailAlertListener.php
│   └── RegisterAuditListener.php
├── Exports/                                (Clases para exportación)
│   ├── VehiclesExport.php                  (Exportar vehículos a Excel)
│   ├── MaintenancesExport.php              (Exportar mantenimientos a Excel)
│   ├── AlertsExport.php                    (Exportar alertas a Excel)
│   └── MaintenanceHistoryPdf.php           (Generar PDF de historial)
└── Helpers/                                (Funciones utilitarias)
    ├── CalculosHelper.php                  (Funciones de cálculo de fechas/km)
    ├── MonedaHelper.php                    (Funciones de redondeo monetario chileno)
    └── ChileanValidationHelper.php         (Validación de patentes y RUT chilenos)
```

### 10.2. Organización de Carpetas

**Estándares de Código (CRÍTICO):**

**Idioma del Código:**
- **Todo el código backend debe estar en INGLÉS** siguiendo estándares de Laravel:
  - Nombres de clases: `Vehicle`, `Maintenance`, `Driver`, `Alert` (PascalCase)
  - Nombres de métodos: `createVehicle()`, `calculateNextMaintenance()` (camelCase)
  - Variables y propiedades: `$vehicle`, `$maintenanceDate`, `$driverId` (camelCase)
  - Nombres de tablas en base de datos: `vehicles`, `maintenances`, `drivers`, `alerts` (snake_case, plural)
  - Columnas en base de datos: `vehicle_id`, `maintenance_date`, `driver_id` (snake_case)
  - Rutas: `/vehicles`, `/vehicles/{id}`, `/maintenances` (snake_case)
  - Controladores: `VehicleController`, `MaintenanceController`
  - Servicios: `VehicleService`, `MaintenanceService`
  - Jobs: `GenerateMaintenanceAlerts`, `GenerateLicenseAlerts`
  - Events: `MaintenanceCompleted`, `VehicleCreated`
  - Helpers: `MonedaHelper::redondearChileno()`, `ChileanValidationHelper::validarPatente()`

**Comentarios y Documentación:**
- **Comentarios en código**: Español (para mejor comprensión del equipo local)
  - Ejemplo: `// Calcula el próximo mantenimiento basado en la frecuencia`
- **Mensajes al usuario**: Español (validaciones, errores, notificaciones, emails)
  - Ejemplo: `"El vehículo con patente ABCD12 ya existe en el sistema"`
  - Ejemplo: `"La licencia de conducir está vencida"`
- **Documentación técnica**: Español
- **Archivos de traducción Laravel**: Español (`resources/lang/es/validation.php`)

**Tests:**
- Código en inglés: `VehicleTest`, `test_can_create_vehicle()`
- Nombres de tests descriptivos en inglés: `test_vehicle_license_plate_validation_fails_with_vowels()`
- Mensajes de aserción en español: `$this->assertTrue($vehicle->isValid(), "El vehículo debe ser válido")`

**Models:**
- Relaciones Eloquent bien definidas
- Accessors y Mutators para formateo
- Scopes para queries comunes
- Eventos del modelo (creating, updating, etc.) para lógica automática
- **Validaciones**: Usar `ChileanValidationHelper` para patentes y RUT

**Services:**
- Lógica de negocio compleja
- Coordinación entre múltiples modelos
- Validaciones de negocio (más allá de validación de formulario)
- **Cálculos monetarios**: Usar `MonedaHelper::redondearChileno()` para todos los cálculos de precios/costos
- **Validaciones chilenas**: Usar `ChileanValidationHelper::validarPatente()` y `ChileanValidationHelper::validarRut()`
- Ejemplo: `MaintenanceService::scheduleNextMaintenance($maintenance)`

**Actions:**
- Operaciones atómicas reutilizables
- Una clase = una acción específica
- Ejemplo: `CalcularProximoMantenimiento::execute($mantenimiento, $tipoMantenimiento)`

**Jobs:**
- Tareas asíncronas programadas
- Usar Laravel Queue para procesos pesados
- Jobs para alertas diarias, emails masivos, etc.

**Events/Listeners:**
- Desacoplar acciones secundarias
- Ejemplo: Al completar mantenimiento → Evento → Listener calcula próximo → Listener genera alerta

### 10.3. Uso de Eventos, Jobs y Notificaciones

**Eventos:**
- `MaintenanceCompleted`: Disparado al marcar mantenimiento como completado
- `AlertGenerated`: Disparado al crear nueva alerta crítica
- `VehicleCreated`: Disparado al crear vehículo (para inicializaciones)
- `DriverAssigned`: Disparado al asignar conductor a vehículo (para auditoría)

**Listeners:**
- `CalculateNextMaintenanceListener`: Escucha `MaintenanceCompleted`
- `SendEmailAlertListener`: Escucha `AlertGenerated` si es crítica
- `RegisterAuditListener`: Escucha eventos críticos para log

**Jobs Programados (Scheduler):**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new GenerateMaintenanceAlerts)->dailyAt('08:00');
    $schedule->job(new GenerateCertificationAlerts)->dailyAt('08:30');
    $schedule->job(new GenerateLicenseAlerts)->dailyAt('08:45');
    $schedule->job(new SendDailyAlertSummary)->dailyAt('09:00');
}
```

**Notificaciones Laravel:**
- Usar sistema de Notifications para emails
- Canales: `mail`, futuro `database` para notificaciones in-app
- Ejemplo: `AlertaCriticaNotification` implementa `ShouldQueue` para envío asíncrono

### 10.4. Livewire Components y DataTables

**Estructura de Componentes Livewire:**

**Tablas Interactivas (con DataTables):**
- `VehicleTable`: Listado de vehículos con búsqueda, filtrado, ordenamiento, paginación server-side
- `MaintenanceTable`: Listado de mantenimientos con filtros avanzados (fecha, tipo, vehículo, estado)
- `AlertTable`: Listado de alertas con actualización en tiempo real (polling o eventos)
- `DriverTable`: Listado de conductores con filtros y búsqueda
- `AssignmentHistoryTable`: Historial de asignaciones de conductores

**Formularios Interactivos:**
- `VehicleForm`: Crear/editar vehículo con validación en tiempo real
- `MaintenanceForm`: Crear/editar mantenimiento con selectores dinámicos
- `DriverForm`: Crear/editar conductor con validación de RUT en tiempo real
- `DocumentForm`: Crear/editar certificación con carga de archivos

**Componentes de Dashboard:**
- `Dashboard`: Panel principal con KPIs y métricas
- `AlertSummary`: Resumen de alertas críticas (actualización automática)
- `VehicleStatusCards`: Cards de estado de vehículos
- `MaintenanceCalendar`: Calendario de mantenimientos programados

**Características de DataTables:**
- **Server-side processing**: Para grandes volúmenes de datos
- **Búsqueda en tiempo real**: En todas las columnas o columnas específicas
- **Filtrado avanzado**: Por columnas individuales (dropdowns, date pickers)
- **Ordenamiento**: Por múltiples columnas simultáneamente
- **Paginación**: Configurable (10, 25, 50, 100 registros por página)
- **Exportación integrada**: Botones de exportar a Excel/PDF/CSV en la misma tabla
- **Responsive**: Adaptable a dispositivos móviles
- **Estado persistente**: Mantiene filtros y ordenamiento en refresh

**Integración Livewire + DataTables:**
- DataTables se inicializa en el método `mount()` o `render()` del componente
- Actualización reactiva cuando cambian filtros o búsqueda (sin recargar página)
- Polling automático para alertas críticas (cada 30 segundos por ejemplo)
- Eventos Livewire para actualizar tabla cuando se crea/edita/elimina registro

### 10.5. Funcionalidades de Exportación

**Exportaciones Disponibles:**

**1. Exportación a Excel (.xlsx, .xls, .csv):**
- **VehiclesExport**: Listado completo de vehículos con todos sus datos
  - Columnas: Patente, Marca, Modelo, Año, Categoría, Estado, Conductor Actual, etc.
- **MaintenancesExport**: Historial de mantenimientos
  - Columnas: Vehículo, Tipo, Fecha Programada, Fecha Ejecutada, Estado, Costo, Técnico, etc.
  - Opción: Filtrar por vehículo, rango de fechas, tipo de mantenimiento
- **AlertsExport**: Lista de alertas
  - Columnas: Vehículo, Tipo, Severidad, Estado, Fecha Generada, Fecha Límite, etc.
  - Opción: Filtrar por severidad, estado, tipo
- **DriversExport**: Lista de conductores
  - Columnas: RUT, Nombre, Teléfono, Email, Licencia, Estado Licencia, etc.
- **MaintenanceHistoryExport**: Historial completo de mantenimientos de un vehículo específico
  - Incluye todos los mantenimientos con detalles completos
- **InventoryExport**: Lista de repuestos con stock actual
  - Columnas: Código, Descripción, Marca, Stock Disponible, Stock Mínimo, Precio Referencia, etc.
- **PurchasesExport**: Historial de compras de repuestos
  - Columnas: Proveedor, Fecha Compra, Fecha Recepción, Total, Estado, Items, etc.
- **StockMovementsExport**: Movimientos de inventario
  - Columnas: Repuesto, Tipo, Cantidad, Fecha, Referencia, Usuario, etc.

**2. Exportación a PDF:**
- **MaintenanceHistoryPdf**: Historial completo de mantenimientos por vehículo (documento oficial)
  - Formato profesional con logo de empresa
  - Todas las certificaciones y documentos
  - Historial de mantenimientos ordenado por fecha
  - Historial de asignaciones de conductores
  - Gráficos de uso (kilometraje, horómetro) si aplica
- **VehicleSummaryPdf**: Resumen ejecutivo de estado de un vehículo
- **FleetStatusPdf**: Estado general de toda la flota (dashboard en PDF)

**3. Exportación a CSV:**
- Versión simplificada de Excel sin formato
- Para análisis en herramientas externas (BI, Excel avanzado, etc.)

**Implementación:**
- Clases Export usando `Maatwebsite/Laravel-Excel`
- Clases PDF usando `Barryvdh/Laravel-DomPDF` o `dompdf/dompdf`
- Botones de exportación en cada tabla/listado
- Filtros aplicados se mantienen en la exportación
- Opción de exportar todos los registros o solo los visibles en la página actual

### 10.6. Consideraciones para Testing

**Estrategia de testing:**

1. **Unit Tests:**
   - Services: Lógica de cálculo de fechas, validaciones de negocio
   - Actions: Operaciones atómicas
   - Models: Accessors, mutators, scopes

2. **Feature Tests:**
   - Endpoints de API/Controllers
   - Flujos completos (crear vehículo → programar mantenimiento → completar)

3. **Database Tests:**
   - Usar `RefreshDatabase` trait
   - Seeders para datos de prueba
   - Verificar integridad de relaciones

4. **Job Tests:**
   - Mock de jobs o uso de `Queue::fake()`
   - Verificar que jobs se despachan correctamente

**Factories y Seeders:**
- `VehicleFactory`: Crear vehículos de prueba con diferentes categorías (usar patentes válidas chilenas)
- `MaintenanceFactory`: Crear mantenimientos en diferentes estados
- `DriverFactory`: Crear conductores de prueba (usar RUT válidos chilenos)
- `DatabaseSeeder`: Datos iniciales (categorías, tipos de mantenimiento) con datos válidos

**Tests de Validaciones Chilenas:**
- Tests específicos para validación de patentes:
  - Formato correcto (4 letras + 2 dígitos para autos, 3 letras + 2 dígitos para motos)
  - Letras permitidas (sin vocales, sin M, N, Ñ, Q)
  - Normalización (mayúsculas, sin espacios)
  - Patentes inválidas (con vocales, formato incorrecto, etc.)
- Tests específicos para validación de RUT:
  - Formato correcto (con y sin puntos)
  - Cálculo correcto del dígito verificador (módulo 11)
  - RUT con dígito verificador K
  - RUT inválidos (DV incorrecto, formato erróneo)

**Consideraciones:**
- Usar PostgreSQL en tests (o al menos configurar para que sea compatible)
- Tests de integridad referencial
- Tests de cálculos de fechas con diferentes escenarios (año nuevo, meses de 28/30/31 días)
- Tests de validaciones de patentes y RUT con casos edge chilenos


## 11. PLAN DE DESARROLLO POR FASES

### FASE 1: NÚCLEO FUNCIONAL MÍNIMO (MVP)

**Objetivo:** Sistema básico funcional para registrar vehículos y mantenimientos manualmente.

**Entregables:**

1. **Setup inicial con Docker:**
   - Configuración Docker Compose para Laravel (PHP/Laravel, Nginx, Redis opcional)
   - **PostgreSQL**: Crear nueva base de datos `melichinkul_db` en PostgreSQL existente del host Docker
     - Configuración `.env` con: `DB_HOST=host.docker.internal`
     - Credenciales: usuario `admin`, base de datos `melichinkul_db`
     - Verificar conectividad desde contenedor a PostgreSQL del host
     - Crear base de datos `melichinkul_db` antes de ejecutar migraciones
   - Instalación Laravel en contenedor
   - Configuración de entorno `.env` para Docker con credenciales PostgreSQL
   - Backup de base de datos `melichinkul_db` antes de migraciones importantes
   - Estructura de base de datos básica (vehicles, vehicle_categories, maintenances, maintenance_types)
   - Autenticación Laravel estándar
   - Roles básicos (admin, supervisor, administrativo, tecnico)
   - Instalación y configuración de Livewire
   - Instalación y configuración de DataTables (yajra/laravel-datatables)

2. **Gestión de vehículos con Livewire:**
   - Componente Livewire `VehicleTable` con DataTables integrado
   - Componente Livewire `VehicleForm` para crear/editar
   - CRUD completo de vehículos
   - Categorías predefinidas (Utilitarios, Camionetas, Camiones Grúa, Maquinaria)
   - Listado con filtros básicos (búsqueda, paginación, ordenamiento)
   - Vista detalle de vehículo
   - Validación de patentes chilenas (ChileanValidationHelper)

3. **Gestión de mantenimientos con Livewire:**
   - Componente Livewire `MaintenanceTable` con DataTables
   - Componente Livewire `MaintenanceForm` para crear/editar
   - CRUD de mantenimientos manual
   - Estados básicos (programado, en_proceso, completado)
   - Asociación vehículo → mantenimiento
   - Registro básico de contadores (kilometraje/horómetro)

4. **Historial básico:**
   - Vista de mantenimientos por vehículo
   - Filtros por fecha y tipo

**Tiempo estimado:** 2-3 semanas

**Criterios de éxito:**
- Administrador puede dar de alta vehículos
- Supervisor puede crear mantenimientos programados
- Técnico puede registrar mantenimientos completados
- Se puede consultar historial por vehículo

---

### FASE 2: ALERTAS Y AUTOMATIZACIÓN

**Objetivo:** Sistema automático de alertas y programación inteligente de mantenimientos.

**Entregables:**

1. **Sistema de alertas:**
   - Modelo de alertas completo
   - Generación automática de alertas (jobs diarios)
   - Vista de alertas con filtros y severidad
   - Cierre de alertas

2. **Programación automática:**
   - Cálculo automático de próximos mantenimientos
   - Tipos de mantenimiento con frecuencias
   - Generación automática al completar mantenimiento preventivo

3. **Notificaciones por email:**
   - Configuración SMTP Gmail
   - Templates de email para alertas
   - Envío automático de alertas críticas
   - Resumen diario opcional

4. **Contadores de uso:**
   - Registro histórico de contadores
   - Múltiples tipos (km, horómetro, horas grúa)
   - Cálculo de próximos mantenimientos basado en uso real

**Tiempo estimado:** 2-3 semanas

**Criterios de éxito:**
- Sistema genera alertas automáticamente todos los días
- Usuarios reciben emails de alertas críticas
- Al completar mantenimiento, se programa el próximo automáticamente
- Alertas se cierran automáticamente al resolver causa

---

### FASE 3: MEJORAS OPERATIVAS Y FUNCIONALIDADES AVANZADAS

**Objetivo:** Funcionalidades que mejoran la operación diaria y trazabilidad.

**Entregables:**

1. **Certificaciones / Documentos Legales (CRÍTICO):**
   - CRUD completo de documentos legales obligatorios chilenos
   - **Documentos obligatorios básicos**: Permiso de Circulación, Revisión Técnica, SOAP, Análisis de Gases
   - Documentos especiales según tipo de vehículo (certificado grúa, certificado de carga, etc.)
   - Alertas automáticas de vencimiento (60 días, 15 días, día 0 - CRÍTICA)
   - **Almacenamiento de copias digitales (escaneos):**
     - Sistema de carga de archivos (PDF, JPG, PNG) para cada documento
     - Soporte para documentos con anverso y reverso (2 archivos por documento)
     - Visualización y descarga de escaneos desde la interfaz
     - Validación que requiere escaneo para documentos obligatorios
     - Almacenamiento seguro de archivos (storage local o en la nube)
     - Historial de escaneos (mantener escaneos de versiones anteriores al renovar)
   - Historial completo de renovaciones
   - **Importante**: Validación de que todos los documentos obligatorios estén vigentes Y tengan escaneo antes de permitir circulación

2. **Gestión de Inventario de Repuestos:**
   - Catálogo completo de repuestos (CRUD)
   - Gestión de proveedores (CRUD)
   - Sistema de compras de repuestos:
     - Crear orden de compra
     - Agregar items (repuestos, cantidades, precios)
     - Recibir compra (genera movimientos de entrada al inventario)
     - Estados de compra (pendiente, recibida, cancelada)
   - Control de stock:
     - Tabla de stock actual por repuesto
     - Configuración de stock mínimo (umbral de alerta)
     - Actualización automática de stock al recibir compras
     - Descuento automático de stock al usar repuestos en mantenimientos
   - Movimientos de inventario:
     - Registro de todas las entradas y salidas
     - Trazabilidad completa (compra, mantenimiento, ajuste manual)
   - Ajustes manuales de inventario (entradas y salidas)
   - Alertas de stock bajo y agotado
   - Componentes Livewire:
     - `RepuestoTable`: Listado de repuestos con stock
     - `CompraForm`: Formulario de compra con items
     - `StockTable`: Vista de stock actual con alertas
     - `MovimientoInventarioTable`: Historial de movimientos
   - Permisos diferenciados:
     - Administrativo: puede comprar, recibir, ajustar stock
     - Técnico: solo puede usar repuestos en mantenimientos (descuenta stock)
   - Cálculo de costos detallado

3. **Gestión de Conductores y Licencias (CRÍTICO):**
   - Componente Livewire `DriverTable` con DataTables
   - Componente Livewire `DriverForm` para crear/editar
   - CRUD de conductores (RUT, nombre, contacto)
   - Validación de RUT chileno (ChileanValidationHelper)
   - **Control completo de licencias de conducir:**
     - Registro de número de licencia, clase, fechas de emisión y vencimiento
     - Almacenamiento de imagen escaneada de licencia (anverso y reverso si es necesario)
     - Validación que BLOQUEA asignación de vehículos a conductores con licencia vencida o faltante
     - Sistema de renovación de licencias (marcar anterior, registrar nueva)
   - Sistema de asignación conductor → vehículo con validación automática de licencia vigente
   - Historial completo de asignaciones
   - Asociación automática de conductor en mantenimientos correctivos
   - Vista de conductor con estadísticas básicas y estado de licencia destacado
   - **Alertas automáticas de licencias:**
     - Advertencia a 60 días antes de vencer
     - CRÍTICA a 30 días antes de vencer
     - CRÍTICA a 15 días antes de vencer
     - CRÍTICA inmediata al vencer (día 0)

4. **Dashboard:**
   - Vista general con KPIs
   - Gráficos de mantenimientos por mes
   - Resumen de alertas críticas
   - Listado de vehículos por estado
   - Información de conductor actual por vehículo

5. **Plantillas de Mantenimiento:**
   - CRUD de plantillas de mantenimiento
   - Asociar repuestos a plantillas
   - Crear mantenimiento desde plantilla (pre-llena datos)
   - Componente Livewire `MaintenanceTemplateForm`

6. **Checklist de Mantenimiento:**
   - CRUD de checklist items por tipo de mantenimiento
   - Checklist automático al crear mantenimiento
   - Mecánico marca items completados durante reparación
   - Validación de items obligatorios antes de completar
   - Componente Livewire `MaintenanceChecklist`

7. **Evidencia en Mantenimientos:**
   - Subir fotos/documentos/videos como evidencia
   - Visualización de evidencia en historial
   - Útil para seguros y auditorías
   - Componente Livewire `MaintenanceEvidenceUpload`

8. **Aprobación de Mantenimientos:**
   - Sistema de aprobación para mantenimientos con costo alto
   - Configuración de umbral de aprobación
   - Estado "pendiente_aprobacion"
   - Supervisor aprueba o rechaza con justificación
   - Notificaciones cuando requiere aprobación

9. **Validaciones Mejoradas:**
   - Validación de documentos obligatorios antes de completar mantenimiento
   - Validación de fechas lógicas (fecha_fin >= fecha_inicio)
   - Confirmaciones de acciones críticas (modales)
   - Validación de stock antes de solicitar repuestos

10. **Dashboard Mejorado:**
    - Widget de mantenimientos en curso con progreso
    - Gráfico de costos por vehículo (últimos 6 meses)
    - Lista de vehículos que requieren atención inmediata
    - Métricas en tiempo real
    - Componente Livewire `EnhancedDashboard`

11. **Notificaciones en Tiempo Real:**
    - Laravel Echo + Broadcasting (Pusher o Redis)
    - Notificaciones push para alertas críticas
    - Notificaciones cuando mecánico solicita repuestos
    - Notificaciones cuando stock llega a mínimo
    - Notificaciones cuando mantenimiento requiere aprobación
    - Badge en navegación con contador actualizado
    - Tabla `notificaciones` para notificaciones in-app

12. **Auditoría:**
    - Log de acciones críticas
    - Vista de historial de cambios
    - Trazabilidad completa
    - Auditoría de cambios de asignaciones de conductores
    - Historial de cambios de costos (`mantenimiento_costos_historial`)
    - Logs de acceso al sistema

6. **Mejoras de UX y Exportación:**
   - Búsqueda avanzada en DataTables
   - Filtros mejorados (integrados en DataTables)
   - **Exportación completa a múltiples formatos:**
     - Excel (xlsx) con formato y estilos
     - PDF para documentos oficiales (historial de vehículo)
     - CSV para análisis externos
   - Filtro por conductor en mantenimientos
   - Botones de exportación en cada tabla/listado
   - Exportación mantiene filtros aplicados

**Tiempo estimado:** 2-3 semanas

**Criterios de éxito:**
- Gestión completa de certificaciones con alertas
- Costos de mantenimientos detallados
- Sistema de asignación de conductores funcional con historial completo
- Dashboard informativo para toma de decisiones
- Auditoría funcional para trazabilidad
- Posibilidad de identificar conductor asociado a cada mantenimiento correctivo

---

### FASE 4: OPTIMIZACIONES Y FUNCIONALIDADES AVANZADAS

**Objetivo:** Mejoras de rendimiento, análisis avanzados y preparación para futuro.

**Entregables:**

1. **Caché Inteligente:**
   - Cachear estadísticas de gasto por vehículo
   - Cachear stock disponible
   - Cachear dashboard KPIs
   - Invalidación selectiva con tags
   - Configuración de TTL por tipo de dato

2. **Jobs Asíncronos para Cálculos Pesados:**
   - Calcular estadísticas en background
   - Generar reportes PDF en cola
   - Exportaciones grandes en background
   - Cálculo de próximos mantenimientos en batch

3. **Búsqueda y Autocompletado Inteligente:**
   - Búsqueda rápida por patente en header
   - Atajos de teclado para acciones frecuentes
   - Autocompletado de descripciones de trabajo
   - Sugerencias de repuestos basadas en tipo de mantenimiento

4. **Análisis Avanzados:**
   - Análisis de costos por conductor (reportes detallados)
   - Predicción de costos anuales por vehículo
   - Comparativa de proveedores (costos, tiempos de entrega)
   - Identificación de patrones de mal uso
   - Gráficos de tendencias

5. **Exportación Automática:**
   - Reportes mensuales automáticos por email
   - Configurable por usuario
   - Formatos: Excel o PDF según preferencia

6. **Backup Automático:**
   - Backup diario de escaneos de documentos
   - Almacenamiento redundante
   - Scripts de restauración
   - Verificación de integridad

7. **Logs de Acceso:**
   - Registro de accesos al sistema
   - IP, User Agent, Timestamp
   - Detección de accesos sospechosos
   - Útil para auditorías de seguridad

**Tiempo estimado:** 2-3 semanas

**Criterios de éxito:**
- Sistema más responsivo con caché
- Reportes se generan sin bloquear usuarios
- Análisis avanzados disponibles para toma de decisiones
- Backup automático funcionando

---

### FASE 5: ENDURECIMIENTO Y ESCALABILIDAD

### FASE 5: ENDURECIMIENTO Y ESCALABILIDAD

**Objetivo:** Preparar sistema para producción y crecimiento.

**Entregables:**

1. **Optimizaciones:**
   - Índices de base de datos optimizados
   - Caché de consultas frecuentes (Redis)
   - Optimización de queries N+1
   - Paginación del lado del servidor en DataTables (para grandes volúmenes)
   - Optimización de componentes Livewire (lazy loading donde sea posible)

2. **Validaciones y reglas de negocio:**
   - Validaciones exhaustivas
   - Reglas de negocio en Services
   - Manejo robusto de errores
   - Mensajes de error claros

3. **Testing:**
   - Suite de tests unitarios
   - Tests de integración de flujos críticos
   - Tests de jobs programados

4. **Documentación:**
   - Documentación técnica
   - Manual de usuario
   - Guía de configuración

5. **Preparación para futuro:**
   - Estructura para API REST
   - Campos preparados para integraciones (GPS, telemetría)
   - Consideraciones de multi-tenant (si aplica)

**Tiempo estimado:** 2 semanas

**Criterios de éxito:**
- Sistema responde rápidamente con muchos registros
- Tests cubren flujos críticos
- Documentación completa
- Sistema listo para despliegue en producción

---

### ROADMAP FUTURO (Post-MVP)

**Funcionalidades adicionales consideradas:**
- App móvil para técnicos
- Integración con GPS/telemetría
- **Reporte avanzado de análisis de conductores:**
  - Identificación de conductores con mayor frecuencia de mantenimientos correctivos
  - Comparativa de costos de mantenimiento por conductor
  - Gráficos de tendencias de problemas por conductor
  - Alertas automáticas si un conductor supera umbral de problemas
- Módulo de reportes avanzados y BI
- Gestión de talleres externos
- Planificación de mantenimientos agrupados
- Notificaciones SMS/WhatsApp
- Dashboard de costos y análisis financiero
- Gestión completa de conductores (permisos, licencias, capacitaciones, historial laboral)


## 11. MEJORAS Y OPTIMIZACIONES ADICIONALES

### 11.1. Caché Inteligente

**Estrategia de caché:**
- **Estadísticas de gasto por vehículo:** Cachear cálculos pesados, invalidar al completar mantenimiento
- **Stock disponible:** Cachear stock actual, invalidar en movimientos de inventario
- **Dashboard KPIs:** Cachear métricas principales, actualizar cada hora o al completar acciones críticas
- **Listados frecuentes:** Cachear listados de vehículos, repuestos, conductores (invalidar al crear/editar)

**Implementación:**
- Usar Laravel Cache (Redis recomendado)
- Tags para invalidación selectiva
- TTL configurable por tipo de dato

### 11.2. Jobs Asíncronos para Cálculos Pesados

**Jobs en cola:**
- **Calcular estadísticas de gasto:** Ejecutar en background después de completar mantenimiento
- **Generar reportes PDF:** No bloquear al usuario, enviar por email cuando esté listo
- **Exportaciones grandes:** Procesar en background, notificar cuando esté disponible
- **Cálculo de próximos mantenimientos:** Procesar en batch diario

**Beneficio:** Mejor experiencia de usuario, sistema más responsivo

### 11.3. Búsqueda y Autocompletado Inteligente

**Búsqueda rápida:**
- Búsqueda por patente en header (acceso rápido desde cualquier página)
- Atajos de teclado para acciones frecuentes (Ctrl+N para nuevo, etc.)

**Autocompletado:**
- Al escribir descripción de trabajo, sugerir descripciones anteriores similares
- Autocompletado de códigos de repuestos al escribir
- Sugerencias de repuestos basadas en tipo de mantenimiento

**Beneficio:** Ahorra tiempo, estandariza descripciones

### 11.4. Validaciones de Fechas Lógicas

**Validaciones implementadas:**
- No permitir `fecha_fin < fecha_inicio` en mantenimientos
- No permitir `fecha_programada` en el pasado (salvo excepciones con justificación)
- No permitir `fecha_vencimiento < fecha_emision` en certificaciones
- Validar que `fecha_recepcion >= fecha_compra` en compras

**Confirmaciones de acciones críticas:**
- Modal de confirmación al cerrar alerta crítica (requiere justificación)
- Confirmación al eliminar compra o movimiento de inventario
- Confirmación al cambiar estado de mantenimiento a "cancelado"

### 11.5. Logs de Acceso y Seguridad

**Registro de accesos:**
- Tabla `accesos_sistema` para registrar:
  - Usuario que accedió
  - IP y User Agent
  - Timestamp
  - Página/acción accedida
- Útil para auditorías de seguridad
- Detección de accesos sospechosos

### 11.6. Backup Automático de Documentos

**Estrategia de backup:**
- Backup diario automático de escaneos de documentos
- Almacenamiento redundante (local + nube opcional)
- Scripts de restauración documentados
- Verificación periódica de integridad de backups

### 11.7. Exportación Automática de Reportes

**Reportes automáticos:**
- Exportar reportes mensuales automáticamente por email
- Para gerencia/supervisores
- Configurable por usuario (qué reportes recibir, frecuencia)
- Formato: Excel o PDF según preferencia

### 11.8. Análisis de Costos por Conductor

**Reportes avanzados:**
- "Conductores con más mantenimientos correctivos"
- Identificación de patrones de mal uso
- Comparativa de costos de mantenimiento por conductor
- Gráficos de tendencias
- Costo promedio de mantenimientos correctivos por conductor

**Beneficio:** Permite identificar conductores problemáticos y tomar acciones correctivas

### 11.9. Predicción de Costos

**Análisis predictivo:**
- Basado en historial, estimar costo anual por vehículo
- Útil para presupuestos
- Identificar vehículos con costos crecientes
- Alertas si costos superan promedios históricos

### 11.10. Comparativa de Proveedores

**Análisis de proveedores:**
- Reporte de costos por proveedor
- Identificar proveedores más económicos
- Tiempo promedio de entrega por proveedor
- Calidad de productos (basado en devoluciones o problemas)

### 11.11. Múltiples Ubicaciones de Stock (Futuro)

**Preparación:**
- Tabla `ubicaciones_stock` para múltiples bodegas/ubicaciones
- Stock por ubicación
- Transferencias entre ubicaciones
- Reportes por ubicación

**Nota:** Implementar cuando la operación crezca y requiera múltiples bodegas

### 11.12. Control de Lotes y Vencimientos (Futuro)

**Para repuestos/insumos con fecha de vencimiento:**
- Tabla `lotes_repuestos` para control de lotes
- Sistema FIFO (First In, First Out)
- Alertas de vencimiento próximo
- Control de rotación de inventario

**Nota:** Implementar cuando se requiera control estricto de vencimientos (ej: aceites, líquidos con fecha de caducidad)

### 11.13. Modo Offline (Futuro)

**Para mecánicos en terreno:**
- Sincronización cuando vuelve la conexión
- Almacenamiento local de datos
- Resolución de conflictos al sincronizar

**Nota:** Requiere app móvil o PWA (Progressive Web App)

### 11.14. Integración con Sistemas de Facturación (Futuro)

**Preparación:**
- Campo `numero_factura` en compras (ya existe)
- Sincronización con sistema contable
- API para exportar datos de compras
- Integración con ERP si aplica

## 12. RIESGOS Y CONSIDERACIONES REALES

### 12.1. Errores Comunes en este Tipo de Sistemas

#### 12.1.1. **Sobresimplificación de Contadores**
**Problema:** Asumir que todos los vehículos usan solo kilometraje.

**Solución:** Modelo flexible desde el inicio con tabla `contadores_uso` que soporta múltiples tipos.

#### 12.1.2. **Falta de Trazabilidad**
**Problema:** No registrar quién hizo qué y cuándo. Imposible auditar después.

**Solución:** 
- Tabla de auditoría desde Fase 1
- Timestamps y usuario en todas las acciones críticas
- Soft deletes para mantener historial

#### 12.1.3. **Alertas Duplicadas o Perdidas**
**Problema:** Generar alertas duplicadas o perder alertas por bugs en jobs.

**Solución:**
- Verificar existencia antes de crear alerta
- Jobs idempotentes (pueden ejecutarse múltiples veces sin problemas)
- Logs de ejecución de jobs

#### 12.1.4. **Cálculos de Fechas Incorrectos**
**Problema:** No considerar días hábiles, meses de diferente duración, años bisiestos.

**Solución:**
- Usar librerías de fechas robustas (Carbon en Laravel)
- Validar cálculos con casos edge (31 de enero + 1 mes = ?)
- Tests específicos para cálculos de fechas

#### 12.1.5. **Falta de Validación de Negocio**
**Problema:** Permitir datos inválidos (ej: mantenimiento completado sin fecha_fin).

**Solución:**
- Validaciones en múltiples capas (Request, Model, Service)
- Transacciones de base de datos para operaciones complejas
- Reglas de negocio claramente documentadas

#### 12.1.6. **Uso Incorrecto de Decimales en Valores Monetarios**
**Problema:** Usar DECIMAL para almacenar valores monetarios en Chile, donde solo se usan enteros.

**Solución:**
- Usar `BIGINT` (entero) para todos los campos monetarios (costos, precios, subtotales)
- Implementar función de redondeo chileno obligatoria:
  - Si termina en 5 o más → redondea al siguiente entero
  - Si termina en 4 o menos → baja al anterior entero
- Aplicar redondeo automáticamente al ingresar valores y en cálculos (subtotales, totales)
- Helper dedicado: `MonedaHelper::redondearChileno($valor)` para consistencia
- Tests específicos para validar redondeo correcto

**Ejemplos de redondeo:**
- $1.234 → $1.234 (sin cambio)
- $1.235 → $1.240
- $1.239 → $1.240
- $1.241 → $1.240
- $1.244 → $1.240
- $1.245 → $1.250

#### 12.1.7. **No Registrar Responsabilidad de Conductores**
**Problema:** No asociar mantenimientos correctivos con conductores, imposible detectar patrones de mal uso.

**Solución:**
- Sistema de asignación de conductores con historial completo
- Asociación automática de conductor en mantenimientos correctivos
- Capturar conductor_actual_id del vehículo al momento del mantenimiento
- Permitir análisis posterior de patrones por conductor

#### 12.1.8. **Patentes y RUT sin Validar**
**Problema:** Patentes mal ingresadas (formato inválido) o RUT con dígito verificador incorrecto generan datos ilegales o inútiles en operaciones y auditorías.

**Solución:**
- Helper dedicado `ChileanValidationHelper` con funciones:
  - `validarPatente($patente, $tipoVehiculo)`: Valida formato según tipo de vehículo (autos: 4 letras + 2 dígitos, motos: 3 letras + 2 dígitos)
  - `normalizarPatente($patente)`: Normaliza a mayúsculas, sin espacios
  - `validarRut($rut)`: Valida formato y calcula dígito verificador (módulo 11)
  - `normalizarRut($rut)`: Normaliza a formato sin puntos, mantiene guion
- Validaciones en Form Requests de Laravel (ej: `StoreVehicleRequest`, `StoreDriverRequest`)
- Tests exhaustivos con casos edge chilenos
- Mensajes de error en español explicando qué está mal

**Validaciones de Patentes:**
- **Autos (vehículos de 4+ ruedas)**: Formato `LLLLNN` (4 letras + 2 dígitos)
  - Ejemplos válidos: `ABCD12`, `BCDF34`, `JKLP56`
  - Letras permitidas: B, C, D, F, G, H, J, K, L, P, R, S, T, V, W, X, Y, Z
  - Invalidar: vocales (A, E, I, O, U), letras prohibidas (M, N, Ñ, Q), formatos incorrectos
- **Motos (vehículos de 2-3 ruedas)**: Formato `LLLNN` (3 letras + 2 dígitos)
  - Ejemplos válidos: `BCD12`, `FGH34`, `JKL56`
- **Normalización**: Convertir a mayúsculas, eliminar espacios y guiones
- **Regex sugerido**: Para autos `^[BCDFGHJKLPQRSTVWXYZ]{4}\d{2}$`, para motos `^[BCDFGHJKLPQRSTVWXYZ]{3}\d{2}$`

**Validaciones de RUT:**
- **Formato**: `NNNNNNNN-D` o `NN.NNN.NNN-D` (con o sin puntos)
  - Ejemplos válidos: `12345678-9`, `12.345.678-9`, `12345678-K`
  - Cuerpo numérico: 7 u 8 dígitos
  - Dígito verificador: 0-9 o K (mayúscula o minúscula)
- **Algoritmo Módulo 11**:
  1. Tomar cuerpo numérico (sin puntos, sin DV)
  2. Multiplicar dígitos de derecha a izquierda por serie: 2, 3, 4, 5, 6, 7 (repetir serie si es necesario)
  3. Sumar todos los productos
  4. Dividir suma por 11, obtener resto
  5. Calcular: `11 - resto`
  6. Si resultado es 11 → DV es 0; si es 10 → DV es K; sino → DV es el resultado
- **Normalización**: Eliminar puntos, mantener guion, convertir DV a mayúscula
- **Regex sugerido**: `^(\d{7,8})-([0-9Kk])$` (para validación de formato)

**Tests Requeridos:**
- Patentes válidas de autos y motos
- Patentes inválidas (con vocales, letras prohibidas, formato incorrecto)
- Normalización de patentes (mayúsculas, sin espacios)
- RUT válidos (con y sin puntos)
- RUT con dígito verificador K
- RUT inválidos (DV incorrecto, formato erróneo)
- Cálculo correcto de dígito verificador (casos edge: resto 0 → DV 0, resto 1 → DV K)

### 12.2. Qué Cuidar Especialmente en Empresa Operativa

#### 12.2.1. **Disponibilidad del Sistema**
**Problema:** Si el sistema cae, se pierde capacidad de planificar mantenimientos.

**Solución:**
- Sistema debe ser estable y confiable
- Considerar alta disponibilidad si es crítico
- Backup automático de base de datos
- Plan de recuperación ante desastres

#### 12.2.2. **Facilidad de Uso para Técnicos**
**Problema:** Técnicos no son usuarios de sistemas, necesitan interfaz simple.

**Solución:**
- UI intuitiva y clara
- Formularios simples con campos mínimos obligatorios
- Flujos guiados para acciones comunes
- Capacitación adecuada

#### 12.2.3. **Registro Rápido en Terreno**
**Problema:** Técnicos en terreno necesitan registrar rápido, no tienen tiempo para formularios complejos.

**Solución:**
- Formularios Livewire optimizados (carga rápida, validación en tiempo real)
- Campos con valores por defecto inteligentes
- Autocompletado en campos de vehículos/conductores
- Posibilidad de registrar básico y completar después
- DataTables para búsqueda rápida de vehículos
- Responsive design para uso en tablets/móviles
- Futuro: app móvil offline-first

#### 12.2.4. **Alertas que Realmente Importan**
**Problema:** Demasiadas alertas hacen que se ignoren todas.

**Solución:**
- Severidad bien calibrada (solo críticas para emails automáticos)
- Configuración de umbrales ajustables
- Posibilidad de pausar alertas temporales

#### 12.2.5. **Integridad de Datos Críticos**
**Problema:** Datos incorrectos (ej: fecha de vencimiento mal ingresada) generan alertas falsas o pierden alertas reales.

**Solución:**
- Validaciones estrictas en fechas
- Confirmaciones para cambios críticos
- Auditoría de cambios en datos sensibles

#### 12.2.6. **Registro de Conductores para Trazabilidad**
**Problema:** Si no se registra quién manejaba el vehículo cuando ocurrió un problema, es imposible identificar patrones de mal uso o conductores problemáticos.

**Solución:**
- Sistema obligatorio de asignación conductor → vehículo
- Historial completo de asignaciones (no solo actual)
- Asociación automática de conductor en mantenimientos correctivos
- Reportes que permitan analizar: ¿cuántos mantenimientos correctivos tuvo cada conductor?
- Considerar hacer obligatorio registrar conductor para correctivos críticos

### 12.3. Qué Dejar Preparado para el Futuro

#### 12.3.0. **Entorno Docker para Desarrollo y Producción**
**Configuración Actual:**
- Ubuntu 24.04 Desktop con Docker instalado
- PostgreSQL existente en Docker
- Nueva base de datos: `melichinkul_db` (exclusiva para la aplicación Melichinkul)
- Conexión desde contenedores Laravel usando `host.docker.internal`

**Preparación:**
- Docker Compose configurado para desarrollo local
  - Contenedor Laravel (PHP/Laravel)
  - Contenedor Nginx para servir aplicación
  - Contenedor Redis (opcional) para cache y queues
  - PostgreSQL del host conectado vía `host.docker.internal`
- Configuración `.env` con credenciales PostgreSQL existentes:
  ```
  DB_CONNECTION=pgsql
  DB_HOST=host.docker.internal
  DB_PORT=5432
  DB_DATABASE=melichinkul_db
  DB_USERNAME=admin
  DB_PASSWORD=***  # Configurar en .env; no subir credenciales al repositorio
  ```
- Preparar configuración para despliegue en producción:
  - Opción 1: Docker Compose en servidor (similar a desarrollo)
  - Opción 2: Docker Swarm o Kubernetes (si se requiere alta disponibilidad)
  - Considerar si PostgreSQL irá en contenedor en producción o servidor dedicado
- Scripts de backup de base de datos
  - Backup de base de datos `melichinkul_db`
  - Scripts para backup antes de migraciones
- Health checks para servicios Docker
- Documentación de despliegue con Docker
- **Consideración importante**: Verificar que `host.docker.internal` funcione en el entorno objetivo, o usar IP del host/red configurada

**Beneficio:** Despliegue consistente entre desarrollo y producción, escalabilidad fácil, reutilización de infraestructura PostgreSQL existente.

#### 12.3.1. **Estructura API-First**
**Preparación:**
- Controllers que pueden servir tanto web como API
- Resource classes de Laravel para respuestas JSON consistentes
- Versionado de API desde el inicio (`/api/v1/`)

**Beneficio:** Facilita desarrollo de app móvil o integraciones futuras.

#### 12.3.2. **Campos para Integraciones**
**Preparación:**
- Campo `external_id` en vehículos (para integración con GPS u otros sistemas)
- Campo `metadata` JSONB en tablas principales (para datos adicionales sin migraciones)
- Tabla `integraciones` para configurar fuentes externas

**Beneficio:** Integración con sistemas GPS, telemetría, ERP sin reestructuración.

**Nota sobre sistema Copec:**
- Actualmente no se requiere integración con el sistema de tarjetas Copec
- Si en el futuro se necesita cruzar datos de combustible con mantenimientos, la estructura permite agregar campos de referencia o integración sin afectar el diseño actual

#### 12.3.3. **Multi-tenant Ready (si aplica)**
**Preparación:**
- Considerar si en el futuro puede haber múltiples "departamentos" o "sucursales"
- Si es probable, estructura con `organizacion_id` o similar desde el inicio

**Beneficio:** Escalabilidad sin migración costosa.

#### 12.3.4. **Sistema de Notificaciones Extensible**
**Preparación:**
- Abstracción para canales de notificación (mail, SMS, push)
- Tabla `notificaciones` en base de datos (además de emails)
- Jobs asíncronos para todos los envíos

**Beneficio:** Agregar SMS/WhatsApp sin cambiar lógica core.

#### 12.3.5. **Reportes y Análisis**
**Preparación:**
- Datos estructurados que faciliten queries analíticas
- Campos calculados almacenados para reportes rápidos (ej: `costo_total_mes`)
- Considerar estructura de data warehouse si el volumen crece mucho

**Beneficio:** BI y reportes avanzados sin afectar sistema transaccional.

#### 12.3.6. **Internacionalización (i18n)**
**Preparación:**
- Strings traducibles desde el inicio (aunque solo español inicialmente)
- Formateo de fechas/monedas según locale

**Beneficio:** Expansión a otros países si aplica.

---

## 13. IMPLEMENTACIÓN INTEGRAL: CRITERIOS Y ESPECIFICACIONES

Este capítulo integra las **mejoras estratégicas de arquitectura y lógica de negocio** que deben aplicarse en el núcleo del sistema. Las versiones de referencia son **Laravel 12.x**, **Livewire 4.x** y **PHP 8.2+**.

---

### 13.1. Gestión de Inventario con Stock Crítico Dinámico (Repuestos)

**Objetivo:** Inventario inteligente que pasa de umbral manual a uno calculado por consumo real.

- **Fase Inicial (Mes 0-3):**  
  El sistema usa el campo `stock_minimo_manual` definido por el usuario para disparar alertas.

- **Fase Dinámica (Mes 3+):**  
  Si existen **más de 90 días** de historial de movimientos del repuesto, el stock crítico se calcula como:  
  **(Promedio de consumo mensual de los últimos 3 meses) × 1.5**

- **Implementación:**
  - **Atributo** (o accessor) en el modelo `Repuesto` que resuelva automáticamente si se usa manual o dinámico.
  - **Service Layer** dedicado para los cálculos (no en controladores ni en el modelo).
  - En la UI (Livewire): distinguir visualmente:
    - **Manual:** alerta Naranja
    - **Dinámico:** alerta Rojo

---

### 13.2. Infraestructura y Desarrollo (Docker y correo)

- **Docker:** `docker-compose.yml` preparado para desarrollo en **Ubuntu 24.04**.
- **PostgreSQL:** Acceso al que corre en el host local (p. ej. `host.docker.internal`). Ver 2.5.
- **Mailpit:** Contenedor Mailpit para capturar correos de alertas de vencimiento (Revisión Técnica, SOAP, Licencias) **sin enviar correos reales** en desarrollo y pruebas.

---

### 13.3. Lógica de Alertas y Documentación (Específico Chile)

- **Validaciones robustas:**
  - **RUT chileno:** con dígito verificador (algoritmo Módulo 11). Implementar como **Helper** y **Trait** reutilizable.
  - **Patentes chilenas:** formato antiguo y nuevo. **Helper** y **Trait** reutilizable.

- **Snooze (posponer alerta):**  
  El Administrador puede **silenciar** o posponer una alerta de mantenimiento o de documento vencido por **48–72 horas** (p. ej. cuando el trámite ya está en curso). Campos: `snoozed_until`, `snoozed_by`, `snoozed_reason`.

- **Evidencia obligatoria en mantenimientos:**
  - En Mantenimiento y Checklists: el sistema debe aceptar archivos **(PDF/JPG)** obligatorios.
  - **No se puede cerrar** un mantenimiento correctivo sin subir **factura** y/o **foto del repuesto instalado**.

---

### 13.4. Seguridad Operativa: Bloqueo de Asignación

**Middleware o servicio** que impida asignar un vehículo a un conductor si:

1. La **licencia de conducir** del conductor está **vencida**.
2. El vehículo tiene la **Revisión Técnica** **caducada**.

La asignación no debe persistirse en base de datos; debe mostrarse un mensaje claro al usuario.

---

### 13.5. Instrucciones Técnicas de Implementación

- **Migrations:** Tipos adecuados (p. ej. `decimal` para litros de aceite/combustible). Nombres y convenciones coherentes con el plan de datos.
- **Livewire 4:** Componentes Livewire 4 para toda la reactividad (tablas, formularios, filtros, dashboard).
- **Traits:** Validaciones de **RUT** y **Patente** en traits reutilizables (p. ej. en modelos y form requests).

---

### 13.6. Orden de Implementación Sugerido

1. **Estructura de base de datos (migraciones)** y **docker-compose.yml** (PostgreSQL en host, Mailpit).
2. **Service Layer** del Stock Crítico Dinámico (evitar lógica en controladores).
3. **Traits y Helpers** de validación (RUT, Patente).
4. Lógica de **Snooze** en alertas y **evidencia obligatoria** en mantenimientos.
5. **Middleware o servicio** de bloqueo de asignación vehículo–conductor.

---

## 14. CONCLUSIÓN Y PRÓXIMOS PASOS

Este plan maestro proporciona una base sólida para el desarrollo del sistema de gestión de mantenimiento de flotas. 

**Puntos clave a recordar:**
1. **Flexibilidad desde el inicio**: Soporte para diferentes tipos de vehículos y contadores
2. **Automatización inteligente**: Alertas y programación automática para reducir carga operativa
3. **Trazabilidad completa**: Auditoría y logs para cumplimiento y seguridad
4. **Registro de conductores**: Sistema de asignación con historial para detectar patrones de mal uso y responsabilidad operativa
5. **Arquitectura limpia**: Servicios, actions, eventos para código mantenible
6. **Desarrollo iterativo**: Fases incrementales con valor entregado en cada una

**Siguiente paso:** Revisar este plan con stakeholders, ajustar según feedback, y comenzar por el **orden de implementación** definido en **13.6** (dentro de la sección 13. Implementación integral).

---

**Documento:** Plan Maestro Único (visión, stack, modelo, alertas, fases, implementación integral)  
**Versiones de referencia:** Laravel 12.x | Livewire 4.x | PHP 8.2+ | PostgreSQL | Docker (Ubuntu 24.04)