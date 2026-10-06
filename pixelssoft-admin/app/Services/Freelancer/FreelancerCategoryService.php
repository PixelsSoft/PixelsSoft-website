<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerCategory;
use App\Models\Freelancer\FreelancerProject;
use Illuminate\Support\Arr;

class FreelancerCategoryService
{
    public function syncFromProjectPayload(array $raw): void
    {
        foreach ((array) Arr::get($raw, 'jobs', []) as $job) {
            if (!is_array($job)) {
                continue;
            }

            $categoryId = Arr::get($job, 'category.id');
            $name = Arr::get($job, 'category.name');
            if (!$name) {
                continue;
            }

            FreelancerCategory::updateOrCreate(
                ['freelancer_category_id' => $categoryId ?: null],
                [
                    'name' => $name,
                    'path' => Arr::get($job, 'category.seo_url'),
                    'raw' => Arr::get($job, 'category'),
                    'is_active' => true,
                ]
            );
        }
    }

    public function options()
    {
        return FreelancerCategory::query()->orderBy('name')->get();
    }
}
