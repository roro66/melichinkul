# Roles y permisos – Melichinkul

El sistema usa **Spatie Laravel Permission**: roles y permisos se definen en base de datos. En el menú **Administrar** (colapsable) tienes: **Usuarios**, **Auditoría**, **Accesos** y **Roles y permisos**. Desde **Roles y permisos** puedes editar qué permisos tiene cada rol; desde **Usuarios** asignas el rol a cada usuario.

---

## 1. Asignar rol a un usuario (desde la app)

1. Entra como **Administrador** o **Supervisor**.
2. Menú **Administrar** → **Usuarios** → lista de usuarios.
3. **Crear usuario** o **Editar** un usuario existente.
4. En el campo **Rol** elige uno de:
   - **Administrador**
   - **Supervisor**
   - **Administrativo**
   - **Técnico**
   - **Visualizador**
5. Guarda. El sistema actualiza la columna `users.role` y sincroniza el rol en Spatie (`syncRoles`), por lo que el usuario tendrá todos los permisos de ese rol.

---

## 2. Resumen de roles (qué puede hacer cada uno)

| Rol             | Descripción breve |
|-----------------|--------------------|
| **Administrador** | Todo: ve y gestiona usuarios, auditoría, accesos, aprobar mantenimientos, eliminar, exportar, etc. |
| **Supervisor**  | Todo excepto **Gestión de usuarios** (no puede crear/editar/eliminar usuarios). |
| **Administrativo** | Operativo: vehículos, mantenimientos, conductores, repuestos, compras, certificaciones, reportes. No puede: aprobar mantenimientos, eliminar mantenimientos, ver auditoría/accesos ni gestionar usuarios. |
| **Técnico**     | Ver vehículos, mantenimientos, conductores, alertas, repuestos, certificaciones, reportes. Crear/editar mantenimientos y ver conductores. No eliminar, aprobar ni exportar. |
| **Visualizador** | Solo ver: vehículos, mantenimientos, conductores, alertas, repuestos, proveedores, compras, movimientos de inventario, certificaciones, reportes. Sin crear, editar ni eliminar. |

Los permisos concretos (por ejemplo `vehicles.view`, `maintenances.approve`) están definidos en `database/seeders/RolesAndPermissionsSeeder.php`.

---

## 3. Cambiar qué permisos tiene cada rol

No hay pantalla en la app para editar “qué permisos tiene el rol X”. Se hace de dos formas:

### A) Editar el seeder y volver a ejecutarlo (recomendado)

1. Abre `database/seeders/RolesAndPermissionsSeeder.php`.
2. Modifica:
   - **Permisos por módulo:** constante `PERMISSIONS` (ej. añadir un permiso nuevo).
   - **Nombres de roles:** constante `ROLE_NAMES` (si añades un rol nuevo, añade también la etiqueta en `UserController::roleLabel()` y en las vistas de usuarios).
   - **Asignación a roles:** método `assignPermissionsToRoles()` (qué permisos tiene cada rol).
3. Ejecuta el seeder y limpia la caché de permisos:

```bash
docker compose exec app php artisan db:seed --class=RolesAndPermissionsSeeder --force
docker compose exec app php artisan permission:cache-reset
```

El seeder **crea** los permisos/roles que falten y **reasigna** los permisos a cada rol según el código; además sincroniza a todos los usuarios con su rol actual (`users.role`).

### Cambios directos en base de datos (avanzado)

Puedes insertar/actualizar en las tablas `roles`, `permissions`, `role_has_permissions` y `model_has_roles`. Si lo haces, después ejecuta:

```bash
docker compose exec app php artisan permission:cache-reset
```

Para que los usuarios sigan teniendo un rol “visible” en la app, el valor de `users.role` debe coincidir con el nombre del rol en `roles` (ej. `administrator`, `supervisor`). Si añades un rol nuevo solo en BD, tendrías que añadirlo también en `UserController::ROLE_NAMES` y en el desplegable de Usuarios para poder asignarlo desde la interfaz.

---

## 4. Comandos útiles

| Acción | Comando |
|--------|--------|
| Aplicar definición de roles/permisos del seeder | `docker compose exec app php artisan db:seed --class=RolesAndPermissionsSeeder --force` |
| Limpiar caché de permisos (tras cambiar roles/permisos) | `docker compose exec app php artisan permission:cache-reset` |
| Sincronizar solo usuarios con sus roles (sin tocar permisos) | El seeder ya hace `syncUsersToRoles()`; no hay comando aparte. |

---

## 5. Dónde está cada cosa en el código

| Qué | Dónde |
|-----|--------|
| Lista de permisos por módulo | `RolesAndPermissionsSeeder::PERMISSIONS` |
| Nombres de roles | `RolesAndPermissionsSeeder::ROLE_NAMES`, `UserController::ROLE_NAMES` |
| Asignación permisos → roles | `RolesAndPermissionsSeeder::assignPermissionsToRoles()` |
| Etiquetas de rol en español (Usuarios) | `UserController::roleLabel()` |
| Rutas protegidas por permiso | `routes/web.php` (middleware `permission:nombre.accion`) |
| Menú según permiso | `resources/views/layouts/app.blade.php` (`@can('permission')`) |

Si en el futuro quieres una pantalla en la app para crear/editar roles y marcar permisos por rol, habría que añadir un módulo “Roles y permisos” (controlador, vistas, rutas) que use los modelos `Role` y `Permission` de Spatie.
