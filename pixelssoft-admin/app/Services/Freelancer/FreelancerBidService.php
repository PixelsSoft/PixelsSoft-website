<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerAuditLog;
use App\Models\Freelancer\FreelancerBid;
use App\Models\Freelancer\FreelancerBidTemplate;
use App\Models\Freelancer\FreelancerProject;
use App\Models\Freelancer\FreelancerStrategy;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FreelancerBidService
{
    public function __construct(
        protected FreelancerApiService $api,
        protected FreelancerAuthService $auth,
        protected FreelancerMatchingService $matching,
        protected FreelancerProjectService $projects,
        protected FreelancerPortfolioLinkService $portfolioLinks,
        protected FreelancerSettingsService $settingsService,
        protected FreelancerAutomationLogService $logger,
    ) {}

    public function prepare(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy = null, ?string $proposalOverride = null): array
    {
        $account->loadMissing(['settings', 'portfolioLinks.skills', 'bidTemplates']);
        $settings = $this->settingsService->forAccount($account);
        $strategy = $strategy ?: ($project->strategy ?: $this->matching->pickStrategy($account, $project));
        if (!$strategy && $settings->default_strategy_id) {
            $strategy = $account->strategies()->find($settings->default_strategy_id);
        }

        $result = $this->matching->evaluate($account, $project, $strategy);
        $amount = $this->calculateAmount($project, $strategy);
        $delivery = $this->calculateDelivery($project, $strategy);
        $portfolioLinkIds = collect($result['portfolio_links'])->pluck('portfolio.id')->all();
        $proposal = $proposalOverride ?: $this->renderProposal($account, $project, $strategy, $result, $delivery, $portfolioLinkIds);

        $project->fill([
            'matched_strategy_id' => $strategy?->id,
            'match_score' => $result['match_score'],
            'skill_match_score' => $result['skill_match_score'],
            'keyword_score' => $result['keyword_score'],
            'country_match' => $result['country_match'],
            'match_explanation' => $result['explanation'],
            'automation_status' => $result['qualified'] ? 'qualified' : 'rejected',
            'reject_reason' => $result['reject_reason'],
            'matching_completed_at' => now(),
            'portfolio_selected_at' => now(),
            'suggested' => [
                'amount' => $amount,
                'delivery_days' => $delivery,
                'proposal' => $proposal,
                'portfolio_ids' => $portfolioLinkIds,
                'native_portfolio_ids' => collect($result['portfolios'])->take(3)->pluck('portfolio.id')->all(),
                'portfolio_explanations' => collect($result['portfolio_links'])->map(fn ($row) => [
                    'id' => $row['portfolio']->id,
                    'title' => $row['portfolio']->title,
                    'score' => $row['score'],
                    'explanation' => $row['explanation'],
                ])->values()->all(),
            ],
        ])->save();

        return compact('amount', 'delivery', 'proposal', 'strategy', 'result', 'portfolioLinkIds');
    }

    public function submit(
        FreelancerAccount $account,
        FreelancerProject $project,
        ?int $userId = null,
        bool $manual = false,
        ?float $amount = null,
        ?int $deliveryDays = null,
        ?string $proposal = null
    ): FreelancerBid {
        return DB::transaction(function () use ($account, $project, $userId, $manual, $amount, $deliveryDays, $proposal) {
            $account->loadMissing('settings');
            $settings = $this->settingsService->forAccount($account);
            $project = FreelancerProject::whereKey($project->id)->lockForUpdate()->firstOrFail();
            $project->forceFill(['bid_started_at' => now()])->save();

            $existing = FreelancerBid::where('freelancer_account_id', $account->id)
                ->where('freelancer_project_id', $project->id)
                ->lockForUpdate()
                ->first();

            if ($existing && in_array($existing->status, ['submitted', 'accepted', 'queued', 'processing', 'pending'], true) && !$existing->is_dry_run) {
                throw new RuntimeException('A bid already exists for this project (duplicate protection).');
            }

            $this->assertCanSubmit($account, $project, $manual);

            $simulation = $this->simulate($account, $project, $manual);
            if (!$simulation['ready']) {
                throw new RuntimeException($simulation['final_reason']);
            }

            $prepared = $this->prepare($account, $project, $project->strategy, $proposal);
            $finalAmount = $amount ?? $project->suggested_amount ?? $prepared['amount'];
            $finalDelivery = $deliveryDays ?? $project->suggested_delivery_days ?? $prepared['delivery'];
            $finalProposal = $proposal ?? $project->suggested_proposal ?? $prepared['proposal'];
            $selectedLinkIds = $project->selected_portfolio_ids ?: $prepared['portfolioLinkIds'];

            if ($finalAmount === null || $finalAmount <= 0) {
                throw new RuntimeException('Bid amount could not be calculated.');
            }
            if (!$finalProposal) {
                throw new RuntimeException('Proposal text is required.');
            }
            $this->portfolioLinks->validateProposalUrls($account, $finalProposal, $selectedLinkIds);

            $payload = [
                'freelancer_account_id' => $account->id,
                'freelancer_project_id' => $project->id,
                'strategy_id' => $prepared['strategy']?->id,
                'amount' => $finalAmount,
                'currency' => $project->currency,
                'delivery_days' => $finalDelivery,
                'proposal' => $finalProposal,
                'match_score' => $prepared['result']['match_score'],
                'skill_match_score' => $prepared['result']['skill_match_score'],
                'keyword_score' => $prepared['result']['keyword_score'],
                'country_match' => $prepared['result']['country_match'],
                'portfolio_ids' => $selectedLinkIds,
                'status' => 'processing',
                'is_dry_run' => (bool) $account->dry_run,
                'queued_at' => $project->bid_queued_at,
                'processing_started_at' => now(),
                'ai_processing_time_ms' => $project->ai_started_at && $project->ai_completed_at ? $project->ai_started_at->diffInMilliseconds($project->ai_completed_at) : null,
                'proposal_mode' => $settings->proposal_mode,
                'ai_provider' => $settings->ai_provider,
                'ai_model' => $settings->ai_model,
                'error_message' => null,
                'submitted_by' => $userId,
            ];

            $bid = $existing ?: new FreelancerBid(['freelancer_account_id' => $account->id, 'freelancer_project_id' => $project->id]);
            $bid->fill($payload)->save();
            $bid = $bid->fresh();

            if ($account->dry_run) {
                $bid->fill([
                    'status' => 'cancelled',
                    'error_message' => 'DRY RUN — BID NOT SUBMITTED',
                    'is_dry_run' => true,
                    'total_time_to_bid_ms' => $project->detected_at ? $project->detected_at->diffInMilliseconds(now()) : null,
                ])->save();

                $project->fill([
                    'automation_status' => 'qualified',
                    'bid_status' => 'dry_run',
                    'total_time_to_bid_ms' => $bid->total_time_to_bid_ms,
                    'total_processing_time_ms' => $project->matching_started_at ? $project->matching_started_at->diffInMilliseconds(now()) : null,
                ])->save();

                $this->logger->log('bid_dry_run', 'Dry run completed without submission', $account, $project, $bid);
                return $bid;
            }

            $this->auth->ensureFreshToken($account);
            $this->api->forAccount($account);
            $fresh = $this->projects->revalidate($account, $project);
            if ($fresh->hasSubmittedBid()) {
                throw new RuntimeException('Project already has a submitted bid.');
            }

            $remoteDuplicate = $this->remoteDuplicateBidExists($account, $project);
            if ($remoteDuplicate) {
                throw new RuntimeException('Freelancer API indicates a bid may already exist for this project.');
            }

            $apiStart = microtime(true);
            $response = $this->api->post('projects/0.1/bids/', [
                'project_id' => (int) $project->freelancer_project_id,
                'bidder_id' => (int) $account->freelancer_user_id,
                'description' => $finalProposal,
                'amount' => (float) $finalAmount,
                'period' => (int) $finalDelivery,
                'milestone_percentage' => 100,
            ], ['project_id' => $project->id, 'bid_id' => $bid->id]);
            $apiElapsed = (int) round((microtime(true) - $apiStart) * 1000);

            $result = Arr::get($response, 'result') ?? $response;
            if (!Arr::get($result, 'id')) {
                throw new RuntimeException('Freelancer bid response did not include a bid id.');
            }

            $totalMs = $project->detected_at ? $project->detected_at->diffInMilliseconds(now()) : null;
            $queueMs = $project->bid_queued_at ? $project->bid_queued_at->diffInMilliseconds(now()) : null;

            $bid->fill([
                'freelancer_bid_id' => Arr::get($result, 'id'),
                'status' => 'submitted',
                'submitted_at' => now(),
                'api_response_time_ms' => $apiElapsed,
                'queue_processing_time_ms' => $queueMs,
                'total_time_to_bid_ms' => $totalMs,
                'response_data' => $this->redact(is_array($result) ? $result : []),
                'error_message' => null,
            ])->save();

            $project->fill([
                'automation_status' => 'bid',
                'bid_status' => 'submitted',
                'bid_submitted_at' => now(),
                'total_time_to_bid_ms' => $totalMs,
                'total_processing_time_ms' => $project->matching_started_at ? $project->matching_started_at->diffInMilliseconds(now()) : null,
            ])->save();

            $this->portfolioLinks->incrementUsage($selectedLinkIds);
            $this->logger->log('bid_submitted', 'Bid submitted to Freelancer API', $account, $project, $bid, ['remote_bid_id' => $bid->freelancer_bid_id]);

            FreelancerAuditLog::create([
                'user_id' => $userId,
                'freelancer_account_id' => $account->id,
                'action' => $manual ? 'bid.manual_submitted' : 'bid.auto_submitted',
                'entity_type' => FreelancerBid::class,
                'entity_id' => $bid->id,
                'new_value' => [
                    'project_id' => $project->freelancer_project_id,
                    'amount' => $finalAmount,
                    'dry_run' => false,
                ],
            ]);

            return $bid->fresh();
        });
    }

    public function simulate(FreelancerAccount $account, FreelancerProject $project, bool $manual = false): array
    {
        $account->loadMissing('settings');
        $settings = $this->settingsService->forAccount($account);
        $checks = [];
        $ready = true;

        $result = $this->matching->evaluate($account, $project, $project->strategy ?: $this->matching->pickStrategy($account, $project));
        $checks['skill'] = ['ok' => $result['skill_match_score'] >= ($project->strategy?->min_skill_match_percent ?? $account->min_skill_match_percent), 'detail' => $result['skill_match_score'].'%'];
        $checks['keyword'] = ['ok' => !str_contains(strtolower((string) $result['reject_reason']), 'keyword'), 'detail' => $result['keyword_score'].'%'];
        $checks['country'] = ['ok' => (bool) $result['country_match'], 'detail' => $project->country ?: 'n/a'];
        $checks['duplicate'] = ['ok' => !$project->hasSubmittedBid(), 'detail' => $project->hasSubmittedBid() ? 'Existing bid found' : 'No local bid'];
        $checks['schedule'] = ['ok' => $this->matching->isWithinSchedule($account, $project->strategy), 'detail' => $account->timezone ?: config('freelancer.timezone')];

        try { $this->assertCanSubmit($account, $project, $manual); $checks['limits'] = ['ok' => true, 'detail' => 'Within limits']; }
        catch (RuntimeException $e) { $checks['limits'] = ['ok' => false, 'detail' => $e->getMessage()]; $ready = false; }

        foreach ($checks as $check) {
            if (!$check['ok']) {
                $ready = false;
            }
        }
        if (!$result['qualified']) {
            $ready = false;
        }

        $finalReason = 'READY TO BID';
        if (!$ready) {
            if (($checks['schedule']['ok'] ?? true) === false) {
                $finalReason = 'Outside configured bidding schedule.';
            } elseif (($checks['duplicate']['ok'] ?? true) === false) {
                $finalReason = $checks['duplicate']['detail'];
            } elseif (($checks['limits']['ok'] ?? true) === false) {
                $finalReason = $checks['limits']['detail'];
            } else {
                $finalReason = $result['reject_reason'] ?: collect($checks)->firstWhere('ok', false)['detail'] ?? 'BLOCKED';
            }
        }

        return [
            'ready' => $ready,
            'match_score' => $result['match_score'],
            'matched_strategy' => $project->strategy?->name ?? $this->matching->pickStrategy($account, $project)?->name,
            'bid_amount' => $project->suggested_amount ?? $this->calculateAmount($project, $project->strategy),
            'delivery_days' => $project->suggested_delivery_days ?? $this->calculateDelivery($project, $project->strategy),
            'proposal' => $project->suggested_proposal,
            'selected_portfolios' => $project->selected_portfolio_ids,
            'checks' => $checks,
            'final_reason' => $finalReason,
        ];
    }

    public function assertCanSubmit(FreelancerAccount $account, FreelancerProject $project, bool $manual = false): void
    {
        $settings = $this->settingsService->forAccount($account);
        if (!$account->is_connected || !$account->tokenLooksValid()) {
            throw new RuntimeException('Freelancer account is not connected or token is invalid.');
        }
        if ($account->global_paused) {
            throw new RuntimeException('Global Freelancer automation is paused.');
        }
        if ($account->dry_run) {
            return;
        }
        if (!$manual && !$account->automation_enabled) {
            throw new RuntimeException('Automation is disabled for this account.');
        }
        if (!$manual && $account->automation_mode === 'manual') {
            throw new RuntimeException('Account is in manual mode.');
        }
        if (in_array($settings->proposal_mode, ['ai', 'hybrid'], true) && $settings->ai_enabled && !$settings->plainAiApiKey()) {
            throw new RuntimeException('Configure and test an AI provider before enabling AI bidding.');
        }
        if (!$project->strategy && !$settings->default_strategy_id) {
            throw new RuntimeException('Create an active bidding strategy before enabling automatic bidding.');
        }
        if (!$this->matching->isWithinSchedule($account, $project->strategy)) {
            throw new RuntimeException('Outside configured bidding schedule.');
        }
        $this->assertWithinLimits($account, $project->strategy);
    }

    public function assertWithinLimits(FreelancerAccount $account, ?FreelancerStrategy $strategy = null): void
    {
        $tz = $account->timezone ?: config('freelancer.timezone');
        $now = now($tz);
        $submitted = fn ($from) => FreelancerBid::where('freelancer_account_id', $account->id)
            ->where('status', 'submitted')
            ->where('is_dry_run', false)
            ->where('submitted_at', '>=', $from)
            ->count();

        $dailyLimit = $strategy?->daily_limit ?: (int) $account->daily_bid_limit;
        if ($submitted($now->copy()->startOfDay()) >= $dailyLimit) {
            throw new RuntimeException('Daily bid limit reached.');
        }
        if ($submitted($now->copy()->startOfHour()) >= (int) $account->hourly_bid_limit) {
            throw new RuntimeException('Hourly bid limit reached.');
        }
        if ($submitted($now->copy()->startOfMonth()) >= (int) $account->monthly_bid_limit) {
            throw new RuntimeException('Monthly bid limit reached.');
        }
    }

    public function calculateAmount(FreelancerProject $project, ?FreelancerStrategy $strategy): ?float
    {
        if (!$strategy) {
            return $project->budget_min ? (float) $project->budget_min : null;
        }

        $amount = match ($strategy->bid_amount_mode) {
            'fixed' => (float) $strategy->bid_fixed_amount,
            'minimum' => (float) ($project->budget_min ?? $strategy->bid_min),
            'maximum' => (float) ($project->budget_max ?? $strategy->bid_max),
            'range' => (float) ($strategy->bid_min ?? $project->budget_min),
            'percent_min' => $project->budget_min !== null ? round(((float) $project->budget_min) * (((float) ($strategy->bid_percent ?? 100)) / 100), 2) : (float) $strategy->bid_fixed_amount,
            'percent_max' => $project->budget_max !== null ? round(((float) $project->budget_max) * (((float) ($strategy->bid_percent ?? 100)) / 100), 2) : (float) $strategy->bid_fixed_amount,
            default => (float) ($project->budget_min ?? $strategy->bid_fixed_amount),
        };

        if ($strategy->bid_min !== null) {
            $amount = max($amount, (float) $strategy->bid_min);
        }
        if ($strategy->bid_max !== null) {
            $amount = min($amount, (float) $strategy->bid_max);
        }

        return $amount > 0 ? $amount : null;
    }

    public function calculateDelivery(FreelancerProject $project, ?FreelancerStrategy $strategy): int
    {
        if (!$strategy) {
            return 7;
        }
        $rules = $strategy->delivery_rules;
        if (is_array($rules) && $project->budget_max !== null) {
            foreach ($rules as $rule) {
                $min = Arr::get($rule, 'min', 0);
                $max = Arr::get($rule, 'max', PHP_FLOAT_MAX);
                $days = Arr::get($rule, 'days');
                if ($days && $project->budget_max >= $min && $project->budget_max <= $max) {
                    return (int) $days;
                }
            }
        }

        return max(1, (int) ($strategy->delivery_days ?: 7));
    }

    public function renderProposal(FreelancerAccount $account, FreelancerProject $project, ?FreelancerStrategy $strategy, array $matchResult, int $delivery, array $portfolioLinkIds = []): string
    {
        $settings = $this->settingsService->forAccount($account);
        $template = $strategy?->template_id ? FreelancerBidTemplate::find($strategy->template_id) : null;
        if (!$template && $settings->default_template_id) {
            $template = FreelancerBidTemplate::find($settings->default_template_id);
        }
        if (!$template) {
            $template = FreelancerBidTemplate::where('freelancer_account_id', $account->id)->where('is_active', true)->latest()->first();
        }

        $portfolioText = $account->portfolioLinks()->whereIn('id', $portfolioLinkIds)->get()
            ->map(fn ($row) => $row->title.' ('.$row->url.')')
            ->implode("\n");

        $vars = [
            '{{project_title}}' => $project->title,
            '{{client_name}}' => $project->client_username ?: 'there',
            '{{matched_skills}}' => implode(', ', $matchResult['matched_skill_names'] ?? []),
            '{{budget}}' => trim(($project->currency ? $project->currency.' ' : '').($project->budget_min ?? '').(($project->budget_max && $project->budget_max != $project->budget_min) ? '-'.$project->budget_max : '')),
            '{{delivery_days}}' => (string) $delivery,
            '{{portfolio}}' => $portfolioText ?: 'Available on request',
            '{{freelancer_name}}' => $account->display_name ?: $account->username ?: 'Freelancer',
        ];

        $content = $template?->content ?: "Hello {{client_name}},\n\nI reviewed your project \"{{project_title}}\" and can help with {{matched_skills}}.\n\nI can deliver within {{delivery_days}} days.\n\nRelevant work:\n{{portfolio}}\n\nBest regards,\n{{freelancer_name}}";
        return strtr($content, $vars);
    }

    protected function remoteDuplicateBidExists(FreelancerAccount $account, FreelancerProject $project): bool
    {
        try {
            $response = $this->api->get('projects/0.1/bids/', [
                'limit' => 25,
                'offset' => 0,
            ], ['project_id' => $project->id], [
                'projects' => [(int) $project->freelancer_project_id],
            ]);
        } catch (\Throwable) {
            return false;
        }

        $bids = Arr::get($response, 'result.bids') ?? Arr::get($response, 'result') ?? [];
        if (!is_array($bids)) {
            return false;
        }

        foreach ($bids as $bid) {
            if (!is_array($bid)) {
                continue;
            }
            $bidderId = Arr::get($bid, 'bidder_id') ?? Arr::get($bid, 'bidder.id') ?? Arr::get($bid, 'user_id');
            if ($bidderId && (int) $bidderId === (int) $account->freelancer_user_id) {
                return true;
            }
        }

        return false;
    }

    protected function redact(array $payload): array
    {
        $json = json_encode($payload) ?: '{}';
        $json = preg_replace('/"(access_token|refresh_token|client_secret|ai_api_key)"\s*:\s*"[^"]*"/i', '"$1":"[redacted]"', $json);
        return json_decode($json, true) ?: [];
    }
}
