@extends('admin.layout')

@section('title', 'Invoices')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Invoices</h2>
        @can('accounts.invoices.create')
            <a href="{{ route('admin.accounts.invoices.create') }}" class="btn btn-primary">+ New Invoice</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Number</th><th>Company</th><th>Status</th><th>Issue Date</th><th>Due Date</th><th>Total</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td><a href="{{ route('admin.accounts.invoices.show', $invoice) }}"><strong>{{ $invoice->number }}</strong></a></td>
                        <td>{{ $invoice->company?->name ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ $invoice->status }}</span></td>
                        <td>{{ $invoice->issue_date->format('M d, Y') }}</td>
                        <td>{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        <td class="actions">
                            @can('accounts.invoices.edit')
                                <a href="{{ route('admin.accounts.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $invoices->links() }}
</div>
@endsection
