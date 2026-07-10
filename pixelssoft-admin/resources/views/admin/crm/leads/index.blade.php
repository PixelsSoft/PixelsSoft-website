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
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Company</th><th>Contact</th><th>Status</th><th>Score</th><th>Owner</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr>
                        <td><a href="{{ route('admin.crm.leads.show', $lead) }}"><strong>{{ $lead->title }}</strong></a></td>
                        <td>{{ $lead->company?->name ?? '—' }}</td>
                        <td>{{ $lead->contact?->name ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ $lead->status }}</span></td>
                        <td><span class="badge score-{{ $lead->score }}">{{ $lead->score }}</span></td>
                        <td>{{ $lead->owner?->name ?? '—' }}</td>
                        <td class="actions">
                            @can('crm.leads.edit')
                                <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                            @can('crm.leads.convert')
                                @if($lead->status !== 'converted')
                                    <form action="{{ route('admin.crm.leads.convert', $lead) }}" method="POST" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Convert</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No leads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $leads->links() }}
</div>
@endsection
