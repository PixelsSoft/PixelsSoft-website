@extends('admin.layout')

@section('title', 'Expenses')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Expenses</h2>
        @can('accounts.expenses.create')
            <a href="{{ route('admin.accounts.expenses.create') }}" class="btn btn-primary">+ Submit Expense</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    @can('accounts.expenses.view-all')<th>Submitter</th>@endcan
                    <th>Category</th>
                    <th>Project</th>
                    <th>Amount</th>
                    <th>Vendor</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $expense->date->format('M d, Y') }}</td>
                        @can('accounts.expenses.view-all')
                            <td>{{ $expense->submitter?->name ?? '—' }}</td>
                        @endcan
                        <td>{{ $expense->category?->name ?? '—' }}</td>
                        <td>{{ $expense->project?->name ?? '—' }}</td>
                        <td>${{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->vendor ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ $expense->status }}</span></td>
                        <td class="actions">
                            @can('accounts.expenses.approve')
                                @if($expense->status === 'pending')
                                    <form action="{{ route('admin.accounts.expenses.approve', $expense) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.accounts.expenses.reject', $expense) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline">Reject</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->can('accounts.expenses.view-all') ? 8 : 7 }}" class="empty-state">No expenses yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $expenses->links() }}
</div>
@endsection
