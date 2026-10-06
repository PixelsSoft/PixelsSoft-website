<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Freelancer\FreelancerAuditLog;
use App\Models\Freelancer\FreelancerBidTemplate;
use App\Models\Freelancer\FreelancerCategory;
use App\Models\Freelancer\FreelancerStrategy;
use App\Services\Freelancer\AiProposalService;
use App\Services\Freelancer\FreelancerAccountResolver;
use App\Services\Freelancer\FreelancerProfileService;
use App\Services\Freelancer\FreelancerSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use RuntimeException;

class FreelancerSettingsController extends Controller
{
    public function index(FreelancerAccountResolver $resolver, FreelancerSettingsService $settingsService)
    {
        $account = $resolver->firstOrCreateForUser()->load(['settings', 'skills', 'strategies', 'bidTemplates']);
        $settings = $settingsService->forAccount($account);
        $timezones = timezone_identifiers_list();
        $categories = FreelancerCategory::query()->orderBy('name')->get();
        $countries = $account->projects()->select('country', 'country_code')->whereNotNull('country')->distinct()->orderBy('country')->get();

        return view('admin.freelancer.settings.index', [
            'account' => $account,
            'settings' => $settings,
            'timezones' => $timezones,
            'categories' => $categories,
            'countries' => $countries,
            'strategies' => $account->strategies()->orderBy('priority')->get(),
            'templates' => $account->bidTemplates()->latest()->get(),
            'tab' => request('tab', 'account'),
            'aiTestResult' => session('ai_test_result'),
        ]);
    }

    public function update(Request $request, FreelancerAccountResolver $resolver, FreelancerSettingsService $settingsService)
    {
        $account = $resolver->firstOrCreateForUser()->load('settings');
        $settings = $settingsService->forAccount($account);
        $section = $request->input('section', 'general');

        match ($section) {
            'api' => $this->updateApi($request, $account, $settings),
            'ai' => $this->updateAi($request, $account, $settings),
            'countries' => $this->updateCountries($request, $account),
            'skills' => $this->updateSkills($request, $account),
            'categories' => $this->updateCategories($request, $account),
            'keywords' => $this->updateKeywords($request, $account),
            'project_filters' => $this->updateProjectFilters($request, $account),
            'client_filters' => $this->updateClientFilters($request, $account),
            'bid' => $this->updateBidSettings($request, $account, $settings),
            'schedule' => $this->updateSchedule($request, $account),
            'limits' => $this->updateLimits($request, $account),
            'notifications' => $this->updateNotifications($request, $settings),
            'automation' => $this->updateAutomation($request, $account, $settings),
            'advanced' => $this->updateAdvanced($request, $account, $settings),
            default => null,
        };

        FreelancerAuditLog::create([
            'user_id' => auth()->id(),
            'freelancer_account_id' => $account->id,
            'action' => 'settings.updated.'.$section,
            'entity_type' => $account::class,
            'entity_id' => $account->id,
        ]);

        return redirect()->route('admin.freelancer.settings.index', ['tab' => $section])->with('success', 'Freelancer settings saved.');
    }

    public function testApi(FreelancerAccountResolver $resolver, FreelancerProfileService $profiles)
    {
        $account = $resolver->firstOrCreateForUser();
        if (!$account->is_connected) {
            return back()->with('error', 'Connect a Freelancer account before testing the API connection.');
        }

        try {
            $profiles->testConnection($account);
            return back()->with('success', 'Freelancer API test succeeded.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Freelancer API test failed: '.$e->getMessage());
        }
    }

    public function testAi(Request $request, FreelancerAccountResolver $resolver, AiProposalService $ai)
    {
        $account = $resolver->firstOrCreateForUser();
        $sample = $request->validate([
            'sample_title' => ['required', 'string', 'max:200'],
            'sample_description' => ['required', 'string', 'max:5000'],
            'sample_skills' => ['nullable', 'string'],
            'sample_budget' => ['nullable', 'string', 'max:120'],
        ]);

        try {
            $result = $ai->testConnection($account, $sample + [
                'skills' => collect(preg_split('/[\n,]+/', (string) ($sample['sample_skills'] ?? '')))->map(fn ($v) => trim($v))->filter()->values()->all(),
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'AI test failed: '.$e->getMessage());
        }

        return redirect()->route('admin.freelancer.settings.index', ['tab' => 'ai'])->with('success', 'AI test succeeded.')->with('ai_test_result', $result);
    }

    protected function updateApi(Request $request, $account, $settings): void
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:500'],
            'oauth_redirect_uri' => ['required', 'url', 'max:500'],
            'api_base_url' => ['required', 'url', 'max:500'],
            'environment' => ['required', 'in:sandbox,production'],
            'api_timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'api_retries' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        $settings->fill($data)->save();
    }

