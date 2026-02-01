<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Labels for permission modules (first part of permission name).
     */
    private const MODULE_LABELS = [
        'vehicles' => 'Vehículos',
        'maintenances' => 'Mantenimientos',
        'drivers' => 'Conductores',
        'alerts' => 'Alertas',
        'spare_parts' => 'Repuestos',
        'suppliers' => 'Proveedores',
        'purchases' => 'Compras',
        'inventory' => 'Inventario',
        'certifications' => 'Certificaciones',
        'users' => 'Usuarios',
        'roles' => 'Roles',
        'audit' => 'Auditoría',
        'reports' => 'Reportes',
    ];

    /**
     * Labels for permission actions (second part of permission name).
     */
    private const ACTION_LABELS = [
        'view' => 'Ver',
        'create' => 'Crear',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'export' => 'Exportar',
        'approve' => 'Aprobar',
        'close' => 'Cerrar',
        'snooze' => 'Posponer',
        'adjust_stock' => 'Ajustar stock',
        'receive' => 'Recibir',
        'view_movements' => 'Ver movimientos',
        'manage' => 'Gestionar',
    ];

    public function index()
    {
        $roles = Role::where('guard_name', 'web')
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        $userCounts = [];
        foreach ($roles as $role) {
            $userCounts[$role->id] = $role->users()->count();
        }

        return view('roles.index', compact('roles', 'userCounts'));
    }

    public function edit(Role $role)
    {
        if ($role->guard_name !== 'web') {
            abort(404);
        }

        $permissions = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        $grouped = $this->groupPermissionsByModule($permissions);
        $rolePermissionNames = $role->permissions->pluck('name')->all();

        return view('roles.edit', compact('role', 'grouped', 'rolePermissionNames'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->guard_name !== 'web') {
            abort(404);
        }

        $valid = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $names = $valid['permissions'] ?? [];
        $role->syncPermissions($names);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Permisos del rol "' . $role->name . '" actualizados correctamente.');
    }

    /**
     * @param \Illuminate\Support\Collection<int, Permission> $permissions
     * @return array<string, array{label: string, items: array<int, array{name: string, label: string}>}>
     */
    private function groupPermissionsByModule($permissions): array
    {
        $grouped = [];
        foreach ($permissions as $p) {
            $parts = explode('.', $p->name, 2);
            $module = $parts[0] ?? 'other';
            $action = $parts[1] ?? $p->name;
            $moduleLabel = self::MODULE_LABELS[$module] ?? ucfirst($module);
            $actionLabel = self::ACTION_LABELS[$action] ?? ucfirst(str_replace('_', ' ', $action));
            if (! isset($grouped[$module])) {
                $grouped[$module] = ['label' => $moduleLabel, 'items' => []];
            }
            $grouped[$module]['items'][] = [
                'name' => $p->name,
                'label' => $actionLabel,
            ];
        }
        return $grouped;
    }
}
