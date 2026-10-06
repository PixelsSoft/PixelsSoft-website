@extends('admin.layout')

@section('title', 'Freelancer Settings')

@section('content')
@php
    $tabs = [
        'account' => 'Account',
        'api' => 'API',
        'ai' => 'AI Bidding',
        'countries' => 'Countries',
        'skills' => 'Skills',
        'categories' => 'Categories',
        'keywords' => 'Keywords',
        'project_filters' => 'Project Filters',
        'client_filters' => 'Client Filters',
        'bid' => 'Bid Settings',
        'portfolio' => 'Portfolio',
        'schedule' => 'Schedule',
        'limits' => 'Limits',
        'notifications' => 'Notifications',
        'automation' => 'Automation',
        'advanced' => 'Advanced',
    ];
    $selectedTab = $tab ?? request('tab', 'account');
    $dayMap = [1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday', 7 => 'sunday'];
    $scheduleByDay = [];
    foreach ($dayMap as $dayNumber => $dayKey) {
        $slots = collect($account->schedule ?? [])->filter(fn ($slot) => in_array($dayNumber, $slot['days'] ?? [], true) || in_array((string) $dayNumber, $slot['days'] ?? [], true));
        $enabled = $slots->contains(fn ($slot) => $slot['enabled'] ?? true);
        $ranges = $slots->filter(fn ($slot) => $slot['enabled'] ?? true)->map(fn ($slot) => ($slot['start'] ?? '09:00').'-'.($slot['end'] ?? '18:00'))->implode("\n");
        $scheduleByDay[$dayKey] = ['enabled' => $enabled, 'ranges' => $ranges];
    }
@endphp

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div class="page-header" style="margin-bottom:1rem;">
    <h1 style="margin:0;">Freelancer Settings</h1>
    <p class="text-muted" style="margin:.25rem 0 0;">Complete operational settings for account connection, official API access, AI bidding, filters, schedule, limits, and automation safety.</p>
</div>

<div class="card" style="margin-bottom:1rem; padding:1rem;">
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.freelancer.settings.index', ['tab' => $key]) }}" class="btn btn-sm {{ $selectedTab === $key ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

@if($selectedTab === 'account')
<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Connected</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->is_connected ? 'YES' : 'NO' }}</div></div>
    <div class="stat-card"><div class="stat-label">Freelancer User ID</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->freelancer_user_id ?: '—' }}</div></div>
    <div class="stat-card"><div class="stat-label">Username</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->username ?: '—' }}</div></div>
    <div class="stat-card"><div class="stat-label">Automation</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->automation_enabled ? 'ACTIVE' : 'OFF' }}</div></div>
</div>
<div class="card" style="padding:1rem;">
    <div style="display:grid;gap:.5rem;">
        <div><strong>Display Name:</strong> {{ $account->display_name ?: '—' }}</div>
        <div><strong>Country:</strong> {{ $account->country ?: '—' }}</div>
        <div><strong>Profile URL:</strong> @if($account->profile_url)<a href="{{ $account->profile_url }}" target="_blank" rel="noopener">{{ $account->profile_url }}</a>@else — @endif</div>
        <div><strong>Account Status:</strong> {{ $account->tokenStatusLabel() }}</div>
        <div><strong>Connected At:</strong> {{ $account->connected_at ?: '—' }}</div>
        <div><strong>Last Sync:</strong> {{ $account->last_sync_at ?: '—' }}</div>
        <div><strong>Token Status:</strong> {{ $account->tokenStatusLabel() }}</div>
        <div><strong>Last Connection Test:</strong> {{ $account->last_connection_test_at ?: '—' }} @if(!is_null($account->last_connection_test_ok)) ({{ $account->last_connection_test_ok ? 'OK' : 'FAILED' }}) @endif</div>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:1rem;">
        @if(!$account->is_connected)
            <a href="{{ route('admin.freelancer.oauth.redirect') }}" class="btn btn-primary">Connect Freelancer</a>
        @else
            <a href="{{ route('admin.freelancer.oauth.redirect') }}" class="btn btn-outline">Reconnect</a>
            <form method="post" action="{{ route('admin.freelancer.account.sync') }}">@csrf<button class="btn btn-outline" type="submit">Sync Profile</button></form>
            <form method="post" action="{{ route('admin.freelancer.account.test') }}">@csrf<button class="btn btn-outline" type="submit">Test Connection</button></form>
            <form method="post" action="{{ route('admin.freelancer.account.disconnect') }}" onsubmit="return confirm('Disconnect Freelancer account?')">@csrf<button class="btn btn-danger" type="submit">Disconnect</button></form>
        @endif
    </div>