    protected function updateAi(Request $request, $account, $settings): void
    {
        $data = $request->validate([
            'proposal_mode' => ['required', 'in:template,ai,hybrid'],
            'ai_provider' => ['nullable', 'in:openai,anthropic,gemini,openai_compatible'],
            'ai_api_key' => ['nullable', 'string', 'max:1000'],
            'ai_model' => ['nullable', 'string', 'max:255'],
            'ai_base_url' => ['nullable', 'url', 'max:500'],
            'ai_temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'ai_max_tokens' => ['required', 'integer', 'min:50', 'max:8000'],
            'ai_timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'ai_retries' => ['required', 'integer', 'min:0', 'max:5'],
            'proposal_style' => ['required', 'in:short,medium,detailed'],
            'proposal_max_characters' => ['required', 'integer', 'min:200', 'max:10000'],
            'ai_system_prompt' => ['required', 'string', 'max:20000'],
            'ai_proposal_prompt' => ['required', 'string', 'max:20000'],
            'ai_failure_behavior' => ['required', 'in:fallback_template,manual_approval,do_not_bid'],
            'ai_enabled' => ['nullable', 'boolean'],
            'ai_proposal_enabled' => ['nullable', 'boolean'],
            'ai_bid_amount_enabled' => ['nullable', 'boolean'],
            'ai_delivery_enabled' => ['nullable', 'boolean'],
            'ai_portfolio_enabled' => ['nullable', 'boolean'],
            'fallback_template_enabled' => ['nullable', 'boolean'],
        ]);

        $data['ai_enabled'] = $request->boolean('ai_enabled');
        $data['ai_proposal_enabled'] = $request->boolean('ai_proposal_enabled');
        $data['ai_bid_amount_enabled'] = $request->boolean('ai_bid_amount_enabled');
        $data['ai_delivery_enabled'] = $request->boolean('ai_delivery_enabled');
        $data['ai_portfolio_enabled'] = $request->boolean('ai_portfolio_enabled');
        $data['fallback_template_enabled'] = $request->boolean('fallback_template_enabled');

        if (($data['proposal_mode'] === 'ai' || $data['proposal_mode'] === 'hybrid') && !$data['ai_enabled']) {
            throw new RuntimeException('Enable AI before selecting AI or Hybrid proposal mode.');
        }

        $settings->fill($data)->save();
    }

    protected function updateCountries(Request $request, $account): void
    {
        $data = $request->validate([
            'country_mode' => ['required', 'in:all,include,exclude'],
            'country_codes' => ['nullable', 'string'],
        ]);
        $account->fill([
            'country_mode' => $data['country_mode'],
            'country_codes' => $this->parseList($data['country_codes'] ?? null),
        ])->save();
    }

