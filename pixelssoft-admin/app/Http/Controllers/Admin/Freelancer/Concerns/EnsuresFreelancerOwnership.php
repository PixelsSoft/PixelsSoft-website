<?php

namespace App\Http\Controllers\Admin\Freelancer\Concerns;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerBid;
use App\Models\Freelancer\FreelancerBidTemplate;
use App\Models\Freelancer\FreelancerPortfolio;
use App\Models\Freelancer\FreelancerPortfolioLink;
use App\Models\Freelancer\FreelancerProject;
use App\Models\Freelancer\FreelancerSkill;
use App\Models\Freelancer\FreelancerStrategy;
use App\Services\Freelancer\FreelancerAccountResolver;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait EnsuresFreelancerOwnership
{
    protected function ownedAccount(): FreelancerAccount
    {
        $account = app(FreelancerAccountResolver::class)->firstOrCreateForUser();

        return $account;
    }

    protected function ensureAccountAccessible(FreelancerAccount $account): FreelancerAccount
    {
        $owned = $this->ownedAccount();
        if ($account->id !== $owned->id) {
            throw new AccessDeniedHttpException('This Freelancer account is not accessible.');
        }

        return $owned;
    }

    protected function ensureProjectOwned(FreelancerProject $project): FreelancerProject
    {
        $this->assertSameAccount($project->freelancer_account_id);

        return $project;
    }

    protected function ensureBidOwned(FreelancerBid $bid): FreelancerBid
    {
        $this->assertSameAccount($bid->freelancer_account_id);

        return $bid;
    }

    protected function ensureSkillOwned(FreelancerSkill $skill): FreelancerSkill
    {
        $this->assertSameAccount($skill->freelancer_account_id);

        return $skill;
    }

    protected function ensurePortfolioOwned(FreelancerPortfolio $portfolio): FreelancerPortfolio
    {
        $this->assertSameAccount($portfolio->freelancer_account_id);

        return $portfolio;
    }

    protected function ensurePortfolioLinkOwned(FreelancerPortfolioLink $portfolioLink): FreelancerPortfolioLink
    {
        $this->assertSameAccount($portfolioLink->freelancer_account_id);

        return $portfolioLink;
    }

    protected function ensureStrategyOwned(FreelancerStrategy $strategy): FreelancerStrategy
    {
        $this->assertSameAccount($strategy->freelancer_account_id);

        return $strategy;
    }

    protected function ensureTemplateOwned(FreelancerBidTemplate $template): FreelancerBidTemplate
    {
        $this->assertSameAccount($template->freelancer_account_id);

        return $template;
    }

    protected function assertSameAccount(int $accountId): void
    {
        if ($this->ownedAccount()->id !== $accountId) {
            throw new AccessDeniedHttpException('This Freelancer resource does not belong to your account.');
        }
    }
}