</div>
@endif

@if($selectedTab === 'api')
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form">
        @csrf @method('PUT')
        <input type="hidden" name="section" value="api">
        <label>Freelancer Client ID<input class="form-control" name="client_id" value="{{ old('client_id', $settings->client_id) }}"></label>
        <label>Freelancer Client Secret<input class="form-control" name="client_secret" type="password" placeholder="{{ $settings->maskedClientSecret() ?: 'Enter new secret' }}"></label>
        <label>OAuth Redirect URL<input class="form-control" name="oauth_redirect_uri" value="{{ old('oauth_redirect_uri', $settings->oauth_redirect_uri) }}"></label>
        <label>API Base URL<input class="form-control" name="api_base_url" value="{{ old('api_base_url', $settings->api_base_url) }}"></label>
        <label>Environment
            <select name="environment" class="form-control">
                <option value="sandbox" @selected($settings->environment==='sandbox')>Sandbox</option>
                <option value="production" @selected($settings->environment==='production')>Production</option>
            </select>
        </label>
        <div class="form-row">
            <label>API Timeout<input class="form-control" type="number" name="api_timeout" value="{{ old('api_timeout', $settings->api_timeout) }}"></label>
            <label>Retry Attempts<input class="form-control" type="number" name="api_retries" value="{{ old('api_retries', $settings->api_retries) }}"></label>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </form>
    <div class="form-actions" style="margin-top:.75rem;">
        <form method="post" action="{{ route('admin.freelancer.settings.test-api') }}">@csrf<button class="btn btn-outline" type="submit">Test Connection</button></form>
        <a href="{{ route('admin.freelancer.oauth.redirect') }}" class="btn btn-outline">Reconnect</a>
        <form method="post" action="{{ route('admin.freelancer.account.sync') }}">@csrf<button class="btn btn-outline" type="submit">Refresh/Synchronize</button></form>
    </div>
</div>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">API Connection Status</div><div class="stat-value" style="font-size:1.1rem;">{{ strtoupper($settings->api_status) }}</div></div>
    <div class="stat-card"><div class="stat-label">Last API Request</div><div class="stat-value" style="font-size:1.1rem;">{{ $settings->api_last_request_at ?: '—' }}</div></div>
    <div class="stat-card"><div class="stat-label">Last Successful Request</div><div class="stat-value" style="font-size:1.1rem;">{{ $settings->api_last_success_at ?: '—' }}</div></div>
    <div class="stat-card"><div class="stat-label">Last Failed Request</div><div class="stat-value" style="font-size:1.1rem;">{{ $settings->api_last_failure_at ?: '—' }}</div></div>
    <div class="stat-card"><div class="stat-label">API Response Time</div><div class="stat-value" style="font-size:1.1rem;">{{ $settings->api_last_response_time_ms ?: '—' }} ms</div></div>
    <div class="stat-card"><div class="stat-label">Rate Limit</div><div class="stat-value" style="font-size:1.1rem;">{{ data_get($settings->api_rate_limit, 'remaining', '—') }}</div></div>
</div>
@endif

