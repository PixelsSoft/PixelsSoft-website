<?php

namespace App\Console\Commands\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateFreelancerAnalyticsCommand extends Command
{
    protected $signature = 'freelancer:update-analytics';

    protected $description = 'Refresh Freelancer analytics cache keys';

    public function handle(): int
    {
        Cache::forget('freelancer.analytics.dashboard');
        Cache::forget('freelancer.analytics.dashboard.0');

        FreelancerAccount::query()->pluck('id')->each(function (int $accountId) {
            Cache::forget('freelancer.analytics.dashboard.'.$accountId);
        });

        $this->info('Freelancer analytics cache cleared.');

        return self::SUCCESS;
    }
}
