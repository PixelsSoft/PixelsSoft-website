@extends('admin.layout')

@section('title', 'PM Reports')

@section('content')
@php
    $totalHours = $billableHours + $nonBillableHours;
    $billablePct = $totalHours > 0 ? round(($billableHours / $totalHours) * 100, 1) : 0;
@endphp

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Billable Hours</div>
        <div class="stat-value">{{ number_format($billableHours, 1) }}</div>
        <small style="color:#6b7280">{{ $billablePct }}% of total</small>
    </div>
    <div class="stat-card">
        <div class="stat-label">Non-Billable Hours</div>
        <div class="stat-value">{{ number_format($nonBillableHours, 1) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Projects at Risk</div>
        <div class="stat-value">{{ $projectsAtRisk->count() }}</div>
        <small style="color:#6b7280">≥80% budget burn</small>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <div class="card">
        <div class="card-header"><h2>Hours by Project</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Project</th><th>Hours</th></tr></thead>
                <tbody>
                    @forelse($hoursByProject as $row)
                        <tr>
                            <td>
                                @if($row->project)
                                    <a href="{{ route('admin.pm.projects.show', $row->project) }}">{{ $row->project->name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ number_format($row->hours, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No time logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Hours by User</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>User</th><th>Hours</th></tr></thead>
                <tbody>
                    @forelse($hoursByUser as $row)
                        <tr>
                            <td>{{ $row->user?->name ?? '—' }}</td>
                            <td>{{ number_format($row->hours, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No time logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Billable vs Non-Billable</h2></div>
    <div style="padding:0 24px 24px">
        <div style="display:flex;height:24px;border-radius:6px;overflow:hidden;background:#e5e7eb">
            @if($totalHours > 0)
                <div style="width:{{ $billablePct }}%;background:#10b981" title="Billable"></div>
                <div style="width:{{ 100 - $billablePct }}%;background:#9ca3af" title="Non-billable"></div>
            @endif
        </div>
        <div style="display:flex;gap:24px;margin-top:12px;font-size:13px;color:#6b7280">
            <span><span style="display:inline-block;width:10px;height:10px;background:#10b981;border-radius:2px;margin-right:6px"></span>Billable {{ number_format($billableHours, 1) }}h</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:#9ca3af;border-radius:2px;margin-right:6px"></span>Non-billable {{ number_format($nonBillableHours, 1) }}h</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Projects at Risk</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Project</th><th>Budget Hours</th><th>Logged</th><th>Burn %</th></tr></thead>
            <tbody>
                @forelse($projectsAtRisk as $project)
                    <tr>
                        <td><a href="{{ route('admin.pm.projects.show', $project) }}">{{ $project->name }}</a></td>
                        <td>{{ number_format($project->budget_hours, 1) }}</td>
                        <td>{{ number_format($project->loggedHours(), 1) }}</td>
                        <td><span class="badge score-hot">{{ $project->budgetBurnPercent() }}%</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">No projects at risk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Tasks by Status</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Status</th><th>Count</th></tr></thead>
            <tbody>
                @forelse($tasksByStatus as $row)
                    <tr>
                        <td><span class="badge badge-draft">{{ str_replace('_', ' ', $row->status) }}</span></td>
                        <td>{{ $row->count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="empty-state">No tasks yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