@if($selectedTab === 'ai')
<div class="card" style="padding:1rem; margin-bottom:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form settings-form-wide">
        @csrf @method('PUT')
        <input type="hidden" name="section" value="ai">
        <div class="form-row">
            <label>Proposal Mode
                <select name="proposal_mode" class="form-control">
                    <option value="template" @selected($settings->proposal_mode==='template')>Template</option>
                    <option value="ai" @selected($settings->proposal_mode==='ai')>AI</option>
                    <option value="hybrid" @selected($settings->proposal_mode==='hybrid')>Hybrid</option>
                </select>
            </label>
            <label>AI Provider
                <select name="ai_provider" class="form-control">
                    <option value="">Select provider</option>
                    <option value="openai" @selected($settings->ai_provider==='openai')>OpenAI</option>
                    <option value="anthropic" @selected($settings->ai_provider==='anthropic')>Anthropic</option>
                    <option value="gemini" @selected($settings->ai_provider==='gemini')>Google Gemini</option>
                    <option value="openai_compatible" @selected($settings->ai_provider==='openai_compatible')>Custom OpenAI-compatible API</option>
                </select>
            </label>
            <label>Model<input class="form-control" name="ai_model" value="{{ old('ai_model', $settings->ai_model) }}"></label>
            <label>Base URL<input class="form-control" name="ai_base_url" value="{{ old('ai_base_url', $settings->ai_base_url) }}"></label>
        </div>
        <label>API Key<input class="form-control" name="ai_api_key" type="password" placeholder="{{ $settings->maskedAiApiKey() ?: 'Enter new API key' }}"></label>
        <div class="form-row">
            <label>Temperature<input class="form-control" type="number" step="0.1" name="ai_temperature" value="{{ old('ai_temperature', $settings->ai_temperature) }}"></label>
            <label>Maximum Tokens<input class="form-control" type="number" name="ai_max_tokens" value="{{ old('ai_max_tokens', $settings->ai_max_tokens) }}"></label>
            <label>API Timeout<input class="form-control" type="number" name="ai_timeout" value="{{ old('ai_timeout', $settings->ai_timeout) }}"></label>
            <label>Retry Attempts<input class="form-control" type="number" name="ai_retries" value="{{ old('ai_retries', $settings->ai_retries) }}"></label>
        </div>
        <div class="form-check-grid">
            <label class="form-check"><input type="checkbox" name="ai_enabled" value="1" @checked($settings->ai_enabled)> Enable AI Bidding</label>
            <label class="form-check"><input type="checkbox" name="ai_proposal_enabled" value="1" @checked($settings->ai_proposal_enabled)> Enable AI Proposal Generation</label>
            <label class="form-check"><input type="checkbox" name="ai_bid_amount_enabled" value="1" @checked($settings->ai_bid_amount_enabled)> Enable AI Bid Amount Suggestion</label>
            <label class="form-check"><input type="checkbox" name="ai_delivery_enabled" value="1" @checked($settings->ai_delivery_enabled)> Enable AI Delivery Suggestion</label>
            <label class="form-check"><input type="checkbox" name="ai_portfolio_enabled" value="1" @checked($settings->ai_portfolio_enabled)> Enable AI Portfolio Selection</label>
            <label class="form-check"><input type="checkbox" name="fallback_template_enabled" value="1" @checked($settings->fallback_template_enabled)> Use fallback template</label>
        </div>
        <div class="form-row">
            <label>Proposal Style
                <select name="proposal_style" class="form-control">
                    <option value="short" @selected($settings->proposal_style==='short')>Short</option>
                    <option value="medium" @selected($settings->proposal_style==='medium')>Medium</option>
                    <option value="detailed" @selected($settings->proposal_style==='detailed')>Detailed</option>
                </select>
            </label>
            <label>Maximum Characters<input class="form-control" type="number" name="proposal_max_characters" value="{{ old('proposal_max_characters', $settings->proposal_max_characters) }}"></label>
            <label>AI Failure Behavior
                <select name="ai_failure_behavior" class="form-control">
                    <option value="fallback_template" @selected($settings->ai_failure_behavior==='fallback_template')>Fallback Template</option>
                    <option value="manual_approval" @selected($settings->ai_failure_behavior==='manual_approval')>Manual Approval</option>
                    <option value="do_not_bid" @selected($settings->ai_failure_behavior==='do_not_bid')>Do Not Bid</option>
                </select>
            </label>
        </div>
        <label>System Prompt<textarea class="form-control" rows="6" name="ai_system_prompt">{{ old('ai_system_prompt', $settings->ai_system_prompt) }}</textarea></label>
        <label>Proposal Prompt<textarea class="form-control" rows="6" name="ai_proposal_prompt">{{ old('ai_proposal_prompt', $settings->ai_proposal_prompt) }}</textarea></label>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
