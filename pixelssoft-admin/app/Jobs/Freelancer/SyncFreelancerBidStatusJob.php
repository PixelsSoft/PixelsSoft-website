<?php

namespace App\Jobs\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerBid;
use App\Services\Freelancer\FreelancerApiService;
use App\Services\Freelancer\FreelancerAuthService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class SyncFreelancerBidStatusJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $accountId) {}

    public function handle(FreelancerApiService $api, FreelancerAuthService $auth): void
    {
        $account = FreelancerAccount::find($this->accountId);
        if (!$account || !$account->is_connected) {
            return;
        }

        $bids = FreelancerBid::where('freelancer_account_id', $account->id)
            ->where('status', 'submitted')
            ->whereNotNull('freelancer_bid_id')
            ->latest('submitted_at')
            ->limit(50)
            ->get();

        if ($bids->isEmpty()) {
            return;
        }

        $auth->ensureFreshToken($account);
        $api->forAccount($account);

        $response = $api->get('projects/0.1/bids/', [
            'limit' => 50,
            'offset' => 0,
        ], [], [
            'bids' => $bids->pluck('freelancer_bid_id')->map(fn ($id) => (int) $id)->values()->all(),
        ]);
        $remote = Arr::get($response, 'result.bids') ?? Arr::get($response, 'result') ?? [];
        if (!is_array($remote)) {
            return;
        }
        if (Arr::isAssoc($remote)) {
            $remote = array_values($remote);
        }

        foreach ($remote as $row) {
            $id = Arr::get($row, 'id');
            if (!$id) {
                continue;
            }
            $local = $bids->firstWhere('freelancer_bid_id', (int) $id);
            if (!$local) {
                continue;
            }

            $status = $this->mapRemoteStatus($row);
            if ($status) {
                $local->fill(['status' => $status, 'response_data' => $row])->save();
            }
        }
    }

    protected function mapRemoteStatus(array $row): ?string
    {
        if (Arr::get($row, 'awarded')) {
            return 'accepted';
        }

        if (Arr::get($row, 'retracted')) {
            return 'withdrawn';
        }

        $status = strtolower((string) (Arr::get($row, 'status') ?? Arr::get($row, 'bid_status') ?? ''));

        return match (true) {
            in_array($status, ['accepted', 'awarded', 'winner'], true) => 'accepted',
            in_array($status, ['retracted', 'withdrawn', 'cancelled', 'canceled'], true) => 'withdrawn',
            in_array($status, ['rejected', 'declined', 'lost', 'not_selected'], true) => 'rejected',
            in_array($status, ['closed', 'expired', 'deleted'], true) => 'closed',
            default => null,
        };
    }
}
