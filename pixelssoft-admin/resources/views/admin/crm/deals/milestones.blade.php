@extends('admin.layout')

@section('title', 'Milestones')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Milestones</h2>
        <a href="{{ route('admin.crm.deals.kanban') }}" class="btn btn-sm btn-outline">Deal Pipeline</a>
    </div>
    <div class="page-help">
        <p>Won deals live here. Open a deal to add or release milestones. Production does not see this money.</p>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Deal</th>
                    <th>Company</th>
                    <th>Source</th>
                    <th>Contract</th>
                    <th>Released</th>
                    <th>Remaining</th>
                    <th>Milestones</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($deals as $deal)
                    @php
                        $project = $deal->project;
                        $finance = $project ? $project->financeSummary() : null;
                        $currency = $project?->currency ?: ($deal->currency ?: 'USD');
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.crm.deals.show', $deal) }}"><strong>{{ $deal->title }}</strong></a>
                            @if($project)
                                <div class="form-meta">{{ $project->code }}</div>
                            @endif
                        </td>
                        <td>{{ $deal->company?->name ?? '—' }}</td>
                        <td>{{ $deal->acquisitionSource?->name ?? '—' }}</td>
                        <td>{{ $currency }} {{ number_format($finance['contract'] ?? (float) $deal->value, 2) }}</td>
                        <td>{{ $currency }} {{ number_format($finance['released_gross'] ?? 0, 2) }}</td>
                        <td>{{ $currency }} {{ number_format($finance['remaining'] ?? (float) $deal->value, 2) }}</td>
                        <td>{{ $project?->milestones->count() ?? 0 }}</td>
                        <td><a href="{{ route('admin.crm.deals.show', $deal) }}" class="btn btn-sm btn-primary">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">No won deals yet. Move a deal to Won on the pipeline.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $deals->links() }}
</div>
@endsection
