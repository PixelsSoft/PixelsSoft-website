<?php

namespace App\Console\Commands\Freelancer;

use App\Jobs\Freelancer\SyncFreelancerProfileJob;
use App\Models\Freelancer\FreelancerAccount;
use Illuminate\Console\Command;

class SyncFreelancerProfileCommand extends Command
{
    protected $signature = 'freelancer:sync-profile {--account=}';

    protected $description = 'Queue Freelancer profile synchronization';

    public function handle(): int
    {
        $query = FreelancerAccount::where('is_connected', true);
        if ($id = $this->option('account')) {
            $query->whereKey($id);
        }

        foreach ($query->get() as $account) {
            SyncFreelancerProfileJob::dispatch($account->id);
            $this->info('Queued profile sync for account #'.$account->id);
        }

        return self::SUCCESS;
    }
}
