@extends('admin.layout')

@section('title', 'Accounts Reports')

@section('content')
@php $netProfit = $totalRevenue - $totalExpenses; @endphp

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">${{ number_format($totalRevenue, 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Expenses</div>
        <div class="stat-value">${{ number_format($totalExpenses, 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Net Profit</div>
        <div class="stat-value" style="color:{{ $netProfit >= 0 ? '#10b981' : '#ef4444' }}">${{ number_format($netProfit, 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Outstanding</div>
        <div class="stat-value">${{ number_format($outstanding, 0) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Accounts Receivable Aging</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Bucket</th><th>Amount</th></tr></thead>
            <tbody>
                <tr><td>Current (not yet due)</td><td>${{ number_format($aging['current'], 2) }}</td></tr>
                <tr><td>1–30 days overdue</td><td>${{ number_format($aging['1_30'], 2) }}</td></tr>
                <tr><td>31–60 days overdue</td><td>${{ number_format($aging['31_60'], 2) }}</td></tr>
                <tr><td>61+ days overdue</td><td>${{ number_format($aging['61_90'], 2) }}</td></tr>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Total Outstanding</strong></td>
                    <td><strong>${{ number_format(array_sum($aging), 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <div class="card">
        <div class="card-header"><h2>Revenue by Month</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Period</th><th>Revenue</th></tr></thead>
                <tbody>
                    @forelse($revenueByMonth as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromDate($row->year, $row->month, 1)->format('M Y') }}</td>
                            <td>${{ number_format($row->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Expenses by Category</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Category</th><th>Amount</th></tr></thead>
                <tbody>
                    @forelse($expensesByCategory as $row)
                        <tr>
                            <td>{{ $row->category?->name ?? 'Uncategorized' }}</td>
                            <td>${{ number_format($row->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No approved expenses.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>P&amp;L Summary</h2></div>
    <div class="table-wrap">
        <table>
            <tbody>
                <tr><td>Revenue (payments received)</td><td style="text-align:right">${{ number_format($totalRevenue, 2) }}</td></tr>
                <tr><td>Expenses (approved)</td><td style="text-align:right">(${{ number_format($totalExpenses, 2) }})</td></tr>
                <tr>
                    <td><strong>Net Profit / Loss</strong></td>
                    <td style="text-align:right"><strong style="color:{{ $netProfit >= 0 ? '#10b981' : '#ef4444' }}">${{ number_format($netProfit, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
