<?php

namespace App\Console\Commands\Freelancer;

use App\Jobs\Freelancer\SyncFreelancerBidStatusJob;
use App\Models\Freelancer\FreelancerAccount;
use Illuminate\Console\Command;

class SyncFreelancerBidsCommand extends Command
{
    protected $signature = 'freelancer:sync-bids {--account=}';

    protected $description = 'Queue Freelancer bid status synchronization';

    public function handle(): int
    {
        $query = FreelancerAccount::where('is_connected', true);
        if ($id = $this->option('account')) {
            $query->whereKey($id);
        }

        foreach ($query->get() as $account) {
            SyncFreelancerBidStatusJob::dispatch($account->id);
            $this->info('Queued bid status sync for account #'.$account->id);
        }

        return self::SUCCESS;
    }
}
