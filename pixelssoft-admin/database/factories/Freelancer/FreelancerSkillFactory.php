<?php

namespace Database\Factories\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerSkill;
use Illuminate\Database\Eloquent\Factories\Factory;

class FreelancerSkillFactory extends Factory
{
    protected $model = FreelancerSkill::class;

    public function definition(): array
    {
        return [
            'freelancer_account_id' => FreelancerAccount::factory(),
            'freelancer_skill_id' => fake()->unique()->numberBetween(1, 5000),
            'name' => fake()->unique()->word(),
            'automation_enabled' => true,
            'priority' => 1,
        ];
    }
}