<div class="card" style="padding:1rem;">
    <h2 style="margin-top:0;">AI Test Screen</h2>
    <form method="post" action="{{ route('admin.freelancer.settings.test-ai') }}" class="admin-form settings-form">
        @csrf
        <label>Sample Project Title<input class="form-control" name="sample_title"></label>
        <label>Sample Description<textarea class="form-control" name="sample_description" rows="5"></textarea></label>
        <label>Skills<textarea class="form-control" name="sample_skills" rows="3"></textarea></label>
        <label>Budget<input class="form-control" name="sample_budget"></label>
        <button class="btn btn-outline" type="submit">Generate Test Proposal</button>
    </form>
    @if($aiTestResult)
        <div class="card" style="margin-top:1rem; padding:1rem; background:rgba(0,0,0,.02);">
            <div><strong>AI Provider:</strong> {{ $aiTestResult['provider'] ?? '—' }}</div>
            <div><strong>Model:</strong> {{ $aiTestResult['model'] ?? '—' }}</div>
            <div><strong>Processing Time:</strong> {{ $aiTestResult['processing_time_ms'] ?? '—' }} ms</div>
            <div><strong>Suggested Bid:</strong> {{ $aiTestResult['suggested_bid'] ?? '—' }}</div>
            <div><strong>Suggested Delivery:</strong> {{ $aiTestResult['suggested_delivery_days'] ?? '—' }}</div>
            <div><strong>Selected Portfolio:</strong> {{ implode(', ', $aiTestResult['selected_portfolio_urls'] ?? []) ?: '—' }}</div>
            <div style="margin-top:.75rem;"><strong>Generated Proposal:</strong></div>
            <pre style="white-space:pre-wrap;">{{ $aiTestResult['proposal'] ?? '' }}</pre>
        </div>
    @endif
</div>
@endif

@if($selectedTab === 'countries')
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form">
        @csrf @method('PUT')<input type="hidden" name="section" value="countries">
        <label>Country Mode
            <select name="country_mode" class="form-control">
                <option value="all" @selected($account->country_mode==='all')>ALL</option>
                <option value="include" @selected($account->country_mode==='include')>INCLUDE_ONLY</option>
                <option value="exclude" @selected($account->country_mode==='exclude')>EXCLUDE</option>
            </select>
        </label>
        <label>Selected Countries / Codes<textarea class="form-control" name="country_codes" rows="5">{{ implode("\n", $account->country_codes ?? []) }}</textarea></label>
        <div><strong>Observed Countries</strong><div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;">@foreach($countries as $country)<span class="badge badge-draft">{{ $country->country_code ?: $country->country }}</span>@endforeach</div></div>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'skills')
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form settings-form-narrow">
        @csrf @method('PUT')<input type="hidden" name="section" value="skills">
        <label>Minimum Skill Match %<input class="form-control" type="number" name="min_skill_match_percent" value="{{ $account->min_skill_match_percent }}"></label>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
    <div class="table-wrap" style="margin-top:1rem;">
        <table><thead><tr><th>Skill</th><th>Primary</th><th>Secondary</th><th>Automation</th><th>Priority</th></tr></thead><tbody>@foreach($account->skills as $skill)<tr><td>{{ $skill->name }}</td><td>{{ $skill->is_primary ? 'Yes' : 'No' }}</td><td>{{ $skill->is_secondary ? 'Yes' : 'No' }}</td><td>{{ $skill->automation_enabled ? 'Yes' : 'No' }}</td><td>{{ $skill->priority }}</td></tr>@endforeach</tbody></table>
    </div>
