<?php

namespace App\Console\Commands\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Services\Freelancer\FreelancerProfileService;
use Illuminate\Console\Command;

class TestFreelancerConnectionCommand extends Command
{
    protected $signature = 'freelancer:test-connection {--account=}';

    protected $description = 'Test Freelancer API connection for connected accounts';

    public function handle(FreelancerProfileService $profiles): int
    {
        $query = FreelancerAccount::where('is_connected', true);
        if ($id = $this->option('account')) {
            $query->whereKey($id);
        }

        $accounts = $query->get();
        if ($accounts->isEmpty()) {
            $this->warn('No connected Freelancer accounts.');

            return self::FAILURE;
        }

        foreach ($accounts as $account) {
            try {
                $result = $profiles->testConnection($account);
                $this->info('Account #'.$account->id.' OK — user '.$($result['username'] ?? $result['freelancer_user_id'] ?? 'unknown'));
            } catch (\Throwable $e) {
                $this->error('Account #'.$account->id.' FAILED — '.$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
