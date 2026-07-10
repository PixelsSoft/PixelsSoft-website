<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAdminController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users', 'permissions')->orderBy('name')->get();

        return view('admin.system.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.system.roles.create', [
            'grouped' => $this->groupedPermissions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ], [
            'name.regex' => 'Use lowercase letters, numbers, and hyphens only (e.g. account-manager).',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.system.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        $grouped = $this->groupedPermissions();
        $assigned = $role->permissions->pluck('name')->all();

        return view('admin.system.roles.form', compact('role', 'grouped', 'assigned'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Super Admin permissions cannot be modified.');
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->syncPermissions($request->input('permissions', []));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.system.roles.index')->with('success', 'Role permissions updated.');
    }

    private function groupedPermissions(): array
    {
        $groups = [];

        foreach (Permissions::all() as $permission) {
            [$module] = explode('.', $permission, 2);
            $groups[$module][] = $permission;
        }

        ksort($groups);

        return $groups;
    }
}