</div>
@endif

@if($selectedTab === 'categories')
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form">
        @csrf @method('PUT')<input type="hidden" name="section" value="categories">
        <label>Include Categories
            <select class="form-control" name="include_category_ids[]" multiple size="8">@foreach($categories as $category)<option value="{{ $category->id }}" @selected(in_array($category->id, $account->include_category_ids ?? []))>{{ $category->name }}</option>@endforeach</select>
        </label>
        <label>Exclude Categories
            <select class="form-control" name="exclude_category_ids[]" multiple size="8">@foreach($categories as $category)<option value="{{ $category->id }}" @selected(in_array($category->id, $account->exclude_category_ids ?? []))>{{ $category->name }}</option>@endforeach</select>
        </label>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'keywords')
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form">
        @csrf @method('PUT')<input type="hidden" name="section" value="keywords">
        <label>Positive Keywords<textarea class="form-control" name="positive_keywords" rows="6">{{ implode("\n", $account->positive_keywords ?? []) }}</textarea></label>
        <label>Negative Keywords<textarea class="form-control" name="negative_keywords" rows="6">{{ implode("\n", $account->negative_keywords ?? []) }}</textarea></label>
        <label>Minimum Keyword Matches<input class="form-control" type="number" name="min_positive_keywords" value="{{ $account->min_positive_keywords }}"></label>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'project_filters')
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form">
        @csrf @method('PUT')<input type="hidden" name="section" value="project_filters">
        <label>Project Type<select class="form-control" name="project_type_filter"><option value="both" @selected($account->project_type_filter==='both')>Both</option><option value="fixed" @selected($account->project_type_filter==='fixed')>Fixed</option><option value="hourly" @selected($account->project_type_filter==='hourly')>Hourly</option></select></label>
        <div class="form-row">
            <label>Budget Min<input class="form-control" type="number" step="0.01" name="budget_min" value="{{ $account->budget_min }}"></label>
            <label>Budget Max<input class="form-control" type="number" step="0.01" name="budget_max" value="{{ $account->budget_max }}"></label>
            <label>Hourly Min<input class="form-control" type="number" step="0.01" name="hourly_rate_min" value="{{ $account->hourly_rate_min }}"></label>
            <label>Hourly Max<input class="form-control" type="number" step="0.01" name="hourly_rate_max" value="{{ $account->hourly_rate_max }}"></label>
            <label>Project Age (minutes)<input class="form-control" type="number" name="max_project_age_minutes" value="{{ $account->max_project_age_minutes }}"></label>
            <label>Max Bids<input class="form-control" type="number" name="max_bid_count" value="{{ $account->max_bid_count }}"></label>
        </div>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'client_filters')
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form settings-form-narrow">
        @csrf @method('PUT')<input type="hidden" name="section" value="client_filters">
        <label>Minimum Rating<input class="form-control" type="number" step="0.1" name="min_client_rating" value="{{ $account->min_client_rating }}"></label>
        <label>Minimum Reviews<input class="form-control" type="number" name="min_client_reviews" value="{{ $account->min_client_reviews }}"></label>
        <p class="text-muted">Verified, spend, and previous-project filters remain API limitations unless exposed in official project owner fields.</p>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'bid')
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form">
        @csrf @method('PUT')<input type="hidden" name="section" value="bid">
        <label>Default Strategy<select class="form-control" name="default_strategy_id"><option value="">None</option>@foreach($strategies as $strategy)<option value="{{ $strategy->id }}" @selected($settings->default_strategy_id===$strategy->id)>{{ $strategy->name }}</option>@endforeach</select></label>
        <label>Default Bid Template<select class="form-control" name="default_template_id"><option value="">None</option>@foreach($templates as $template)<option value="{{ $template->id }}" @selected($settings->default_template_id===$template->id)>{{ $template->name }}</option>@endforeach</select></label>
        <label>Proposal Mode<select class="form-control" name="proposal_mode"><option value="template" @selected($settings->proposal_mode==='template')>Template</option><option value="ai" @selected($settings->proposal_mode==='ai')>AI</option><option value="hybrid" @selected($settings->proposal_mode==='hybrid')>Hybrid</option></select></label>
        <label>Maximum Portfolio Links<select class="form-control" name="max_portfolio_links_per_bid">@foreach([1,2,3,4,5] as $n)<option value="{{ $n }}" @selected((int)$settings->max_portfolio_links_per_bid===$n)>{{ $n }}</option>@endforeach</select></label>
        <label>Bid Delay<select class="form-control" name="bid_delay_seconds">@foreach([0,30,60,120,300,600] as $n)<option value="{{ $n }}" @selected((int)$account->bid_delay_seconds===$n)>{{ $n }} seconds</option>@endforeach</select></label>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'portfolio')
