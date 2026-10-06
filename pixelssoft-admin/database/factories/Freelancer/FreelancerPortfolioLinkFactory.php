<?php

namespace Database\Factories\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerPortfolioLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class FreelancerPortfolioLinkFactory extends Factory
{
    protected $model = FreelancerPortfolioLink::class;

    public function definition(): array
    {
        return [
            'freelancer_account_id' => FreelancerAccount::factory(),
            'title' => fake()->sentence(3),
            'url' => fake()->url(),
            'description' => fake()->paragraph(),
            'technologies' => ['Laravel', 'Vue'],
            'priority' => 100,
            'is_active' => true,
            'usage_count' => 0,
        ];
    }
}
