# Manual de Melichinkul

Melichinkul lleva la flota, los conductores, los mantenimientos, el kilometraje y el stock de repuestos. El menú izquierdo abre cada módulo. En el celular, el menú se abre con el botón de la esquina superior.

## Asistente

**Quién puede:** rol `administrator`

El asistente está en el menú, bajo el nombre Asistente. Sirve para dos cosas:

- Preguntar datos de la flota, los conductores, las alertas, los mantenimientos, el kilometraje, los repuestos o las compras. La respuesta sale de una consulta de solo lectura. No modifica la base.
- Preguntar cómo se usa una pantalla. La respuesta sale de este manual y solo describe las pantallas que el usuario puede abrir.

No guarda contraseñas ni lee sesiones. Si la pregunta es ambigua, conviene nombrar la patente, el conductor o el repuesto.

## Vehículos

**Quién puede:** permiso `vehicles.view`

En Vehículos se ve la flota: patente, marca, modelo, conductor actual y estado. Al abrir un móvil se ve su ficha, el historial, los documentos y las alertas de ese vehículo.

Para crear un móvil hace falta el permiso `vehicles.create`. Para editarlo, `vehicles.edit`. Para eliminarlo, `vehicles.delete`. La exportación está en `vehicles.export`.

El buscador de la barra superior encuentra un móvil por patente u otros datos visibles.

## Conductores y documentos

**Quién puede:** permiso `drivers.view`

En Conductores se ve el nombre, el RUT, el contacto y el estado de la licencia. La ficha muestra los vehículos asignados y los documentos cargados.

Los documentos se agregan en Editar conductor, con el permiso `drivers.edit`. Hay tres tipos:

- Carnet de identidad.
- Licencia de conducir vigente. Hay que indicar la nueva fecha de vencimiento.
- Otro documento, por ejemplo un certificado de antecedentes.

El carnet y la licencia aceptan una imagen o un PDF. Si el escaneo trae las dos caras juntas, basta con subir esa imagen. Si van separadas, se agrega el reverso. El reverso no es obligatorio. Cada cara se puede ver y descargar desde la ficha.

## Licencias y alertas

**Quién puede:** permiso `alerts.view`

La licencia se considera por vencer cuando faltan 60 días o menos. Si ya venció, la alerta es de licencia vencida. El aviso no se cierra a mano: se cierra al cargar la licencia vigente con un vencimiento a más de 60 días, o al actualizar esa fecha en la ficha del conductor. Si la fecha nueva sigue dentro de los 60 días, el aviso continúa.

El correo a los administradores y al conductor sale una vez al día, solo los días que corresponden:

- Entre 31 y 60 días: los lunes.
- Entre 16 y 30 días: lunes, miércoles y viernes.
- Con 15 días o menos, o ya vencida: todos los días.

En pantalla, el administrador que está conectado ve un aviso a las 09:35, a las 13:00 y a las 17:00, hora de Chile. Puede aplazarlo de 1 a 7 días. Al cumplirse el plazo, si la licencia sigue por vencer o vencida, el aviso vuelve.

En Alertas, la fila de una licencia lleva a la ficha del conductor para subir el documento. No tiene botón de cerrar.

## Mantenimientos

**Quién puede:** permiso `maintenances.view`

En Mantenimientos se programan y se revisan los trabajos de cada móvil. El calendario muestra las fechas. Tareas pendientes lista lo que falta por hacer, con el permiso `maintenances.pending_tasks`.

Un trabajo se crea con `maintenances.create` y se edita con `maintenances.edit`. El técnico registra el trabajo realizado, los repuestos usados y la evidencia con `maintenances.record_work`. La aprobación usa `maintenances.approve`.

Las plantillas y el checklist se administran desde Plantillas y Checklist. Crear o cambiar una plantilla o un ítem del checklist usa `maintenances.create`.

## Kilometraje

**Quién puede:** permiso `mileage.view`

En Kilometraje se registran las lecturas, se revisa el historial y se ven los gráficos. Una lectura nueva usa `mileage.create`. Corregir una lectura usa `mileage.edit`. La importación masiva usa `mileage.import`.

## Repuestos, compras e inventario

**Quién puede:** permiso `spare_parts.view`

En Repuestos está el catálogo y el stock. Crear un repuesto usa `spare_parts.create`. Editarlo, `spare_parts.edit`. Ajustar el stock a mano usa `spare_parts.adjust_stock`.

Los proveedores están en Proveedores (`suppliers.view` para verlos, `suppliers.create` para crearlos). Las compras están en Compras (`purchases.view` para verlas y `purchases.create` para ingresarlas). Los movimientos de inventario están en Movimientos (`inventory.view_movements`).

Una alerta de stock bajo aparece en Alertas cuando el saldo llega al mínimo configurado en el repuesto.

## Reportes

**Quién puede:** permiso `reports.view`

En Reportes se descargan el estado de la flota y el resumen ejecutivo en PDF.

## Usuarios, roles y auditoría

**Quién puede:** permiso `users.manage`

En el menú de administración se administran los usuarios y, con `roles.manage`, los roles. Los roles son administrador, supervisor, administrativo, técnico y visor. El administrador tiene todos los permisos.

La auditoría de cambios usa `audit.view`. El registro de accesos está en la misma sección.
