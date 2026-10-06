@extends('admin.layout')

@section('title', $status === 'qualified' ? 'Qualified Projects' : 'Freelancer Projects')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
    <h1 style="margin:0;">{{ $status === 'qualified' ? 'Qualified Projects' : 'Projects' }}</h1>
    @can('freelancer.projects.manage')
    <form method="post" action="{{ route('admin.freelancer.projects.sync') }}">@csrf<button class="btn btn-primary btn-sm" type="submit">Sync Now</button></form>
    @endcan
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Budget</th>
                    <th>Skills</th>
                    <th>Country</th>
                    <th>Client</th>
                    <th>Bids</th>
                    <th>Posted</th>
                    <th>Match</th>
                    <th>Status</th>
                    <th>Strategy</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    @php $skills = collect($project->required_skills ?? [])->pluck('name')->filter()->take(3)->implode(', '); @endphp
                    <tr>
                        <td>{{ Str::limit($project->title, 50) }}</td>
                        <td>{{ $project->currency }} {{ $project->budget_min }}@if($project->budget_max)–{{ $project->budget_max }}@endif</td>
                        <td>{{ $skills ?: '—' }}</td>
                        <td>{{ $project->country ?: '—' }}</td>
                        <td>{{ $project->client_rating ?: '—' }} / {{ $project->client_reviews ?: 0 }}</td>
                        <td>{{ $project->bid_count ?? '—' }}</td>
                        <td>{{ $project->posted_at?->diffForHumans() ?: '—' }}</td>
                        <td>{{ $project->match_score }}%</td>
                        <td>
                            <span class="badge badge-draft">{{ $project->automation_status }}</span>
                            @if($project->bid_status && $project->bid_status !== 'none')
                                <span class="badge badge-draft" style="margin-left:.25rem;">{{ str_replace('_', ' ', $project->bid_status) }}</span>
                            @endif
                        </td>
                        <td>{{ $project->strategy?->name ?: '—' }}</td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.freelancer.projects.show', $project) }}" class="btn btn-sm btn-outline">View</a>
                            @can('freelancer.projects.manage')
                                @if($project->automation_status === 'qualified')
                                    @if($project->bid_status === 'approval_required')
                                        <form method="post" action="{{ route('admin.freelancer.projects.approve', $project) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-primary" type="submit">Approve Bid</button></form>
                                    @else
                                        <form method="post" action="{{ route('admin.freelancer.projects.bid', $project) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-outline" type="submit">Bid Now</button></form>
                                    @endif
                                @endif
                                <form method="post" action="{{ route('admin.freelancer.projects.reject', $project) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-outline" type="submit">Reject</button></form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="empty-state">No projects found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem;">{{ $projects->links() }}</div>
</div>
@endsection
