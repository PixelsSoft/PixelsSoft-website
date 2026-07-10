@extends('admin.layout')

@section('title', 'Payroll — ' . \Carbon\Carbon::createFromDate($run->year, $run->month, 1)->format('F Y'))

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ \Carbon\Carbon::createFromDate($run->year, $run->month, 1)->format('F Y') }} Payroll</h2>
        <div>
            <a href="{{ route('admin.hr.payroll.index') }}" class="btn btn-sm btn-outline">Back to Payroll</a>
            @can('hr.payroll.process')
                @if($run->status === 'draft')
                    <form action="{{ route('admin.hr.payroll.process', $run) }}" method="POST" style="display:inline" onsubmit="return confirm('Process this payroll run? This cannot be undone.')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-primary">Process Payroll</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
    <div class="form-grid" style="padding:0 24px 24px">
        <div><strong>Status:</strong> <span class="badge badge-draft">{{ $run->status }}</span></div>
        <div><strong>Employees:</strong> {{ $run->items->count() }}</div>
        <div><strong>Total Net Pay:</strong> ${{ number_format($run->items->sum('net_pay'), 2) }}</div>
        @if($run->processed_at)
            <div><strong>Processed:</strong> {{ $run->processed_at->format('M d, Y H:i') }} by {{ $run->processor?->name ?? '—' }}</div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Payroll Items</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Employee</th><th>Code</th><th>Basic Salary</th><th>Allowances</th><th>Deductions</th><th>Net Pay</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($run->items as $item)
                    <tr>
                        <td>{{ $item->employee?->user?->name ?? '—' }}</td>
                        <td>{{ $item->employee?->employee_code ?? '—' }}</td>
                        <td>${{ number_format($item->basic_salary, 2) }}</td>
                        <td>${{ number_format($item->allowances, 2) }}</td>
                        <td>${{ number_format($item->deductions, 2) }}</td>
                        <td><strong>${{ number_format($item->net_pay, 2) }}</strong></td>
                        <td class="actions">
                            @if($run->status === 'processed')
                                <a href="{{ route('admin.hr.payroll.payslip', [$run, $item]) }}" class="btn btn-sm btn-outline">Download Payslip</a>
                            @else
                                <span style="font-size:12px;color:#9ca3af">Process to download</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No payroll items.</td></tr>
                @endforelse
            </tbody>
            @if($run->items->count())
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align:right"><strong>Totals</strong></td>
                        <td><strong>${{ number_format($run->items->sum('net_pay'), 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
