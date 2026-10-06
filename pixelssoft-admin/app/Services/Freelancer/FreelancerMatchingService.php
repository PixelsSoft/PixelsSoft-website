<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerPortfolio;
use App\Models\Freelancer\FreelancerProject;
use App\Models\Freelancer\FreelancerStrategy;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class FreelancerMatchingService
{
    public function __construct(protected FreelancerPortfolioLinkService $portfolioLinks) {}

    public function evaluate(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy = null): array
    {
        $weights = $account->score_weights ?: config('freelancer.score_weights');
        $explanation = [];
        $rejectReasons = [];

        $skillResult = $this->skillMatch($account, $project, $strategy);
        $keywordResult = $this->keywordMatch($account, $project);
        $budgetResult = $this->budgetMatch($account, $project, $strategy);
        $countryResult = $this->countryMatch($account, $project, $strategy);
        $clientResult = $this->clientMatch($account, $project, $strategy);
        $freshnessResult = $this->freshnessMatch($account, $project, $strategy);
        $bidCountResult = $this->bidCountMatch($account, $project, $strategy);
        $typeResult = $this->projectTypeMatch($account, $project);
        $categoryResult = $this->categoryMatch($account, $project, $strategy);

        foreach ([$skillResult, $keywordResult, $budgetResult, $countryResult, $clientResult, $freshnessResult, $bidCountResult, $typeResult, $categoryResult] as $part) {
            $explanation = array_merge($explanation, $part['explanation'] ?? []);
            if (!empty($part['reject'])) {
                $rejectReasons[] = $part['reject'];
            }
        }

        $minSkill = $strategy?->min_skill_match_percent ?? $account->min_skill_match_percent ?? (int) config('freelancer.defaults.min_skill_match_percent');
        if ($skillResult['percent'] < $minSkill) {
            $rejectReasons[] = sprintf('Skill match %d%%, minimum required %d%%.', (int) $skillResult['percent'], (int) $minSkill);
        }

        $score = 0.0;
        $score += ($skillResult['percent'] / 100) * (float) ($weights['skill'] ?? 40);
        $score += ($keywordResult['percent'] / 100) * (float) ($weights['keyword'] ?? 20);
        $score += ($budgetResult['percent'] / 100) * (float) ($weights['budget'] ?? 15);
        $score += ($countryResult['percent'] / 100) * (float) ($weights['country'] ?? 10);
        $score += ($clientResult['percent'] / 100) * (float) ($weights['client'] ?? 10);
        $score += ($freshnessResult['percent'] / 100) * (float) ($weights['freshness'] ?? 5);

        $qualified = empty($rejectReasons);
        $portfolioLinks = $this->portfolioLinks->rank($account, $project, $skillResult['matched_ids'] ?? []);
        $nativePortfolios = $this->rankNativePortfolios($account, $project, $skillResult['matched_ids'] ?? []);

        return [
            'qualified' => $qualified,
            'match_score' => round($score, 2),
            'skill_match_score' => round($skillResult['percent'], 2),
            'keyword_score' => round($keywordResult['percent'], 2),
            'country_match' => $countryResult['matched'],
            'reject_reason' => $qualified ? null : implode(' ', $rejectReasons),
            'explanation' => [
                'status' => $qualified ? 'QUALIFIED' : 'REJECTED',
                'parts' => $explanation,
                'weights' => $weights,
                'reject_reasons' => $rejectReasons,
            ],
            'matched_skill_names' => $skillResult['matched_names'] ?? [],
            'matched_skill_ids' => $skillResult['matched_ids'] ?? [],
            'portfolio_links' => $portfolioLinks,
            'portfolios' => $nativePortfolios,
        ];
    }

    public function pickStrategy(FreelancerAccount $account, FreelancerProject $project): ?FreelancerStrategy
    {
        $settings = $account->loadMissing('settings')->settings;
        $strategies = $account->strategies()->where('is_active', true)->orderBy('priority')->get();

        if ($settings?->default_strategy_id) {
            $default = $strategies->firstWhere('id', $settings->default_strategy_id);
            if ($default && $this->evaluate($account, $project, $default)['qualified']) {
                return $default;
            }
        }

        foreach ($strategies as $strategy) {
            if ($this->evaluate($account, $project, $strategy)['qualified']) {
                return $strategy;
            }
        }

        return null;
    }

    protected function skillMatch(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy): array
    {
        $projectSkills = collect($project->required_skills ?? []);
        $projectIds = $projectSkills->map(fn ($s) => (int) (is_array($s) ? ($s['id'] ?? 0) : 0))->filter()->values();
        $projectNames = $projectSkills->map(fn ($s) => strtolower((string) (is_array($s) ? ($s['name'] ?? '') : $s)))->filter()->values();

        $accountSkills = $account->skills()->where('automation_enabled', true)->get();
        if ($strategy?->skill_ids) {
            $accountSkills = $accountSkills->whereIn('freelancer_skill_id', $strategy->skill_ids);
        }

        if ($projectIds->isEmpty() && $projectNames->isEmpty()) {
            return [
                'percent' => 0,
                'matched_ids' => [],
                'matched_names' => [],
                'explanation' => [['label' => 'Skill match', 'detail' => 'No project skills listed', 'ok' => false]],
            ];
        }

        $matchedIds = [];
        $matchedNames = [];
        foreach ($accountSkills as $skill) {
            $idMatch = $projectIds->contains((int) $skill->freelancer_skill_id);
            $nameMatch = $projectNames->contains(strtolower($skill->name));
            if ($idMatch || $nameMatch) {
                $matchedIds[] = (int) $skill->freelancer_skill_id;
                $matchedNames[] = $skill->name;
            }
        }

        $denominator = max($projectIds->count() ?: $projectNames->count(), 1);
        $percent = (count(array_unique($matchedIds)) / $denominator) * 100;

        return [
            'percent' => $percent,
            'matched_ids' => array_values(array_unique($matchedIds)),
            'matched_names' => array_values(array_unique($matchedNames)),
            'explanation' => [[
                'label' => 'Skill match',
                'detail' => sprintf('%d%% (%s)', (int) $percent, $matchedNames ? implode(', ', array_unique($matchedNames)) : 'none'),
                'ok' => $percent > 0,
            ]],
        ];
    }

    protected function keywordMatch(FreelancerAccount $account, FreelancerProject $project): array
    {
        $haystack = strtolower(($project->title ?? '').' '.($project->description ?? ''));
        $negative = collect($account->negative_keywords ?? [])->filter()->map(fn ($k) => strtolower(trim($k)));
        foreach ($negative as $kw) {
            if ($kw !== '' && str_contains($haystack, $kw)) {
                return [
                    'percent' => 0,
                    'reject' => 'Excluded keyword matched: '.$kw.'.',
                    'explanation' => [['label' => 'Keywords', 'detail' => 'Negative keyword: '.$kw, 'ok' => false]],
                ];
            }
        }

        $positive = collect($account->positive_keywords ?? [])->filter()->map(fn ($k) => strtolower(trim($k)))->values();
        if ($positive->isEmpty()) {
            return ['percent' => 100, 'explanation' => [['label' => 'Keywords', 'detail' => 'No positive keywords configured', 'ok' => true]]];
        }

        $matched = $positive->filter(fn ($kw) => $kw !== '' && str_contains($haystack, $kw))->values();
        $minRequired = (int) ($account->min_positive_keywords ?? 0);
        $percent = ($matched->count() / max($positive->count(), 1)) * 100;

        return [
            'percent' => $percent,
            'reject' => $minRequired > 0 && $matched->count() < $minRequired
                ? sprintf('Only %d positive keywords matched; minimum required %d.', $matched->count(), $minRequired)
                : null,
            'explanation' => [[
                'label' => 'Keywords',
                'detail' => sprintf('%d matched', $matched->count()),
                'ok' => !($minRequired > 0 && $matched->count() < $minRequired),
            ]],
        ];
    }

    protected function budgetMatch(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy): array
    {
        $min = $strategy?->budget_min ?? $account->budget_min;
        $max = $strategy?->budget_max ?? $account->budget_max;
        $projectMin = $project->budget_min ?? $project->hourly_rate;
        $projectMax = $project->budget_max ?? $project->hourly_rate;

        if ($project->project_type === 'hourly') {
            $min = $account->hourly_rate_min ?? $min;
            $max = $account->hourly_rate_max ?? $max;
        }

        if ($min === null && $max === null) {
            return ['percent' => 100, 'explanation' => [['label' => 'Budget', 'detail' => 'No budget filter', 'ok' => true]]];
        }
        if ($projectMin === null && $projectMax === null) {
            return ['percent' => 50, 'explanation' => [['label' => 'Budget', 'detail' => 'Project budget unknown', 'ok' => true]]];
        }

        $value = $projectMax ?? $projectMin;
        if ($min !== null && $value < $min) {
            return ['percent' => 0, 'reject' => 'Budget below minimum.', 'explanation' => [['label' => 'Budget', 'detail' => 'Below minimum', 'ok' => false]]];
        }
        if ($max !== null && ($projectMin ?? $value) > $max) {
            return ['percent' => 0, 'reject' => 'Budget above maximum.', 'explanation' => [['label' => 'Budget', 'detail' => 'Above maximum', 'ok' => false]]];
        }

        return ['percent' => 100, 'explanation' => [['label' => 'Budget', 'detail' => 'Within configured range', 'ok' => true]]];
    }

    protected function countryMatch(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy): array
    {
        $mode = $strategy?->country_mode ?? $account->country_mode ?? 'all';
        $codes = collect($strategy?->country_codes ?? $account->country_codes ?? [])->map(fn ($c) => strtoupper((string) $c))->filter()->values();
        $projectCode = strtoupper((string) ($project->country_code ?: ''));
        $projectCountry = strtoupper((string) ($project->country ?: ''));

        if ($mode === 'all' || $codes->isEmpty()) {
            return ['percent' => 100, 'matched' => true, 'explanation' => [['label' => 'Country', 'detail' => $project->country ?: 'Any', 'ok' => true]]];
        }

        $inList = $codes->contains(fn ($c) => $c === $projectCode || $c === $projectCountry);
        if ($mode === 'include' && !$inList) {
            return ['percent' => 0, 'matched' => false, 'reject' => 'Country not allowed.', 'explanation' => [['label' => 'Country', 'detail' => ($project->country ?: 'Unknown').' not allowed', 'ok' => false]]];
        }
        if ($mode === 'exclude' && $inList) {
            return ['percent' => 0, 'matched' => false, 'reject' => 'Country excluded.', 'explanation' => [['label' => 'Country', 'detail' => ($project->country ?: 'Unknown').' excluded', 'ok' => false]]];
        }

        return ['percent' => 100, 'matched' => true, 'explanation' => [['label' => 'Country', 'detail' => $project->country ?: 'OK', 'ok' => true]]];
    }

    protected function clientMatch(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy): array
    {
        $minRating = $strategy?->min_client_rating ?? $account->min_client_rating;
        $minReviews = $account->min_client_reviews;

        if ($minRating !== null && $project->client_rating !== null && $project->client_rating < $minRating) {
            return ['percent' => 0, 'reject' => 'Client rating below minimum.', 'explanation' => [['label' => 'Client', 'detail' => 'Rating too low', 'ok' => false]]];
        }
        if ($minReviews !== null && $project->client_reviews !== null && $project->client_reviews < $minReviews) {
            return ['percent' => 0, 'reject' => 'Client reviews below minimum.', 'explanation' => [['label' => 'Client', 'detail' => 'Reviews too low', 'ok' => false]]];
        }

        $percent = $project->client_rating !== null ? min(100, max(0, ((float) $project->client_rating / 5) * 100)) : 70;
        return ['percent' => $percent, 'explanation' => [['label' => 'Client', 'detail' => sprintf('Rating %s, reviews %s', $project->client_rating ?? 'n/a', $project->client_reviews ?? 'n/a'), 'ok' => true]]];
    }

    protected function freshnessMatch(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy): array
    {
        $maxAge = $strategy?->max_project_age_minutes ?? $account->max_project_age_minutes;
        if (!$maxAge || !$project->posted_at) {
            return ['percent' => 100, 'explanation' => [['label' => 'Freshness', 'detail' => 'No age filter', 'ok' => true]]];
        }

        $reference = now($account->timezone ?: config('freelancer.timezone'));
        $ageMinutes = $project->posted_at->copy()->timezone($reference->timezone)->diffInMinutes($reference);
        if ($ageMinutes > $maxAge) {
            return ['percent' => 0, 'reject' => sprintf('Project age %d minutes exceeds maximum %d.', $ageMinutes, $maxAge), 'explanation' => [['label' => 'Freshness', 'detail' => $ageMinutes.' minutes old', 'ok' => false]]];
        }

        return ['percent' => max(0, 100 - (($ageMinutes / max($maxAge, 1)) * 100)), 'explanation' => [['label' => 'Freshness', 'detail' => $ageMinutes.' minutes ago', 'ok' => true]]];
    }

    protected function bidCountMatch(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy): array
    {
        $max = $strategy?->max_bid_count ?? $account->max_bid_count;
        if ($max === null || $project->bid_count === null) {
            return ['percent' => 100, 'explanation' => [['label' => 'Bid count', 'detail' => (string) ($project->bid_count ?? 'n/a'), 'ok' => true]]];
        }
        if ($project->bid_count > $max) {
            return ['percent' => 0, 'reject' => sprintf('Bid count %d exceeds maximum %d.', $project->bid_count, $max), 'explanation' => [['label' => 'Bid count', 'detail' => (string) $project->bid_count, 'ok' => false]]];
        }

        return ['percent' => 100, 'explanation' => [['label' => 'Bid count', 'detail' => (string) $project->bid_count, 'ok' => true]]];
    }

    protected function projectTypeMatch(FreelancerAccount $account, FreelancerProject $project): array
    {
        $filter = $account->project_type_filter ?: 'both';
        $type = strtolower((string) $project->project_type);
        if ($filter === 'both' || !$type) {
            return ['percent' => 100, 'explanation' => [['label' => 'Type', 'detail' => $type ?: 'any', 'ok' => true]]];
        }

        $ok = ($filter === 'fixed' && in_array($type, ['fixed', 'fixed_price'], true)) || ($filter === 'hourly' && $type === 'hourly');
        return ['percent' => $ok ? 100 : 0, 'reject' => $ok ? null : 'Project type not allowed.', 'explanation' => [['label' => 'Type', 'detail' => $type, 'ok' => $ok]]];
    }

    protected function categoryMatch(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy): array
    {
        $include = collect($account->include_category_ids ?? [])->map(fn ($id) => (int) $id);
        $exclude = collect($account->exclude_category_ids ?? [])->map(fn ($id) => (int) $id);
        $cats = collect($project->category_ids ?? [])->map(fn ($id) => (int) $id);

        if ($exclude->isNotEmpty() && $cats->intersect($exclude)->isNotEmpty()) {
            return ['percent' => 0, 'reject' => 'Category excluded.', 'explanation' => [['label' => 'Category', 'detail' => 'Excluded', 'ok' => false]]];
        }
        if ($include->isNotEmpty() && $cats->intersect($include)->isEmpty()) {
            return ['percent' => 0, 'reject' => 'Category not in include list.', 'explanation' => [['label' => 'Category', 'detail' => 'Not included', 'ok' => false]]];
        }

        return ['percent' => 100, 'explanation' => [['label' => 'Category', 'detail' => $project->category ?: 'OK', 'ok' => true]]];
    }

    protected function rankNativePortfolios(FreelancerAccount $account, FreelancerProject $project, array $matchedSkillIds = []): Collection
    {
        return $account->portfolios()->where('is_enabled', true)->where('use_for_bidding', true)->get()
            ->map(function (FreelancerPortfolio $item) use ($matchedSkillIds, $project) {
                $skillOverlap = collect($item->skill_ids ?? [])->intersect($matchedSkillIds)->count();
                $textScore = 0;
                foreach (preg_split('/\s+/', strtolower($item->title.' '.$item->description)) as $word) {
                    if (strlen($word) > 3 && str_contains(strtolower(($project->title ?? '').' '.($project->description ?? '')), $word)) {
                        $textScore++;
                    }
                }
                return ['portfolio' => $item, 'score' => ($skillOverlap * 30) + min(40, $textScore * 5)];
            })->sortByDesc('score')->values();
    }

    public function isWithinSchedule(FreelancerAccount $account, ?FreelancerStrategy $strategy = null, ?Carbon $at = null): bool
    {
        $at = ($at ?? now())->timezone($strategy?->timezone ?: $account->timezone ?: config('freelancer.timezone'));
        $schedule = $strategy?->schedule ?: $account->schedule;
        if (empty($schedule) || !is_array($schedule)) {
            return true;
        }

        $day = (int) $at->dayOfWeekIso;
        $time = $at->format('H:i');
        foreach ($schedule as $slot) {
            $days = Arr::get($slot, 'days', []);
            if (!Arr::get($slot, 'enabled', true)) {
                continue;
            }
            if ($days && !in_array($day, $days, true) && !in_array((string) $day, $days, true)) {
                continue;
            }
            if ($time >= Arr::get($slot, 'start', '00:00') && $time <= Arr::get($slot, 'end', '23:59')) {
                return true;
            }
        }

        return false;
    }
}
