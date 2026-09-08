@extends('admin.layout')

@section('title', 'Leads')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Leads</h2>
        @can('crm.leads.create')
            <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary">+ New Lead</a>
        @endcan
    </div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search leads…',
        'filters' => [
            [
                'name' => 'status',
                'label' => 'All statuses',
                'options' => [
                    'new' => 'New',
                    'contacted' => 'Contacted',
                    'qualified' => 'Qualified',
                    'converted' => 'Converted',
                    'lost' => 'Lost',
                ],
            ],
            [
                'name' => 'score',
                'label' => 'All scores',
                'options' => [
                    'hot' => 'Hot',
                    'warm' => 'Warm',
                    'cold' => 'Cold',
                ],
            ],
        ],
    ])

    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Company</th><th>Source</th><th>Budget</th><th>Status</th><th>Score</th><th>Owner</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr>
                        <td><a href="{{ route('admin.crm.leads.show', $lead) }}"><strong>{{ $lead->title }}</strong></a></td>
                        <td>{{ $lead->company?->name ?? '—' }}</td>
                        <td>{{ $lead->acquisitionSource?->name ?? $lead->source ?? '—' }}</td>
                        <td>{{ $lead->currency ?: 'USD' }} {{ number_format((float) $lead->budget, 2) }}</td>
                        <td><span class="badge badge-draft">{{ $lead->status }}</span></td>
                        <td><span class="badge score-{{ $lead->score }}">{{ $lead->score }}</span></td>
                        <td>{{ $lead->owner?->name ?? '—' }}</td>
                        <td class="table-actions">
                            <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-sm btn-outline">View</a>
                            @can('crm.leads.edit')
                                <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                            @can('crm.leads.convert')
                                @if($lead->status !== 'converted')
                                    <form action="{{ route('admin.crm.leads.convert', $lead) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Convert</button>
                                    </form>
                                @endif
                            @endcan
                            @can('crm.leads.delete')
                                <form action="{{ route('admin.crm.leads.destroy', $lead) }}" method="POST" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">No leads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $leads->links() }}
</div>
@endsection
