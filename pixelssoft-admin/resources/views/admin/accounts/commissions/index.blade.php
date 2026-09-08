@extends('admin.layout')

@section('title', 'Sales Commissions')

@section('content')
<div class="card">
    <div class="card-header"><h2>Sales commissions</h2></div>
    @include('admin.partials.list-toolbar', [
        'showSearch' => false,
        'filters' => [
            [
                'name' => 'status',
                'label' => 'All statuses',
                'options' => [
                    'accrued' => 'Owed',
                    'paid' => 'Paid',
                ],
            ],
        ],
    ])
    <div class="table-wrap">
        <table>
            <thead><tr><th>When</th><th>Salesperson</th><th>Project</th><th>Amount</th><th>%</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($commissions as $commission)
                    <tr>
                        <td>{{ $commission->accrued_at?->format('M d, Y H:i') ?? $commission->created_at->format('M d, Y') }}</td>
                        <td>{{ $commission->salesperson?->name ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.pm.projects.show', $commission->project) }}">{{ $commission->project?->code }}</a>
                            <div style="font-size:12px;color:#6b7280">{{ $commission->milestone?->title }}</div>
                        </td>
                        <td>{{ $commission->currency }} {{ number_format($commission->amount, 2) }}</td>
                        <td>{{ number_format($commission->percent, 2) }}% {{ $commission->basis }}</td>
                        <td>
                            <span class="badge {{ $commission->isPaid() ? 'badge-published' : 'badge-draft' }}">{{ $commission->status }}</span>
                            @if($commission->isPaid())
                                <div style="font-size:12px;color:#6b7280">{{ $commission->paid_at?->format('M d, Y') }} · {{ $commission->paidFromAccount?->name }}</div>
                            @endif
                        </td>
                        <td>
                            @can('accounts.commissions.pay')
                                @unless($commission->isPaid())
                                    <form method="POST" action="{{ route('admin.accounts.commissions.pay', $commission) }}" class="form-grid" style="min-width:280px">
                                        @csrf
                                        <div class="form-group">
                                            <select name="paid_from_account_id" required>
                                                <option value="">Pay from wallet…</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="datetime-local" name="paid_at" value="{{ now()->format('Y-m-d\\TH:i') }}" required>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary">Mark paid</button>
                                    </form>
                                @endunless
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No commissions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $commissions->links() }}
</div>
@endsection
