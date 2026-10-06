<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerPortfolioLink;
use App\Models\Freelancer\FreelancerProject;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class FreelancerPortfolioLinkService
{
    public function rank(FreelancerAccount $account, FreelancerProject $project, array $matchedSkillIds = [], ?int $limit = null): Collection
    {
        $settings = $account->loadMissing('settings')->settings;
        $items = $account->portfolioLinks()->with(['skills', 'categories'])
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();

        $projectCategoryIds = collect($project->category_ids ?? [])->map(fn ($id) => (int) $id)->filter();
        $text = strtolower(($project->title ?? '').' '.($project->description ?? ''));

        $ranked = $items->map(function (FreelancerPortfolioLink $item) use ($matchedSkillIds, $projectCategoryIds, $text) {
            $skillIds = $item->skills->pluck('freelancer_skill_id')->map(fn ($id) => (int) $id)->all();
            $skillMatches = array_values(array_intersect($matchedSkillIds, $skillIds));
            $categoryMatches = $item->categories->pluck('freelancer_category_id')->filter()->map(fn ($id) => (int) $id)->intersect($projectCategoryIds)->values()->all();

            $reason = [];
            foreach ($item->skills as $skill) {
                if (in_array((int) $skill->freelancer_skill_id, $skillMatches, true)) {
                    $reason[] = $skill->name;
                }
            }

            $textScore = 0;
            foreach (preg_split('/\s+/', strtolower($item->title.' '.$item->description.' '.implode(' ', $item->technologies ?? []))) as $word) {
                if (strlen($word) > 3 && str_contains($text, $word)) {
                    $textScore++;
                }
            }

            $projectSkillCount = max(count(array_unique($matchedSkillIds)), count($project->required_skills ?? []), 1);
            $skillScore = (count($skillMatches) / $projectSkillCount) * 100;
            $score = round($skillScore + min(20, count($categoryMatches) * 10) + min(20, $textScore * 4), 2);

            return [
                'portfolio' => $item,
                'score' => min(100, $score),
                'matched_skill_names' => $reason,
                'matched_category_ids' => $categoryMatches,
                'explanation' => $reason ? 'Selected because: '.implode(', ', $reason) : 'Selected by keyword/category relevance',
            ];
        })->sortByDesc('score')->values();

        return $ranked->take($limit ?: (int) ($settings?->max_portfolio_links_per_bid ?: 3));
    }

    public function validateProposalUrls(FreelancerAccount $account, string $proposal, array $selectedLinkIds = []): void
    {
        preg_match_all('/https?:\/\/[^\s\)\]]+/i', $proposal, $matches);
        $urls = collect($matches[0] ?? [])->unique()->values();
        if ($urls->isEmpty()) {
            return;
        }

        $allowed = $account->portfolioLinks()
            ->where('is_active', true)
            ->when($selectedLinkIds, fn ($q) => $q->whereIn('id', $selectedLinkIds))
            ->pluck('url')
            ->filter()
            ->map(fn ($url) => rtrim(Str::lower($url), '/'))
            ->values();

        foreach ($urls as $url) {
            $normalized = rtrim(Str::lower($url), '/');
            if (!$allowed->contains($normalized)) {
                throw new RuntimeException('Proposal contains a portfolio URL that is not configured in CRM: '.$url);
            }
        }
    }

    public function incrementUsage(array $portfolioLinkIds): void
    {
        if (empty($portfolioLinkIds)) {
            return;
        }

        FreelancerPortfolioLink::whereIn('id', $portfolioLinkIds)->get()->each(function (FreelancerPortfolioLink $link) {
            $link->increment('usage_count');
            $link->forceFill(['last_used_at' => now()])->save();
        });
    }
}
