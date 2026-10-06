<?php

namespace Database\Factories\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FreelancerAccountFactory extends Factory
{
    protected $model = FreelancerAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'username' => fake()->userName(),
            'display_name' => fake()->name(),
            'timezone' => 'Asia/Karachi',
            'is_connected' => true,
            'automation_enabled' => true,
            'dry_run' => true,
            'automation_mode' => 'automatic',
            'global_paused' => false,
            'freelancer_user_id' => fake()->numberBetween(1000, 9999),
            'access_token' => 'token',
            'refresh_token' => 'refresh-token',
            'daily_bid_limit' => 20,
            'hourly_bid_limit' => 5,
            'monthly_bid_limit' => 200,
            'min_skill_match_percent' => 60,
            'project_type_filter' => 'both',
            'country_mode' => 'all',
            'score_weights' => config('freelancer.score_weights'),
            'schedule' => [
                ['days' => [1, 2, 3, 4, 5, 6, 7], 'start' => '00:00', 'end' => '23:59', 'enabled' => true],
            ],
        ];
    }
}
