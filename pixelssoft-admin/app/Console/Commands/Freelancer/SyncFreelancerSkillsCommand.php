<?php

namespace App\Console\Commands\Freelancer;

use App\Jobs\Freelancer\SyncFreelancerSkillsJob;
use App\Models\Freelancer\FreelancerAccount;
use Illuminate\Console\Command;

class SyncFreelancerSkillsCommand extends Command
{
    protected $signature = 'freelancer:sync-skills {--account=}';

    protected $description = 'Queue Freelancer skills catalog refresh';

    public function handle(): int
    {
        $query = FreelancerAccount::where('is_connected', true);
        if ($id = $this->option('account')) {
            $query->whereKey($id);
        }

        foreach ($query->get() as $account) {
            SyncFreelancerSkillsJob::dispatch($account->id);
            $this->info('Queued skills sync for account #'.$account->id);
        }

        return self::SUCCESS;
    }
}
