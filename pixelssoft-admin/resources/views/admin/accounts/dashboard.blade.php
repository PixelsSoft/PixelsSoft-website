@extends('admin.layout')

@section('title', 'Accounts Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Invoices</div>
        <div class="stat-value">{{ $stats['invoices'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Outstanding</div>
        <div class="stat-value">${{ number_format($stats['outstanding'], 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Overdue</div>
        <div class="stat-value">{{ $stats['overdue'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Paid This Month</div>
        <div class="stat-value">${{ number_format($stats['paid_this_month'], 0) }}</div>
        <small style="color:#6b7280">{{ $stats['pending_expenses'] }} pending expenses</small>
    </div>
</div>

<div class="quick-actions">
    @can('accounts.invoices.create')
        <a href="{{ route('admin.accounts.invoices.create') }}" class="quick-action"><div class="qa-icon">+</div><div><strong>New Invoice</strong></div></a>
    @endcan
    @can('accounts.expenses.create')
        <a href="{{ route('admin.accounts.expenses.create') }}" class="quick-action"><div class="qa-icon">$</div><div><strong>Submit Expense</strong></div></a>
    @endcan
    <a href="{{ route('admin.accounts.payments.index') }}" class="quick-action"><div class="qa-icon">✓</div><div><strong>Payments</strong></div></a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Invoices</h2>
        <a href="{{ route('admin.accounts.invoices.index') }}" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Number</th><th>Company</th><th>Status</th><th>Total</th><th>Due</th></tr></thead>
            <tbody>
                @forelse($recentInvoices as $invoice)
                    <tr>
                        <td><a href="{{ route('admin.accounts.invoices.show', $invoice) }}">{{ $invoice->number }}</a></td>
                        <td>{{ $invoice->company?->name ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ $invoice->status }}</span></td>
                        <td>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        <td>{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('accounts.invoices.create')
@php $invoiceProjects = \App\Models\Pm\Project::orderBy('name')->get(['id', 'name', 'company_id']); @endphp
<div class="card">
    <div class="card-header"><h2>Create Invoice from Time</h2></div>
    <form method="POST" action="{{ route('admin.accounts.invoices.from-time') }}" style="padding:0 24px 24px">
        @csrf
        <p style="margin-bottom:16px;color:#6b7280;font-size:14px">Generate a draft invoice from approved billable time entries for a project.</p>
        <div class="form-grid">
            <div class="form-group">
                <label>Project *</label>
                <select name="project_id" required>
                    <option value="">Select project…</option>
                    @foreach($invoiceProjects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Hourly Rate *</label>
                <input type="number" step="0.01" min="0" name="hourly_rate" value="{{ old('hourly_rate', '100') }}" required>
            </div>
        </div>
        <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
            <button type="submit" class="btn btn-primary">Create Invoice</button>
        </div>
    </form>
</div>
@endcan

<div class="card">
    <div class="card-header">
        <h2>Pending Expenses</h2>
        <a href="{{ route('admin.accounts.expenses.index') }}" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Submitter</th><th>Category</th><th>Amount</th><th>Vendor</th></tr></thead>
            <tbody>
                @forelse($pendingExpenses as $expense)
                    <tr>
                        <td>{{ $expense->date->format('M d, Y') }}</td>
                        <td>{{ $expense->submitter?->name ?? '—' }}</td>
                        <td>{{ $expense->category?->name ?? '—' }}</td>
                        <td>${{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->vendor ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No pending expenses.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
