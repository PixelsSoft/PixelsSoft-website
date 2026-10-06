<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'freelancer.dashboard.view',
            'freelancer.projects.view',
            'freelancer.projects.manage',
            'freelancer.bids.view',
            'freelancer.bids.manage',
            'freelancer.automation.manage',
            'freelancer.strategies.manage',
            'freelancer.templates.manage',
            'freelancer.skills.manage',
            'freelancer.portfolio.manage',
            'freelancer.analytics.view',
            'freelancer.account.manage',
            'freelancer.logs.view',
            'freelancer.settings.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        Role::where('name', 'super-admin')->first()?->givePermissionTo($permissions);
        Role::where('name', 'sales-manager')->first()?->givePermissionTo($permissions);
    }

    public function down(): void
    {
        $permissions = [
            'freelancer.dashboard.view',
            'freelancer.projects.view',
            'freelancer.projects.manage',
            'freelancer.bids.view',
            'freelancer.bids.manage',
            'freelancer.automation.manage',
            'freelancer.strategies.manage',
            'freelancer.templates.manage',
            'freelancer.skills.manage',
            'freelancer.portfolio.manage',
            'freelancer.analytics.view',
            'freelancer.account.manage',
            'freelancer.logs.view',
            'freelancer.settings.manage',
        ];

        Permission::whereIn('name', $permissions)->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
