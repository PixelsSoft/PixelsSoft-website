<?php

namespace Tests\Feature\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerProject;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreelancerOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_cannot_mutate_another_accounts_project(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $intruder->givePermissionTo('freelancer.projects.manage');

        $ownedAccount = FreelancerAccount::factory()->create(['user_id' => $owner->id]);
        FreelancerAccount::factory()->create(['user_id' => $intruder->id]);

        $foreignProject = FreelancerProject::factory()->create([
            'freelancer_account_id' => $ownedAccount->id,
            'automation_status' => 'new',
        ]);

        $this->actingAs($intruder)
            ->post(route('admin.freelancer.projects.reject', $foreignProject))
            ->assertForbidden();

        $this->assertSame('new', $foreignProject->fresh()->automation_status);
    }
}
