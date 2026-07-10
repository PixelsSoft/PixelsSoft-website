@extends('admin.layout')

@section('title', 'CRM Reports')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Conversion Rate</div>
        <div class="stat-value">{{ $conversionRate }}%</div>
        <small style="color:#6b7280">leads converted</small>
    </div>
    <div class="stat-card">
        <div class="stat-label">Won Value</div>
        <div class="stat-value">${{ number_format($wonValue, 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Lost Deals</div>
        <div class="stat-value">{{ $lostCount }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Pipeline by Stage</h2>
        @if($pipeline)
            <small style="color:#6b7280">{{ $pipeline->name }}</small>
        @endif
    </div>
    @if(!$pipeline)
        <div class="empty-state"><p>No pipeline configured.</p></div>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Stage</th><th>Deals</th><th>Value</th></tr></thead>
                <tbody>
                    @foreach($pipeline->stages as $stage)
                        @php $row = $dealsByStage->get($stage->id); @endphp
                        <tr>
                            <td>
                                <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $stage->color ?? '#6b7280' }};margin-right:8px"></span>
                                {{ $stage->name }}
                            </td>
                            <td>{{ $row->count ?? 0 }}</td>
                            <td>${{ number_format($row->value ?? 0, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <div class="card">
        <div class="card-header"><h2>Leads by Source</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Source</th><th>Count</th></tr></thead>
                <tbody>
                    @forelse($leadsBySource as $row)
                        <tr>
                            <td>{{ $row->source ?: 'Unknown' }}</td>
                            <td>{{ $row->count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No lead data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Leads by Status</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Status</th><th>Count</th></tr></thead>
                <tbody>
                    @forelse($leadsByStatus as $row)
                        <tr>
                            <td><span class="badge badge-draft">{{ $row->status }}</span></td>
                            <td>{{ $row->count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No lead data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
