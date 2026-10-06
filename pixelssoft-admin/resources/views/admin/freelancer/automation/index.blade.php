@extends('admin.layout')

@section('title', 'Freelancer Automation')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div class="page-header" style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
    <div>
        <h1 style="margin:0;">Automation</h1>
        <p class="text-muted">Safety controls for bidding. Dry run and emergency pause are always available.</p>
    </div>
    <form method="post" action="{{ route('admin.freelancer.automation.pause') }}" onsubmit="return confirm('Pause ALL Freelancer automation?')">
        @csrf
        <button class="btn btn-danger" type="submit">PAUSE ALL AUTOMATION</button>
    </form>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Automation</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->automation_enabled && !$account->global_paused ? 'ON' : 'OFF' }}</div></div>
    <div class="stat-card"><div class="stat-label">Mode</div><div class="stat-value" style="font-size:1.1rem;">{{ strtoupper($account->automation_mode) }}</div></div>
    <div class="stat-card"><div class="stat-label">Dry Run</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->dry_run ? 'YES' : 'NO' }}</div></div>
    <div class="stat-card"><div class="stat-label">Global Pause</div><div class="stat-value" style="font-size:1.1rem;">{{ $account->global_paused ? 'YES' : 'NO' }}</div></div>
</div>

<div class="card">
    <div class="card-header"><h2 style="margin:0;">Settings</h2></div>
    <div class="card-body">
        <form method="post" action="{{ route('admin.freelancer.automation.update') }}" class="admin-form admin-form-narrow">
            @csrf
            @method('PUT')
            <div class="form-check-grid">
                <label class="form-check"><input type="checkbox" name="automation_enabled" value="1" @checked($account->automation_enabled)> Automation enabled</label>
                <label class="form-check"><input type="checkbox" name="global_paused" value="1" @checked($account->global_paused)> Global pause</label>
                <label class="form-check"><input type="checkbox" name="dry_run" value="1" @checked($account->dry_run)> Dry run (discover/match only — no real bids)</label>
            </div>
            <label>Mode
                <select name="automation_mode" class="form-control">
                    <option value="manual" @selected($account->automation_mode==='manual')>Manual</option>
                    <option value="approval" @selected($account->automation_mode==='approval')>Approval required</option>
                    <option value="automatic" @selected($account->automation_mode==='automatic')>Full automation</option>
                </select>
            </label>
            <label>Timezone
                <select name="timezone" class="form-control">
                    @foreach(timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" @selected($account->timezone===$tz)>{{ $tz }}</option>
                    @endforeach
                </select>
            </label>
            <div class="form-row">
                <label>Daily bid limit<input type="number" name="daily_bid_limit" value="{{ $account->daily_bid_limit }}" class="form-control"></label>
                <label>Hourly bid limit<input type="number" name="hourly_bid_limit" value="{{ $account->hourly_bid_limit }}" class="form-control"></label>
                <label>Monthly bid limit<input type="number" name="monthly_bid_limit" value="{{ $account->monthly_bid_limit }}" class="form-control"></label>
            </div>
            <label>Bid delay
                <select name="bid_delay_seconds" class="form-control">
                    @foreach([0,30,60,120,300,600] as $sec)
                        <option value="{{ $sec }}" @selected((int)$account->bid_delay_seconds===$sec)>{{ $sec }} seconds</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div>
</div>
@endsection
