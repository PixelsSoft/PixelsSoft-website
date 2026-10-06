<?php

namespace App\Console\Commands\Freelancer;

use App\Jobs\Freelancer\SyncFreelancerPortfolioJob;
use App\Models\Freelancer\FreelancerAccount;
use Illuminate\Console\Command;

class SyncFreelancerPortfolioCommand extends Command
{
    protected $signature = 'freelancer:sync-portfolio {--account=}';

    protected $description = 'Queue Freelancer portfolio synchronization';

    public function handle(): int
    {
        $query = FreelancerAccount::where('is_connected', true);
        if ($id = $this->option('account')) {
            $query->whereKey($id);
        }

        foreach ($query->get() as $account) {
            SyncFreelancerPortfolioJob::dispatch($account->id);
            $this->info('Queued portfolio sync for account #'.$account->id);
        }

        return self::SUCCESS;
    }
}
