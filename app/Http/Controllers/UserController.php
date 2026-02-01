<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    private const ROLE_NAMES = [
        'administrator',
        'supervisor',
        'administrativo',
        'technician',
        'viewer',
    ];

    public function index()
    {
        if (request()->ajax()) {
            $users = User::query()->select('users.*');

            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $users->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            }

            $activeFilter = request()->input('columns.5.search.value');
            if ($activeFilter !== null && $activeFilter !== '') {
                $active = in_array(strtolower($activeFilter), ['1', 'true', 'activo', 'yes'], true);
                $users->where('active', $active);
            }

            return DataTables::of($users)
                ->addColumn('role_badge', function (User $user) {
                    $labels = [
                        'administrator' => 'Administrador',
                        'supervisor' => 'Supervisor',
                        'administrativo' => 'Administrativo',
                        'technician' => 'Técnico',
                        'viewer' => 'Visualizador',
                    ];
                    $role = $user->rol;
                    $label = $labels[$role] ?? ucfirst($role);
                    $colors = [
                        'administrator' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
                        'supervisor' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                        'administrativo' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300',
                        'technician' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300',
                        'viewer' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                    ];
                    $color = $colors[$role] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$label}</span>";
                })
                ->addColumn('active_badge', function (User $user) {
                    $color = $user->active ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                    $label = $user->active ? 'Activo' : 'Inactivo';
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$label}</span>";
                })
                ->addColumn('actions', function (User $user) {
                    $edit = "<a href='" . route('usuarios.edit', $user->id) . "' class='text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors' title='Editar'><i class='fas fa-edit'></i></a>";
                    $delete = $user->id === auth()->id()
                        ? "<span class='text-gray-400 cursor-not-allowed' title='No puedes eliminarte a ti mismo'><i class='fas fa-trash-alt'></i></span>"
                        : "<button onclick='deleteUser(" . $user->id . ")' class='text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 cursor-pointer bg-transparent border-none' title='Eliminar'><i class='fas fa-trash-alt'></i></button>";
                    return "<div class='flex justify-end space-x-3'>{$edit} {$delete}</div>";
                })
                ->rawColumns(['role_badge', 'active_badge', 'actions'])
                ->make(true);
        }

        return view('usuarios.index');
    }

    public function create()
    {
        $roles = collect(self::ROLE_NAMES)->mapWithKeys(fn (string $name) => [$name => $this->roleLabel($name)]);
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(self::ROLE_NAMES)],
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email_notifications' => ['boolean'],
            'active' => ['boolean'],
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = $validated['role'];
        $user->full_name = $validated['full_name'] ?? null;
        $user->phone = $validated['phone'] ?? null;
        $user->email_notifications = $request->boolean('email_notifications');
        $user->active = $request->boolean('active');
        $user->save();

        $user->syncRoles([$validated['role']]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $user = $usuario;
        $roles = collect(self::ROLE_NAMES)->mapWithKeys(fn (string $name) => [$name => $this->roleLabel($name)]);
        return view('usuarios.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(self::ROLE_NAMES)],
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email_notifications' => ['boolean'],
            'active' => ['boolean'],
        ]);

        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];
        if (! empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }
        $usuario->role = $validated['role'];
        $usuario->full_name = $validated['full_name'] ?? null;
        $usuario->phone = $validated['phone'] ?? null;
        $usuario->email_notifications = $request->boolean('email_notifications');
        $usuario->active = $request->boolean('active');
        $usuario->save();

        $usuario->syncRoles([$validated['role']]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu propio usuario.',
            ], 403);
        }

        $adminCount = User::role('administrator')->count();
        if ($usuario->hasRole('administrator') && $adminCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el último administrador.',
            ], 403);
        }

        try {
            $usuario->syncRoles([]);
            $usuario->delete();
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            'administrator' => 'Administrador',
            'supervisor' => 'Supervisor',
            'administrativo' => 'Administrativo',
            'technician' => 'Técnico',
            'viewer' => 'Visualizador',
            default => ucfirst($role),
        };
    }
}
