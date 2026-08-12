@extends('admin.layout')

@section('title', 'Payroll')

@section('content')
@can('hr.payroll.process')
<div class="card">
    <div class="card-header"><h2>Create Payroll Run</h2></div>
    <form method="POST" action="{{ route('admin.hr.payroll.store') }}" style="padding:0 24px 24px">
        @csrf
        @include('admin.partials.errors')
        <div class="form-grid">
            <div class="form-group">
                <label>Month *</label>
                <select name="month" required>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected(old('month', now()->month) == $m)>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group">
                <label>Year *</label>
                <input type="number" name="year" value="{{ old('year', now()->year) }}" min="2020" max="2100" required>
            </div>
        </div>
        <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
            <button type="submit" class="btn btn-primary">Create Payroll Run</button>
        </div>
    </form>
</div>
@endcan

<div class="card">
    <div class="card-header"><h2>Payroll Runs</h2></div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search by year, month, processor…',
        'filters' => [
            [
                'name' => 'status',
                'label' => 'All statuses',
                'options' => [
                    'draft' => 'Draft',
                    'processed' => 'Processed',
                ],
            ],
        ],
    ])

    <div class="table-wrap">
        <table>
            <thead><tr><th>Period</th><th>Status</th><th>Employees</th><th>Processed By</th><th>Processed At</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($runs as $run)
                    <tr>
                        <td><strong>{{ \Carbon\Carbon::createFromDate($run->year, $run->month, 1)->format('F Y') }}</strong></td>
                        <td><span class="badge badge-draft">{{ $run->status }}</span></td>
                        <td>{{ $run->items_count }}</td>
                        <td>{{ $run->processor?->name ?? '—' }}</td>
                        <td>{{ $run->processed_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="actions">
                            <a href="{{ route('admin.hr.payroll.show', $run) }}" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No payroll runs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $runs->links() }}
</div>
@endsection
