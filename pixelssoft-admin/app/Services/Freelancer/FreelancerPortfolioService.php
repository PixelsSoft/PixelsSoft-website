<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerPortfolio;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FreelancerPortfolioService
{
    public function __construct(
        protected FreelancerApiService $api,
        protected FreelancerAuthService $auth
    ) {}

    public function sync(FreelancerAccount $account): int
    {
        $this->auth->ensureFreshToken($account);
        $this->api->forAccount($account);

        if (!$account->freelancer_user_id) {
            throw new \RuntimeException('Sync profile first so freelancer_user_id is available.');
        }

        // Official: GET /api/users/0.1/portfolios/?users[]={id}
        $response = $this->api->get('users/0.1/portfolios/', [
            'limit' => 50,
            'offset' => 0,
        ], [], [
            'users' => [(int) $account->freelancer_user_id],
        ]);

        $portfolios = Arr::get($response, 'result.portfolios')
            ?? Arr::get($response, 'result')
            ?? [];

        // Result may be keyed by user id
        if (isset($portfolios[(string) $account->freelancer_user_id])) {
            $portfolios = $portfolios[(string) $account->freelancer_user_id];
        }
        if (Arr::isAssoc($portfolios) && isset($portfolios['articles'])) {
            $portfolios = $portfolios['articles'] ?? $portfolios;
        }
        if (!is_array($portfolios)) {
            $portfolios = [];
        }
        if (Arr::isAssoc($portfolios)) {
            $portfolios = array_values($portfolios);
        }

        $count = 0;
        foreach ($portfolios as $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = Arr::get($item, 'id') ?? Arr::get($item, 'portfolio_item_id');
            $title = Arr::get($item, 'title') ?? Arr::get($item, 'description');
            if (!$title) {
                continue;
            }

            FreelancerPortfolio::updateOrCreate(
                [
                    'freelancer_account_id' => $account->id,
                    'freelancer_portfolio_id' => $id ? (int) $id : null,
                ],
                [
                    'title' => Str::limit($title, 250, ''),
                    'description' => Arr::get($item, 'description'),
                    'url' => Arr::get($item, 'url') ?? Arr::get($item, 'link'),
                    'image_url' => Arr::get($item, 'thumbnail_url') ?? Arr::get($item, 'files.0.url'),
                    'category' => Arr::get($item, 'category') ?? Arr::get($item, 'seo_url'),
                    'skill_ids' => collect(Arr::get($item, 'jobs') ?? [])->pluck('id')->filter()->values()->all(),
                    'status' => 'active',
                    'raw' => $item,
                ]
            );
            $count++;
        }

        return $count;
    }
}
