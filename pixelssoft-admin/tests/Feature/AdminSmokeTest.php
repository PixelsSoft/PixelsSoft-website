<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AccountsSeeder;
use Database\Seeders\CrmSeeder;
use Database\Seeders\HrSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesAndPermissionsSeeder::class,
            CrmSeeder::class,
            AccountsSeeder::class,
            HrSeeder::class,
        ]);
    }

    public function test_super_admin_can_load_key_admin_pages(): void
    {
        $user = User::where('email', 'admin@pixelssoft.com')->firstOrFail();

        $routes = [
            'admin.dashboard',
            'admin.crm.dashboard',
            'admin.crm.leads.index',
            'admin.crm.deals.kanban',
            'admin.crm.companies.index',
            'admin.crm.contacts.index',
            'admin.crm.reports.index',
            'admin.pm.dashboard',
            'admin.pm.projects.index',
            'admin.pm.time.index',
            'admin.pm.reports.index',
            'admin.accounts.dashboard',
            'admin.accounts.invoices.index',
            'admin.accounts.payments.index',
            'admin.accounts.expenses.index',
            'admin.accounts.reports.index',
            'admin.hr.dashboard',
            'admin.hr.employees.index',
            'admin.hr.leave.index',
            'admin.hr.attendance.index',
            'admin.hr.payroll.index',
            'admin.hr.documents.index',
            'admin.hr.departments.index',
            'admin.hr.reports.index',
            'admin.blogs.index',
            'admin.portfolios.index',
            'admin.showcases.index',
            'admin.services.index',
            'admin.sections.index',
            'admin.media.index',
            'admin.messages.index',
            'admin.system.users.index',
            'admin.system.roles.index',
            'admin.system.invitations.index',
            'admin.system.activity.index',
            'admin.settings.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get(route($route));
            $response->assertOk("Failed loading route: {$route}");
        }
    }
}
