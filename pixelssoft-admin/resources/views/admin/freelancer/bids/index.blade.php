@extends('admin.layout')

@section('title', 'Freelancer Bids')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="page-header" style="margin-bottom:1rem;"><h1 style="margin:0;">Bids</h1></div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Project</th><th>Amount</th><th>Match</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
            <tbody>
                @forelse($bids as $bid)
                    <tr>
                        <td>{{ Str::limit($bid->project?->title, 50) }}</td>
                        <td>{{ $bid->currency }} {{ $bid->amount }}</td>
                        <td>{{ $bid->match_score }}%</td>
                        <td>{{ $bid->status }}@if($bid->is_dry_run) (dry run)@endif</td>
                        <td>{{ $bid->submitted_at?->diffForHumans() ?: '—' }}</td>
                        <td>
                            @if($bid->project)
                                <a href="{{ route('admin.freelancer.projects.show', $bid->project) }}" class="btn btn-sm btn-outline">View</a>
                            @endif
                            @can('freelancer.bids.manage')
                                @if(!in_array($bid->status, ['submitted','accepted'], true) || $bid->is_dry_run)
                                <form method="post" action="{{ route('admin.freelancer.bids.cancel', $bid) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-outline" type="submit">Cancel</button></form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No bids yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem;">{{ $bids->links() }}</div>
</div>
@endsection
