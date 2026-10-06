<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use Illuminate\Support\Facades\Auth;

class FreelancerAccountResolver
{
    public function __construct(protected FreelancerSettingsService $settings) {}

    public function current(?int $accountId = null): ?FreelancerAccount
    {
        $userId = Auth::id();

        $account = $accountId
            ? FreelancerAccount::with(['settings'])->find($accountId)
            : ($userId
                ? FreelancerAccount::query()
                    ->with(['settings'])
                    ->where('user_id', $userId)
                    ->orderByDesc('is_connected')
                    ->orderByDesc('id')
                    ->first()
                : null);

        if ($account && $userId && $account->user_id && (int) $account->user_id !== (int) $userId) {
            return null;
        }

        if ($account && !$account->settings) {
            $this->settings->forAccount($account);
            $account->refresh()->load('settings');
        }

        return $account;
    }

    public function firstOrCreateForUser(?int $userId = null): FreelancerAccount
    {
        $userId = $userId ?: Auth::id();

        $account = FreelancerAccount::firstOrCreate(
            ['user_id' => $userId],
            [
                'timezone' => config('freelancer.timezone', 'Asia/Karachi'),
                'dry_run' => (bool) config('freelancer.defaults.dry_run', true),
                'daily_bid_limit' => (int) config('freelancer.defaults.daily_bid_limit', 20),
                'hourly_bid_limit' => (int) config('freelancer.defaults.hourly_bid_limit', 5),
                'monthly_bid_limit' => (int) config('freelancer.defaults.monthly_bid_limit', 500),
                'min_skill_match_percent' => (int) config('freelancer.defaults.min_skill_match_percent', 60),
                'max_project_age_minutes' => (int) config('freelancer.defaults.max_project_age_minutes', 15),
                'bid_delay_seconds' => (int) config('freelancer.defaults.bid_delay_seconds', 60),
                'score_weights' => config('freelancer.score_weights'),
                'schedule' => [
                    ['days' => [1, 2, 3, 4, 5], 'start' => '09:00', 'end' => '18:00', 'enabled' => true],
                    ['days' => [6], 'start' => '10:00', 'end' => '15:00', 'enabled' => true],
                    ['days' => [7], 'start' => '00:00', 'end' => '00:00', 'enabled' => false],
                ],
            ]
        );

        $this->settings->forAccount($account);

        return $account->fresh(['settings']);
    }
}
