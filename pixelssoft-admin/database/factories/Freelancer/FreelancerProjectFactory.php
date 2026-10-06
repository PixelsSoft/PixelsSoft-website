<?php

namespace Database\Factories\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerProject;
use Illuminate\Database\Eloquent\Factories\Factory;

class FreelancerProjectFactory extends Factory
{
    protected $model = FreelancerProject::class;

    public function definition(): array
    {
        return [
            'freelancer_account_id' => FreelancerAccount::factory(),
            'freelancer_project_id' => fake()->unique()->numberBetween(10000, 99999),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'currency' => 'USD',
            'project_type' => 'fixed',
            'budget_min' => 100,
            'budget_max' => 300,
            'required_skills' => [],
            'status' => 'active',
            'matched' => true,
            'automation_status' => 'qualified',
            'bid_status' => 'none',
            'posted_at' => now(),
            'detected_at' => now(),
            'target_bid_seconds' => 60,
        ];
    }
}