    protected function updateSkills(Request $request, $account): void
    {
        $data = $request->validate([
            'min_skill_match_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);
        $account->fill($data)->save();
    }

    protected function updateCategories(Request $request, $account): void
    {
        $data = $request->validate([
            'include_category_ids' => ['nullable', 'array'],
            'include_category_ids.*' => ['integer'],
            'exclude_category_ids' => ['nullable', 'array'],
            'exclude_category_ids.*' => ['integer'],
        ]);
        $account->fill([
            'include_category_ids' => array_values(array_unique($data['include_category_ids'] ?? [])),
            'exclude_category_ids' => array_values(array_unique($data['exclude_category_ids'] ?? [])),
        ])->save();
    }

    protected function updateKeywords(Request $request, $account): void
    {
        $data = $request->validate([
            'positive_keywords' => ['nullable', 'string'],
            'negative_keywords' => ['nullable', 'string'],
            'min_positive_keywords' => ['required', 'integer', 'min:0', 'max:50'],
        ]);
        $account->fill([
            'positive_keywords' => $this->parseList($data['positive_keywords'] ?? null),
            'negative_keywords' => $this->parseList($data['negative_keywords'] ?? null),
            'min_positive_keywords' => $data['min_positive_keywords'],
        ])->save();
    }

    protected function updateProjectFilters(Request $request, $account): void
    {
        $data = $request->validate([
            'project_type_filter' => ['required', 'in:fixed,hourly,both'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate_min' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate_max' => ['nullable', 'numeric', 'min:0'],
            'max_project_age_minutes' => ['nullable', 'integer', 'min:1'],
            'max_bid_count' => ['nullable', 'integer', 'min:0'],
        ]);
        $account->fill($data)->save();
    }

    protected function updateClientFilters(Request $request, $account): void
    {
        $data = $request->validate([
            'min_client_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'min_client_reviews' => ['nullable', 'integer', 'min:0'],
        ]);
        $account->fill($data)->save();
    }

    protected function updateBidSettings(Request $request, $account, $settings): void
    {
        $data = $request->validate([
            'default_strategy_id' => ['nullable', 'integer'],
            'default_template_id' => ['nullable', 'integer'],
            'proposal_mode' => ['required', 'in:template,ai,hybrid'],
            'max_portfolio_links_per_bid' => ['required', 'integer', 'min:1', 'max:10'],
            'bid_delay_seconds' => ['required', 'integer', 'in:0,30,60,120,300,600'],
        ]);
        $account->fill(['bid_delay_seconds' => $data['bid_delay_seconds']])->save();
        $settings->fill(Arr::except($data, ['bid_delay_seconds']))->save();
    }

    protected function updateSchedule(Request $request, $account): void
    {
        $request->validate(['timezone' => ['required', 'timezone']]);
        $schedule = [];
        foreach ([1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday', 7 => 'sunday'] as $dayNumber => $dayKey) {
            $enabled = $request->boolean($dayKey.'_enabled');
            $raw = trim((string) $request->input($dayKey.'_ranges', ''));
            if (!$enabled || $raw === '') {
                $schedule[] = ['days' => [$dayNumber], 'enabled' => false, 'start' => '00:00', 'end' => '00:00'];
                continue;
            }
            foreach ($this->parseRanges($raw) as $range) {
                $schedule[] = ['days' => [$dayNumber], 'enabled' => true, 'start' => $range['start'], 'end' => $range['end']];
            }
        }
        $account->fill(['timezone' => $request->input('timezone'), 'schedule' => $schedule])->save();
    }

    protected function updateLimits(Request $request, $account,): void
    {
        $data = $request->validate([
            'daily_bid_limit' => ['required', 'integer', 'min:0', 'max:1000'],
            'hourly_bid_limit' => ['required', 'integer', 'min:0', 'max:100'],
            'monthly_bid_limit' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);
        $account->fill($data)->save();
    }

    protected function updateNotifications(Request $request, $settings): void
    {
        $keys = ['account_disconnected', 'token_expired', 'automation_paused', 'bid_submitted', 'bid_failed', 'limit_reached', 'approval_required'];
        $prefs = [];
        foreach ($keys as $key) {
            $prefs[$key] = $request->boolean('notify_'.$key);
        }
        $settings->fill(['notification_preferences' => $prefs])->save();
    }

    protected function updateAutomation(Request $request, $account, $settings): void
    {
        $data = $request->validate([
            'automation_enabled' => ['nullable', 'boolean'],
            'dry_run' => ['nullable', 'boolean'],
            'global_paused' => ['nullable', 'boolean'],
            'automation_mode' => ['required', 'in:manual,approval,automatic'],
            'max_bid_submission_seconds' => ['required', 'integer', 'in:30,60,90,120,300'],
        ]);

        $account->fill([
            'automation_enabled' => $request->boolean('automation_enabled'),
            'dry_run' => $request->boolean('dry_run'),
            'global_paused' => $request->boolean('global_paused'),
            'automation_mode' => $data['automation_mode'],
        ]);

        if ($request->boolean('automation_enabled')) {
            if (!$account->is_connected) {
                throw new RuntimeException('Connect a Freelancer account before enabling automatic bidding.');
            }
            if ($data['automation_mode'] === 'automatic' && !$account->strategies()->where('is_active', true)->exists() && !$settings->default_strategy_id) {
                throw new RuntimeException('Create an active bidding strategy before enabling automatic bidding.');
            }
            if (in_array($settings->proposal_mode, ['ai', 'hybrid'], true) && $settings->ai_enabled && !$settings->plainAiApiKey()) {
                throw new RuntimeException('Configure and test an AI provider before enabling AI bidding.');
            }
        }

        $account->save();
        $settings->fill(['max_bid_submission_seconds' => $data['max_bid_submission_seconds']])->save();
    }

    protected function updateAdvanced(Request $request, $account, $settings): void
    {
        $data = $request->validate([
            'score_skill' => ['required', 'integer', 'min:0', 'max:100'],
            'score_keyword' => ['required', 'integer', 'min:0', 'max:100'],
            'score_budget' => ['required', 'integer', 'min:0', 'max:100'],
            'score_country' => ['required', 'integer', 'min:0', 'max:100'],
            'score_client' => ['required', 'integer', 'min:0', 'max:100'],
            'score_freshness' => ['required', 'integer', 'min:0', 'max:100'],
        ]);
        $account->fill([
            'score_weights' => [
                'skill' => $data['score_skill'],
                'keyword' => $data['score_keyword'],
                'budget' => $data['score_budget'],
                'country' => $data['score_country'],
                'client' => $data['score_client'],
                'freshness' => $data['score_freshness'],
            ],
        ])->save();
    }

    protected function parseList(?string $value): array
    {
        return collect(preg_split('/[\n,]+/', (string) $value))->map(fn ($v) => trim($v))->filter()->values()->all();
    }

    protected function parseRanges(string $raw): array
    {
        $ranges = [];
        foreach (preg_split('/[\n,]+/', $raw) as $line) {
            $line = trim($line);
            if ($line === '' || !str_contains($line, '-')) {
                continue;
            }
            [$start, $end] = array_map('trim', explode('-', $line, 2));
            $ranges[] = ['start' => $start, 'end' => $end];
        }
        return $ranges;
    }
}
