@extends('admin.layout')

@section('title', $invoice->exists ? 'Edit Invoice' : 'New Invoice')

@section('content')
@php
    $oldItems = old('items');
    if (!is_array($oldItems) || empty($oldItems)) {
        $oldItems = $invoice->exists
            ? $invoice->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
            ])->all()
            : [['description' => '', 'quantity' => 1, 'unit_price' => '']];
    }
@endphp
<div class="card">
    <div class="card-header"><h2>{{ $invoice->exists ? 'Edit Invoice' : 'Create Invoice' }}</h2></div>
    <form method="POST" action="{{ $invoice->exists ? route('admin.accounts.invoices.update', $invoice) : route('admin.accounts.invoices.store') }}" id="invoice-form">
        @csrf
        @if($invoice->exists) @method('PUT') @endif
        <div class="form-grid">
            @if($invoice->exists)
                <div class="form-group"><label>Invoice Number</label><input type="text" value="{{ $invoice->number }}" disabled></div>
            @endif
            <div class="form-group">
                <label>Deal *</label>
                <select name="deal_id" id="deal_id" required>
                    <option value="">— Select deal —</option>
                    @foreach($deals as $deal)
                        <option
                            value="{{ $deal->id }}"
                            data-company="{{ $deal->company_id }}"
                            data-project="{{ $deal->project?->id }}"
                            @selected(old('deal_id', $invoice->deal_id) == $deal->id)
                        >{{ $deal->title }}{{ $deal->company ? ' · '.$deal->company->name : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Company</label>
                <select name="company_id" id="company_id">
                    <option value="">— From deal —</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected(old('company_id', $invoice->company_id) == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="project_id" id="project_id" value="{{ old('project_id', $invoice->project_id) }}">
            <div class="form-group"><label>Issue Date *</label><input type="date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></div>
            <div class="form-group"><label>Due Date</label><input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d') ?? now()->addDays(14)->format('Y-m-d')) }}"></div>
            <div class="form-group"><label>Currency</label><input type="text" name="currency" id="invoice-currency" maxlength="3" value="{{ old('currency', $invoice->currency ?? 'USD') }}"></div>
            <div class="form-group"><label>Tax</label><input type="number" step="0.01" min="0" name="tax" id="invoice-tax" value="{{ old('tax', $invoice->tax ?? 0) }}"></div>
        </div>

        <h3 class="invoice-items-title">Items</h3>
        <p class="settle-form-lead">Amount is the sum of these items, same as Stripe. Add each service or milestone as a line.</p>
        <div class="table-wrap">
            <table class="invoice-items-editor">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit price</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="invoice-items">
                    @foreach($oldItems as $index => $item)
                        <tr class="invoice-item-row">
                            <td><input type="text" name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required placeholder="e.g. Website design"></td>
                            <td><input type="number" step="0.01" min="0.01" name="items[{{ $index }}][quantity]" class="js-qty" value="{{ $item['quantity'] ?? 1 }}" required></td>
                            <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" class="js-price" value="{{ $item['unit_price'] ?? '' }}" required></td>
                            <td class="js-amount num">0.00</td>
                            <td><button type="button" class="btn btn-sm btn-outline js-remove-item">Remove</button></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="3" style="text-align:right">Subtotal</td><td id="invoice-subtotal">0.00</td><td></td></tr>
                    <tr><td colspan="3" style="text-align:right">Tax</td><td id="invoice-tax-display">0.00</td><td></td></tr>
                    <tr><td colspan="3" style="text-align:right"><strong>Amount due</strong></td><td id="invoice-total"><strong>0.00</strong></td><td></td></tr>
                </tfoot>
            </table>
        </div>
        <div style="padding:12px 0 0">
            <button type="button" class="btn btn-sm btn-outline" id="add-invoice-item">+ Add item</button>
        </div>

        <div class="form-group" style="margin-top:16px"><label>Notes</label><textarea name="notes" rows="3">{{ old('notes', $invoice->notes) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.accounts.invoices.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $invoice->exists ? 'Save Invoice' : 'Create unpaid invoice' }}</button>
        </div>
    </form>
</div>
<script>
(function () {
    var tbody = document.getElementById('invoice-items');
    var currencyInput = document.getElementById('invoice-currency');
    var taxInput = document.getElementById('invoice-tax');
    var dealSelect = document.getElementById('deal_id');
    var companySelect = document.getElementById('company_id');
    var projectInput = document.getElementById('project_id');

    function money(n) {
        return (Number(n) || 0).toFixed(2);
    }

    function recalc() {
        var subtotal = 0;
        tbody.querySelectorAll('.invoice-item-row').forEach(function (row) {
            var qty = parseFloat(row.querySelector('.js-qty').value) || 0;
            var price = parseFloat(row.querySelector('.js-price').value) || 0;
            var amount = qty * price;
            row.querySelector('.js-amount').textContent = money(amount);
            subtotal += amount;
        });
        var tax = parseFloat(taxInput.value) || 0;
        document.getElementById('invoice-subtotal').textContent = money(subtotal);
        document.getElementById('invoice-tax-display').textContent = money(tax);
        document.getElementById('invoice-total').innerHTML = '<strong>' + money(subtotal + tax) + '</strong>';
    }

    function bindRow(row) {
        row.querySelector('.js-qty').addEventListener('input', recalc);
        row.querySelector('.js-price').addEventListener('input', recalc);
        row.querySelector('.js-remove-item').addEventListener('click', function () {
            if (tbody.querySelectorAll('.invoice-item-row').length === 1) return;
            row.remove();
            reindex();
            recalc();
        });
    }

    function reindex() {
        tbody.querySelectorAll('.invoice-item-row').forEach(function (row, i) {
            row.querySelectorAll('input').forEach(function (input) {
                input.name = input.name.replace(/items\[\d+]/, 'items[' + i + ']');
            });
        });
    }

    document.getElementById('add-invoice-item').addEventListener('click', function () {
        var index = tbody.querySelectorAll('.invoice-item-row').length;
        var row = document.createElement('tr');
        row.className = 'invoice-item-row';
        row.innerHTML = '<td><input type="text" name="items[' + index + '][description]" required placeholder="e.g. Website design"></td>'
            + '<td><input type="number" step="0.01" min="0.01" name="items[' + index + '][quantity]" class="js-qty" value="1" required></td>'
            + '<td><input type="number" step="0.01" min="0" name="items[' + index + '][unit_price]" class="js-price" required></td>'
            + '<td class="js-amount num">0.00</td>'
            + '<td><button type="button" class="btn btn-sm btn-outline js-remove-item">Remove</button></td>';
        tbody.appendChild(row);
        bindRow(row);
    });

    tbody.querySelectorAll('.invoice-item-row').forEach(bindRow);
    taxInput.addEventListener('input', recalc);
    recalc();

    dealSelect.addEventListener('change', function () {
        var option = dealSelect.options[dealSelect.selectedIndex];
        if (!option || !option.value) return;
        var companyId = option.getAttribute('data-company');
        var projectId = option.getAttribute('data-project');
        if (companyId) companySelect.value = companyId;
        projectInput.value = projectId || '';
    });
})();
</script>
@endsection