<div class="card" style="padding:1rem;">
    <p style="margin-top:0;">Use the dedicated Portfolio Links screen to manage CRM-owned work URLs with multi-skill mapping.</p>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <a class="btn btn-primary" href="{{ route('admin.freelancer.portfolio-links.index') }}">Open Portfolio Links</a>
        <a class="btn btn-outline" href="{{ route('admin.freelancer.portfolio.index') }}">Open Synced Freelancer Portfolio</a>
    </div>
</div>
@endif

@if($selectedTab === 'schedule')
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form">
        @csrf @method('PUT')<input type="hidden" name="section" value="schedule">
        <label>Timezone<select class="form-control" name="timezone">@foreach($timezones as $tz)<option value="{{ $tz }}" @selected($account->timezone===$tz)>{{ $tz }}</option>@endforeach</select></label>
        @foreach($dayMap as $num => $dayKey)
            <div class="schedule-day">
                <label class="form-check"><input type="checkbox" name="{{ $dayKey }}_enabled" value="1" @checked($scheduleByDay[$dayKey]['enabled'])> {{ ucfirst($dayKey) }} enabled</label>
                <label>{{ ucfirst($dayKey) }} ranges<textarea class="form-control" name="{{ $dayKey }}_ranges" rows="3" placeholder="09:00-18:00&#10;19:00-21:00">{{ $scheduleByDay[$dayKey]['ranges'] }}</textarea></label>
            </div>
        @endforeach
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'limits')
@php
    $todayCount = $account->bids()->where('status', 'submitted')->whereDate('submitted_at', now($account->timezone)->toDateString())->count();
    $hourCount = $account->bids()->where('status', 'submitted')->where('submitted_at', '>=', now($account->timezone)->copy()->startOfHour())->count();
    $monthCount = $account->bids()->where('status', 'submitted')->where('submitted_at', '>=', now($account->timezone)->copy()->startOfMonth())->count();
@endphp
<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Daily</div><div class="stat-value">{{ $todayCount }}/{{ $account->daily_bid_limit }}</div></div>
    <div class="stat-card"><div class="stat-label">Hourly</div><div class="stat-value">{{ $hourCount }}/{{ $account->hourly_bid_limit }}</div></div>
    <div class="stat-card"><div class="stat-label">Monthly</div><div class="stat-value">{{ $monthCount }}/{{ $account->monthly_bid_limit }}</div></div>
    <div class="stat-card"><div class="stat-label">Remaining Today</div><div class="stat-value">{{ max(0, $account->daily_bid_limit - $todayCount) }}</div></div>
</div>
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form settings-form-narrow">
        @csrf @method('PUT')<input type="hidden" name="section" value="limits">
        <label>Daily Bid Limit<input class="form-control" type="number" name="daily_bid_limit" value="{{ $account->daily_bid_limit }}"></label>
        <label>Hourly Bid Limit<input class="form-control" type="number" name="hourly_bid_limit" value="{{ $account->hourly_bid_limit }}"></label>
        <label>Monthly Bid Limit<input class="form-control" type="number" name="monthly_bid_limit" value="{{ $account->monthly_bid_limit }}"></label>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'notifications')
