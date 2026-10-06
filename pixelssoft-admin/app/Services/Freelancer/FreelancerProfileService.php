<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerPortfolio;
use App\Models\Freelancer\FreelancerSkill;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FreelancerProfileService
{
    public function __construct(
        protected FreelancerApiService $api,
        protected FreelancerAuthService $auth
    ) {}

    public function sync(FreelancerAccount $account): FreelancerAccount
    {
        $this->auth->ensureFreshToken($account);
        $this->api->forAccount($account);

        // Official Users API — self profile
        $response = $this->api->get('users/0.1/self/', [
            'avatar' => 'true',
            'country_details' => 'true',
            'profile_description' => 'true',
            'reputation' => 'true',
            'jobs' => 'true',
            'portfolio_details' => 'true',
            'preferred_details' => 'true',
        ]);

        $user = Arr::get($response, 'result') ?? $response;
        if (isset($user['user']) && is_array($user['user'])) {
            // Some responses nest under result.users or result
            $user = $user['user'];
        }
        if (isset($response['result']['users']) && is_array($response['result']['users'])) {
            $user = Arr::first($response['result']['users']) ?: $user;
        }

        $account->fill([
            'freelancer_user_id' => Arr::get($user, 'id') ?? Arr::get($user, 'user_id'),
            'username' => Arr::get($user, 'username'),
            'display_name' => Arr::get($user, 'display_name') ?: Arr::get($user, 'public_name') ?: Arr::get($user, 'username'),
            'email' => Arr::get($user, 'email'),
            'country' => Arr::get($user, 'location.country.name') ?: Arr::get($user, 'country.name') ?: Arr::get($user, 'country'),
            'city' => Arr::get($user, 'location.city') ?: Arr::get($user, 'city'),
            'profile_url' => $this->profileUrl(Arr::get($user, 'username')),
            'hourly_rate' => Arr::get($user, 'hourly_rate'),
            'rating' => Arr::get($user, 'reputation.entire_history.overall')
                ?? Arr::get($user, 'reputation.earnings_score')
                ?? Arr::get($user, 'rating'),
            'reviews_count' => (int) (Arr::get($user, 'reputation.entire_history.reviews') ?? Arr::get($user, 'reviews') ?? 0),
            'profile_description' => Arr::get($user, 'profile_description') ?: Arr::get($user, 'tagline'),
            'raw_profile' => $this->redactSensitive($response),
            'last_sync_at' => now(),
        ])->save();

        $this->syncSkillsFromProfile($account, $user);
        $this->syncPortfoliosFromProfile($account, $user);

        return $account->fresh(['skills', 'portfolios']);
    }

    public function testConnection(FreelancerAccount $account): array
    {
        $this->auth->ensureFreshToken($account);
        $this->api->forAccount($account);
        $response = $this->api->get('users/0.1/self/');

        return [
            'ok' => true,
            'freelancer_user_id' => Arr::get($response, 'result.id')
                ?? Arr::get($response, 'result.user.id')
                ?? Arr::get($response, 'result.users.0.id'),
            'username' => Arr::get($response, 'result.username')
                ?? Arr::get($response, 'result.user.username')
                ?? Arr::get($response, 'result.users.0.username'),
        ];
    }

    protected function syncSkillsFromProfile(FreelancerAccount $account, array $user): void
    {
        $jobs = Arr::get($user, 'jobs') ?? Arr::get($user, 'skills') ?? [];
        if (!is_array($jobs)) {
            return;
        }

        foreach ($jobs as $index => $job) {
            if (!is_array($job)) {
                continue;
            }
            $skillId = Arr::get($job, 'id') ?? Arr::get($job, 'job.id');
            $name = Arr::get($job, 'name') ?? Arr::get($job, 'job.name');
            if (!$skillId || !$name) {
                continue;
            }

            FreelancerSkill::updateOrCreate(
                [
                    'freelancer_account_id' => $account->id,
                    'freelancer_skill_id' => (int) $skillId,
                ],
                [
                    'name' => $name,
                    'seo_url' => Arr::get($job, 'seo_url') ?? Arr::get($job, 'job.seo_url'),
                    'is_primary' => $index < 3,
                    'automation_enabled' => true,
                    'priority' => $index + 1,
                    'raw' => $job,
                ]
            );
        }
    }

    protected function syncPortfoliosFromProfile(FreelancerAccount $account, array $user): void
    {
        $items = Arr::get($user, 'portfolio_items')
            ?? Arr::get($user, 'portfolios')
            ?? Arr::get($user, 'portfolio_details')
            ?? [];

        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
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
                    'is_enabled' => true,
                    'use_for_bidding' => true,
                    'status' => 'active',
                    'raw' => $item,
                ]
            );
        }
    }

    protected function profileUrl(?string $username): ?string
    {
        if (!$username) {
            return null;
        }

        $host = config('freelancer.sandbox')
            ? 'https://www.freelancer-sandbox.com/u/'
            : 'https://www.freelancer.com/u/';

        return $host.$username;
    }

    protected function redactSensitive(array $payload): array
    {
        $json = json_encode($payload);
        if ($json === false) {
            return [];
        }

        // Strip anything that looks like a token if present in nested payloads
        $json = preg_replace('/"(access_token|refresh_token|client_secret)"\s*:\s*"[^"]*"/i', '"$1":"[redacted]"', $json);

        return json_decode($json, true) ?: [];
    }
}
