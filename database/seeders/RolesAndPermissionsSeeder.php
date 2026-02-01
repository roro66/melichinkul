<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissions by module (backend English).
     */
    private const PERMISSIONS = [
        'vehicles' => ['view', 'create', 'edit', 'delete', 'export'],
        'maintenances' => ['view', 'create', 'edit', 'delete', 'approve', 'export'],
        'drivers' => ['view', 'create', 'edit', 'delete'],
        'alerts' => ['view', 'close', 'snooze'],
        'spare_parts' => ['view', 'create', 'edit', 'delete', 'adjust_stock'],
        'suppliers' => ['view', 'create', 'edit', 'delete'],
        'purchases' => ['view', 'create', 'edit', 'delete', 'receive', 'export'],
        'inventory' => ['view_movements'],
        'certifications' => ['view', 'create', 'edit', 'delete'],
        'users' => ['manage'],
        'roles' => ['manage'],
        'audit' => ['view'],
        'reports' => ['view'],
    ];

    /**
     * Role names (must match users.role column for sync).
     */
    private const ROLE_NAMES = [
        'administrator',
        'supervisor',
        'administrativo',
        'technician',
        'viewer',
    ];

    public function run(): void
    {
        $this->createPermissions();
        $roles = $this->createRoles();
        $this->assignPermissionsToRoles($roles);
        $this->syncUsersToRoles();
    }

    private function createPermissions(): void
    {
        foreach (self::PERMISSIONS as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }
    }

    /** @return array<string, Role> */
    private function createRoles(): array
    {
        $roles = [];
        foreach (self::ROLE_NAMES as $name) {
            $roles[$name] = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        return $roles;
    }

    /** @param array<string, Role> $roles */
    private function assignPermissionsToRoles(array $roles): void
    {
        $all = Permission::pluck('name')->all();

        // Administrator: all
        $roles['administrator']->syncPermissions($all);

        // Supervisor: all except users.manage (includes audit.view, roles.manage)
        $roles['supervisor']->syncPermissions(
            array_filter($all, fn (string $p) => $p !== 'users.manage')
        );

        // Administrativo: operational, no approve/maintenances.delete, no users.manage
        $adminPerms = array_filter($all, function (string $p) {
            if ($p === 'users.manage' || $p === 'audit.view') {
                return false;
            }
            if ($p === 'maintenances.approve' || $p === 'maintenances.delete') {
                return false;
            }
            return true;
        });
        $roles['administrativo']->syncPermissions($adminPerms);

        // Technician: view + maintenances/drivers create+edit, no delete/approve/export
        $techPerms = [
            'vehicles.view',
            'maintenances.view', 'maintenances.create', 'maintenances.edit',
            'drivers.view',
            'alerts.view',
            'spare_parts.view',
            'certifications.view',
            'reports.view',
        ];
        $roles['technician']->syncPermissions($techPerms);

        // Viewer: view only
        $viewPerms = [
            'vehicles.view',
            'maintenances.view',
            'drivers.view',
            'alerts.view',
            'spare_parts.view',
            'suppliers.view',
            'purchases.view',
            'inventory.view_movements',
            'certifications.view',
            'reports.view',
        ];
        $roles['viewer']->syncPermissions($viewPerms);
    }

    private function syncUsersToRoles(): void
    {
        foreach (User::all() as $user) {
            $legacyRole = $user->getRawOriginal('role') ?? $user->getAttributeFromArray('role');
            if ($legacyRole && in_array($legacyRole, self::ROLE_NAMES, true)) {
                $user->syncRoles([$legacyRole]);
            }
        }
    }
}