@php $prefs = $settings->notification_preferences ?? []; @endphp
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form settings-form-narrow">
        @csrf @method('PUT')<input type="hidden" name="section" value="notifications">
        @foreach(['account_disconnected' => 'Account disconnected', 'token_expired' => 'Token expired', 'automation_paused' => 'Automation paused', 'bid_submitted' => 'Bid submitted', 'bid_failed' => 'Bid failed', 'limit_reached' => 'Limit reached', 'approval_required' => 'Approval required'] as $key => $label)
            <label class="form-check"><input type="checkbox" name="notify_{{ $key }}" value="1" @checked($prefs[$key] ?? false)> {{ $label }}</label>
        @endforeach
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'automation')
<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Freelancer Account</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->is_connected ? 'CONNECTED' : 'NOT CONNECTED' }}</div></div>
    <div class="stat-card"><div class="stat-label">AI</div><div class="stat-value" style="font-size:1.1rem;">{{ $settings->ai_status ? strtoupper($settings->ai_status) : 'NOT CONFIGURED' }}</div></div>
    <div class="stat-card"><div class="stat-label">Automation</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->automation_enabled && !$account->global_paused ? 'ACTIVE' : 'OFF' }}</div></div>
    <div class="stat-card"><div class="stat-label">Mode</div><div class="stat-value" style="font-size:1.1rem;">{{ strtoupper($account->automation_mode) }}</div></div>
    <div class="stat-card"><div class="stat-label">Timezone</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->timezone }}</div></div>
    <div class="stat-card"><div class="stat-label">Target Bid Time</div><div class="stat-value" style="font-size:1.1rem;">{{ $settings->max_bid_submission_seconds }} seconds</div></div>
</div>
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form">
        @csrf @method('PUT')<input type="hidden" name="section" value="automation">
        <div class="form-check-grid">
            <label class="form-check"><input type="checkbox" name="automation_enabled" value="1" @checked($account->automation_enabled)> Automation Enabled</label>
            <label class="form-check"><input type="checkbox" name="dry_run" value="1" @checked($account->dry_run)> Dry Run</label>
            <label class="form-check"><input type="checkbox" name="global_paused" value="1" @checked($account->global_paused)> Pause All Automation</label>
        </div>
        <label>Automation Mode<select class="form-control" name="automation_mode"><option value="manual" @selected($account->automation_mode==='manual')>Manual</option><option value="approval" @selected($account->automation_mode==='approval')>Approval Required</option><option value="automatic" @selected($account->automation_mode==='automatic')>Automatic</option></select></label>
        <label>Maximum Bid Submission Time<select class="form-control" name="max_bid_submission_seconds">@foreach([30,60,90,120,300] as $n)<option value="{{ $n }}" @selected((int)$settings->max_bid_submission_seconds===$n)>{{ $n }} seconds</option>@endforeach</select></label>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif

@if($selectedTab === 'advanced')
@php $weights = $account->score_weights ?? config('freelancer.score_weights'); @endphp
<div class="card" style="padding:1rem;">
    <form method="post" action="{{ route('admin.freelancer.settings.update') }}" class="admin-form settings-form">
        @csrf @method('PUT')<input type="hidden" name="section" value="advanced">
        <div class="form-row">
            <label>Skill Weight<input class="form-control" type="number" name="score_skill" value="{{ $weights['skill'] ?? 40 }}"></label>
            <label>Keyword Weight<input class="form-control" type="number" name="score_keyword" value="{{ $weights['keyword'] ?? 20 }}"></label>
            <label>Budget Weight<input class="form-control" type="number" name="score_budget" value="{{ $weights['budget'] ?? 15 }}"></label>
            <label>Country Weight<input class="form-control" type="number" name="score_country" value="{{ $weights['country'] ?? 10 }}"></label>
            <label>Client Weight<input class="form-control" type="number" name="score_client" value="{{ $weights['client'] ?? 10 }}"></label>
            <label>Freshness Weight<input class="form-control" type="number" name="score_freshness" value="{{ $weights['freshness'] ?? 5 }}"></label>
        </div>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
</div>
@endif
@endsection
