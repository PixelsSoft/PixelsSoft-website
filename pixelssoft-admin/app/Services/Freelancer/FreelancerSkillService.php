<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerSkill;
use Illuminate\Support\Arr;

class FreelancerSkillService
{
    public function __construct(
        protected FreelancerApiService $api,
        protected FreelancerAuthService $auth
    ) {}

    /**
     * Sync skill catalog entries referenced by jobs[] IDs via GET projects/0.1/jobs/
     * and refresh the account's selected skills from self profile when needed.
     */
    public function syncCatalog(FreelancerAccount $account, array $jobIds): array
    {
        if (empty($jobIds)) {
            return [];
        }

        $this->auth->ensureFreshToken($account);
        $this->api->forAccount($account);

        $response = $this->api->get('projects/0.1/jobs/', [
            'seo_details' => 'true',
            'lang' => 'en',
        ], [], [
            'jobs' => array_map('intval', array_values($jobIds)),
        ]);
        $jobs = Arr::get($response, 'result.jobs') ?? Arr::get($response, 'result') ?? [];
        if (Arr::isAssoc($jobs)) {
            $jobs = array_values($jobs);
        }

        $synced = [];
        foreach ($jobs as $job) {
            if (!is_array($job) || empty($job['id'])) {
                continue;
            }
            $skill = FreelancerSkill::updateOrCreate(
                [
                    'freelancer_account_id' => $account->id,
                    'freelancer_skill_id' => (int) $job['id'],
                ],
                [
                    'name' => $job['name'] ?? ('Skill #'.$job['id']),
                    'seo_url' => $job['seo_url'] ?? null,
                    'raw' => $job,
                ]
            );
            $synced[] = $skill;
        }

        return $synced;
    }

    public function searchLocal(FreelancerAccount $account, ?string $q = null)
    {
        return $account->skills()
            ->when($q, fn ($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate(50);
    }
}
