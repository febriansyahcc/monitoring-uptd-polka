<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $roleFilter = $request->input('role', '');

        $query = User::with('permissions');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        $users = $query->orderBy('role')->orderBy('name')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip ?: '-',
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'permissions' => $user->getPermissionSlugs(),
            ];
        });

        $masterPermissions = Permission::orderBy('category')->orderBy('name')->get()->groupBy('category');

        $roleCounts = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'manager' => User::where('role', 'manager')->count(),
            'tl_operasi' => User::where('role', 'tl_operasi')->count(),
            'tl_pemeliharaan' => User::where('role', 'tl_pemeliharaan')->count(),
            'operator' => User::where('role', 'operator')->count(),
        ];

        return Inertia::render('UserManagement/Index', [
            'users' => $users,
            'masterPermissions' => $masterPermissions,
            'roleCounts' => $roleCounts,
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nip' => 'nullable|string|unique:users,nip',
            'role' => 'required|string|in:admin,manager,tl_operasi,tl_pemeliharaan,operator',
            'password' => 'required|string|min:6',
            'permissions' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nip' => $validated['nip'] ?? null,
            'role' => $validated['role'],
            'is_active' => true,
            'password' => Hash::make($validated['password']),
        ]);

        // Sync permissions
        if (isset($validated['permissions']) && is_array($validated['permissions'])) {
            $allPerms = Permission::all()->keyBy('slug');
            foreach ($validated['permissions'] as $slug) {
                if (isset($allPerms[$slug])) {
                    UserPermission::create([
                        'user_id' => $user->id,
                        'permission_id' => $allPerms[$slug]->id,
                        'is_granted' => true,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', "Akun pengguna {$user->name} ({$user->role}) berhasil dibuat.");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'nip' => "nullable|string|unique:users,nip,{$id}",
            'role' => 'required|string|in:admin,manager,tl_operasi,tl_pemeliharaan,operator',
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
        ]);

        // Cegah admin menurunkan role akunnya sendiri (bisa mengunci diri dari User Management)
        if ($user->id === $request->user()->id && $validated['role'] !== $user->role) {
            return redirect()->back()->withErrors(['role' => 'Anda tidak dapat mengubah role akun Anda sendiri.']);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->nip = $validated['nip'] ?? null;
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Update PBAC permissions
        UserPermission::where('user_id', $user->id)->delete();

        if (isset($validated['permissions']) && is_array($validated['permissions'])) {
            $allPerms = Permission::all()->keyBy('slug');
            foreach ($validated['permissions'] as $slug) {
                if (isset($allPerms[$slug])) {
                    UserPermission::create([
                        'user_id' => $user->id,
                        'permission_id' => $allPerms[$slug]->id,
                        'is_granted' => true,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', "Pengaturan akun dan hak akses PBAC {$user->name} telah diperbarui.");
    }

    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Status akun {$user->name} berhasil {$statusText}.");
    }
}
