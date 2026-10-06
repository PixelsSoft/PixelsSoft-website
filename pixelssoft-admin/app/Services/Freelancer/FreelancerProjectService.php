<?php

namespace App\Services\Freelancer;

use App\Jobs\Freelancer\ProcessFreelancerProjectJob;
use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerProject;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class FreelancerProjectService
{
    public function __construct(
        protected FreelancerApiService $api,
        protected FreelancerAuthService $auth,
        protected FreelancerCategoryService $categories,
        protected FreelancerAutomationLogService $logger,
    ) {}

    public function syncActiveProjects(FreelancerAccount $account, array $options = []): int
    {
        $this->auth->ensureFreshToken($account);
        $this->api->forAccount($account);

        $skillIds = $account->skills()
            ->where('automation_enabled', true)
            ->orderBy('priority')
            ->pluck('freelancer_skill_id')
            ->filter()
            ->take(20)
            ->values()
            ->all();

        $searchQuery = trim((string) ($options['query'] ?? ''));
        if ($searchQuery === '') {
            $searchQuery = $account->skills()
                ->where('automation_enabled', true)
                ->orderBy('priority')
                ->pluck('name')
                ->take(5)
                ->implode(' ');
        }

        $query = [
            'limit' => (int) ($options['limit'] ?? 50),
            'offset' => (int) ($options['offset'] ?? 0),
            'full_description' => 'true',
            'job_details' => 'true',
            'user_details' => 'true',
            'user_country_details' => 'true',
            'user_location_details' => 'true',
            'user_reputation' => 'true',
            'location_details' => 'true',
            'query' => $searchQuery !== '' ? $searchQuery : 'developer',
        ];

        $response = $this->api->get('projects/0.1/projects/active/', $query, [], [
            'jobs' => $skillIds,
        ]);
        $projects = Arr::get($response, 'result.projects') ?? Arr::get($response, 'result') ?? [];
        if (!is_array($projects)) {
            $projects = [];
        }
        if (Arr::isAssoc($projects)) {
            $projects = array_values($projects);
        }

        $count = 0;
        foreach ($projects as $raw) {
            if (!is_array($raw)) {
                continue;
            }
            $project = $this->storeDiscoveredProject($account, $raw);
            if (in_array($project->automation_status, ['new', 'rejected', 'qualified'], true) && $project->bid_status !== 'submitted') {
                ProcessFreelancerProjectJob::dispatch($project->id);
            }
            $count++;
        }

        $account->forceFill(['last_project_sync_at' => now()])->save();

        return $count;
    }

    public function storeDiscoveredProject(FreelancerAccount $account, array $raw): FreelancerProject
    {
        $projectId = (int) (Arr::get($raw, 'id') ?? 0);
        if (!$projectId) {
            throw new \InvalidArgumentException('Project payload missing id.');
        }

        $this->categories->syncFromProjectPayload($raw);

        $budget = Arr::get($raw, 'budget', []);
        $jobs = Arr::get($raw, 'jobs', []) ?: [];
        $owner = Arr::get($raw, 'owner') ?? Arr::get($raw, 'user') ?? [];
        $location = Arr::get($raw, 'location') ?? [];
        $ownerLocation = Arr::get($owner, 'location') ?? [];
        $type = strtolower((string) (Arr::get($raw, 'type') ?? 'fixed'));
        if (in_array($type, ['fixed', 'fixed_price'], true)) {
            $type = 'fixed';
        }

        $posted = Arr::get($raw, 'time_submitted') ?? Arr::get($raw, 'submitdate');
        $postedAt = $posted ? Carbon::createFromTimestamp((int) $posted) : null;
        $seo = Arr::get($raw, 'seo_url');
        $host = ($account->settings?->environment ?? (config('freelancer.sandbox') ? 'sandbox' : 'production')) === 'sandbox'
            ? 'https://www.freelancer-sandbox.com/projects/'
            : 'https://www.freelancer.com/projects/';

        $existing = FreelancerProject::where('freelancer_account_id', $account->id)
            ->where('freelancer_project_id', $projectId)
            ->first();

        $project = FreelancerProject::updateOrCreate(
            [
                'freelancer_account_id' => $account->id,
                'freelancer_project_id' => $projectId,
            ],
            [
                'title' => Arr::get($raw, 'title') ?: 'Untitled project',
                'description' => Arr::get($raw, 'description'),
                'project_url' => $seo ? $host.$seo : null,
                'project_type' => $type,
                'budget_min' => Arr::get($budget, 'minimum'),
                'budget_max' => Arr::get($budget, 'maximum'),
                'currency' => Arr::get($raw, 'currency.code') ?? Arr::get($budget, 'currency.code'),
                'hourly_rate' => Arr::get($raw, 'hourly_project_info.commitment.hourly_rate')
                    ?? Arr::get($budget, 'minimum'),
                'duration' => Arr::get($raw, 'duration'),
                'country' => Arr::get($ownerLocation, 'country.name')
                    ?? Arr::get($owner, 'country.name')
                    ?? Arr::get($location, 'country.name'),
                'country_code' => Arr::get($ownerLocation, 'country.code')
                    ?? Arr::get($owner, 'country.code')
                    ?? Arr::get($location, 'country.code'),
                'client_id' => Arr::get($raw, 'owner_id') ?? Arr::get($owner, 'id'),
                'client_username' => Arr::get($owner, 'username'),
                'client_rating' => Arr::get($owner, 'reputation.entire_history.overall') ?? Arr::get($owner, 'employer_reputation.entire_history.overall'),
                'client_reviews' => Arr::get($owner, 'reputation.entire_history.reviews') ?? Arr::get($owner, 'employer_reputation.entire_history.reviews'),
                'required_skills' => collect($jobs)->map(fn ($j) => ['id' => Arr::get($j, 'id'), 'name' => Arr::get($j, 'name')])->values()->all(),
                'category_ids' => collect($jobs)->pluck('category.id')->filter()->values()->all() ?: null,
                'category' => Arr::get($jobs, '0.category.name'),
                'status' => Arr::get($raw, 'status'),
                'bid_count' => Arr::get($raw, 'bid_stats.bid_count') ?? Arr::get($raw, 'bid_count'),
                'average_bid' => Arr::get($raw, 'bid_stats.bid_avg'),
                'expires_at' => ($expires = Arr::get($raw, 'time_free_bids_expire') ?? Arr::get($raw, 'expire_time'))
                    ? Carbon::createFromTimestamp((int) $expires)
                    : null,
                'posted_at' => $postedAt,
                'detected_at' => $existing?->detected_at ?: now(),
                'target_bid_seconds' => (int) ($account->settings?->max_bid_submission_seconds ?: 60),
                'automation_status' => $existing?->automation_status ?: 'new',
                'raw_response' => $raw,
            ]
        );

        if (!$existing) {
            $this->logger->log('project_discovered', 'Project discovered', $account, $project, null, ['freelancer_project_id' => $project->freelancer_project_id]);
        }

        return $project->fresh(['account.settings']);
    }

    public function revalidate(FreelancerAccount $account, FreelancerProject $project): FreelancerProject
    {
        $this->auth->ensureFreshToken($account);
        $this->api->forAccount($account);

        $response = $this->api->get('projects/0.1/projects/'.$project->freelancer_project_id.'/', [
            'full_description' => 'true',
            'job_details' => 'true',
            'user_details' => 'true',
            'user_country_details' => 'true',
            'user_location_details' => 'true',
            'user_reputation' => 'true',
            'location_details' => 'true',
        ], ['project_id' => $project->id]);

        $raw = Arr::get($response, 'result') ?? $response;

        return $this->storeDiscoveredProject($account, is_array($raw) ? $raw : []);
    }
}
