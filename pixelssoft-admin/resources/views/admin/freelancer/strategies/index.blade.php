@extends('admin.layout')

@section('title', 'Bid Strategies')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="page-header" style="margin-bottom:1rem;"><h1 style="margin:0;">Strategies</h1></div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2 style="margin:0;">Create Strategy</h2></div>
    <div class="card-body">
        <form method="post" action="{{ route('admin.freelancer.strategies.store') }}" class="admin-form admin-form-medium">
            @csrf
            <div class="form-row">
                <label>Name<input name="name" class="form-control" required></label>
                <label>Priority (1=highest)<input type="number" name="priority" value="1" class="form-control"></label>
                <label>Min skill match %<input type="number" name="min_skill_match_percent" value="70" class="form-control"></label>
            </div>
            <div class="form-row">
                <label>Budget min<input type="number" step="0.01" name="budget_min" class="form-control"></label>
                <label>Budget max<input type="number" step="0.01" name="budget_max" class="form-control"></label>
                <label>Min client rating<input type="number" step="0.1" name="min_client_rating" class="form-control"></label>
            </div>
            <div class="form-row">
                <label>Max project age (minutes)<input type="number" name="max_project_age_minutes" value="15" class="form-control"></label>
                <label>Max bid count<input type="number" name="max_bid_count" value="20" class="form-control"></label>
                <label>Bid delay seconds<input type="number" name="bid_delay_seconds" value="60" class="form-control"></label>
                <label>Daily limit<input type="number" name="daily_limit" value="10" class="form-control"></label>
            </div>
            <div class="form-row">
                <label>Bid amount mode
                    <select name="bid_amount_mode" class="form-control">
                        <option value="percent_min">% of minimum budget</option>
                        <option value="percent_max">% of maximum budget</option>
                        <option value="fixed">Fixed amount</option>
                        <option value="minimum">Project minimum</option>
                        <option value="maximum">Project maximum</option>
                        <option value="range">Custom range min</option>
                    </select>
                </label>
                <label>Bid percent<input type="number" step="0.01" name="bid_percent" value="100" class="form-control"></label>
                <label>Fixed amount<input type="number" step="0.01" name="bid_fixed_amount" class="form-control"></label>
                <label>Delivery days<input type="number" name="delivery_days" value="7" class="form-control"></label>
            </div>
            <label class="form-check"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <button class="btn btn-primary" type="submit">Create Strategy</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Priority</th><th>Name</th><th>Active</th><th>Skill %</th><th>Budget</th><th>Mode</th><th></th></tr></thead>
            <tbody>
                @forelse($strategies as $strategy)
                    <tr>
                        <td>{{ $strategy->priority }}</td>
                        <td>{{ $strategy->name }}</td>
                        <td>{{ $strategy->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $strategy->min_skill_match_percent ?? '—' }}</td>
                        <td>{{ $strategy->budget_min }}–{{ $strategy->budget_max }}</td>
                        <td>{{ $strategy->bid_amount_mode }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.freelancer.strategies.destroy', $strategy) }}" onsubmit="return confirm('Delete strategy?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No strategies yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
