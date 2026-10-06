<?php

namespace App\Console\Commands\Freelancer;

use App\Jobs\Freelancer\SyncFreelancerProjectsJob;
use App\Models\Freelancer\FreelancerAccount;
use Illuminate\Console\Command;

class SyncFreelancerProjectsCommand extends Command
{
    protected $signature = 'freelancer:sync-projects {--account=}';

    protected $description = 'Queue Freelancer active project synchronization';

    public function handle(): int
    {
        $accounts = $this->accounts();
        foreach ($accounts as $account) {
            SyncFreelancerProjectsJob::dispatch($account->id);
            $this->info('Queued project sync for account #'.$account->id);
        }

        return self::SUCCESS;
    }

    protected function accounts()
    {
        if ($id = $this->option('account')) {
            return FreelancerAccount::whereKey($id)->where('is_connected', true)->get();
        }

        return FreelancerAccount::where('is_connected', true)->get();
    }
}
