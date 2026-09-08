@extends('admin.layout')

@section('title', 'Account ledgers')

@section('content')
<div class="card">
    <div class="card-header"><h2>Ledgers by account</h2></div>
    <div class="page-help">
        <p>Each wallet has its own ledger. Open Wise, Payoneer, Pakistani bank, or Stripe to see which milestones landed there.</p>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Account</th><th>Currency</th><th>Milestones in</th><th>Money in</th><th>Money out</th><th>Balance</th><th></th></tr></thead>
            <tbody>
                @forelse($accounts as $account)
                    @php
                        $in = (float) ($account->money_in ?? 0);
                        $out = (float) ($account->money_out ?? 0);
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.accounts.ledger.show', $account) }}"><strong>{{ $account->name }}</strong></a>
                            <div class="form-meta">{{ str_replace('_', ' ', $account->provider) }}</div>
                        </td>
                        <td>{{ $account->currency }}</td>
                        <td>{{ $account->milestone_payments ?? 0 }}</td>
                        <td>{{ $account->currency }} {{ number_format($in, 2) }}</td>
                        <td>{{ $account->currency }} {{ number_format($out, 2) }}</td>
                        <td><strong>{{ $account->currency }} {{ number_format($in - $out, 2) }}</strong></td>
                        <td><a href="{{ route('admin.accounts.ledger.show', $account) }}" class="btn btn-sm btn-primary">Open ledger</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">Add wallets first under Accounts → Wallets.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
