<?php

namespace App\Console\Commands\Freelancer;

use App\Models\Freelancer\FreelancerApiLog;
use Illuminate\Console\Command;

class CleanupFreelancerLogsCommand extends Command
{
    protected $signature = 'freelancer:cleanup-logs {--days=30}';

    protected $description = 'Delete old Freelancer API logs';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $deleted = FreelancerApiLog::where('created_at', '<', now()->subDays($days))->delete();
        $this->info("Deleted {$deleted} API log rows older than {$days} days.");

        return self::SUCCESS;
    }
}
