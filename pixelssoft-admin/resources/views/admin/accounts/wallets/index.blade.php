@extends('admin.layout')

@section('title', 'Wallets')

@section('content')
<div class="card">
    <div class="card-header"><h2>Payment accounts</h2></div>
    <div class="page-help">
        <p>These are the wallets. Each one has a separate ledger under Accounts → Ledger.</p>
    </div>
    <div class="table-wrap">
        <table class="compact-table">
            <thead><tr><th>Account</th><th>Provider</th><th>Currency</th><th>Identifier</th><th>Active</th><th></th></tr></thead>
            <tbody>
                @foreach($accounts as $account)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.accounts.wallets.update', $account) }}" id="wallet-{{ $account->id }}">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $account->name }}" required>
                            </form>
                        </td>
                        <td>
                            <select name="provider" form="wallet-{{ $account->id }}">
                                @foreach(['wise' => 'Wise', 'payoneer' => 'Payoneer', 'stripe' => 'Stripe', 'pakistani_bank' => 'Pakistani bank', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected($account->provider === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" maxlength="3" name="currency" form="wallet-{{ $account->id }}" value="{{ $account->currency }}" required></td>
                        <td><input type="text" name="identifier" form="wallet-{{ $account->id }}" value="{{ $account->identifier }}"></td>
                        <td>
                            <select name="is_active" form="wallet-{{ $account->id }}">
                                <option value="1" @selected($account->is_active)>Yes</option>
                                <option value="0" @selected(!$account->is_active)>No</option>
                            </select>
                        </td>
                        <td class="table-actions">
                            <button type="submit" form="wallet-{{ $account->id }}" class="btn btn-sm btn-primary">Save</button>
                            @can('accounts.ledger.view')
                                <a href="{{ route('admin.accounts.ledger.show', $account) }}" class="btn btn-sm btn-outline">Ledger</a>
                            @endcan
                            <form action="{{ route('admin.accounts.wallets.destroy', $account) }}" method="POST" onsubmit="return confirm('Remove this account?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <form method="POST" action="{{ route('admin.accounts.wallets.store') }}" class="source-add-bar">
        @csrf
        <div class="source-add-title">Add account</div>
        <div class="source-add-fields">
            <input type="text" name="name" placeholder="Name" required>
            <select name="provider" required>
                <option value="wise">Wise</option>
                <option value="payoneer">Payoneer</option>
                <option value="stripe">Stripe</option>
                <option value="pakistani_bank">Pakistani bank</option>
                <option value="other">Other</option>
            </select>
            <input type="text" maxlength="3" name="currency" value="USD" required>
            <input type="text" name="identifier" placeholder="Email / IBAN">
            <button type="submit" class="btn btn-primary">Add</button>
        </div>
    </form>
</div>
@endsection
