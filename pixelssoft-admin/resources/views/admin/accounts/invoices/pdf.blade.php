<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; line-height: 1.5; padding: 40px; }
        .header { display: table; width: 100%; margin-bottom: 32px; }
        .header-left, .header-right { display: table-cell; vertical-align: top; }
        .header-right { text-align: right; }
        .brand { font-size: 22px; font-weight: bold; color: #111827; }
        .brand span { color: #6366f1; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #6366f1; margin-bottom: 8px; }
        .meta { margin-bottom: 24px; }
        .meta-row { margin-bottom: 4px; }
        .meta-label { color: #6b7280; display: inline-block; width: 100px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { background: #f3f4f6; text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
        td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .totals { width: 280px; margin-left: auto; }
        .totals td { border: none; padding: 6px 12px; }
        .totals .total-row td { font-weight: bold; font-size: 14px; border-top: 2px solid #111827; padding-top: 10px; }
        .notes { margin-top: 24px; padding: 16px; background: #f9fafb; border-radius: 4px; }
        .notes h4 { font-size: 11px; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #9ca3af; }
        .status { display: inline-block; padding: 4px 10px; background: #e5e7eb; border-radius: 4px; font-size: 11px; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="brand">Pixels<span>Soft</span></div>
            <div style="margin-top:8px;color:#6b7280;font-size:11px">Business Platform</div>
        </div>
        <div class="header-right">
            <div class="invoice-title">INVOICE</div>
            <div><strong>{{ $invoice->number }}</strong></div>
            <div class="status">{{ strtoupper($invoice->status) }}</div>
        </div>
    </div>

    <div class="meta" style="display:table;width:100%">
        <div style="display:table-cell;width:50%;vertical-align:top">
            <div style="font-weight:bold;margin-bottom:8px;color:#6b7280;font-size:11px;text-transform:uppercase">Bill To</div>
            <div style="font-size:14px;font-weight:bold">{{ $invoice->company?->name ?? '—' }}</div>
            @if($invoice->company?->email)<div>{{ $invoice->company->email }}</div>@endif
            @if($invoice->company?->phone)<div>{{ $invoice->company->phone }}</div>@endif
        </div>
        <div style="display:table-cell;width:50%;vertical-align:top;text-align:right">
            <div class="meta-row"><span class="meta-label">Issue Date</span> {{ $invoice->issue_date->format('M d, Y') }}</div>
            <div class="meta-row"><span class="meta-label">Due Date</span> {{ $invoice->due_date?->format('M d, Y') ?? '—' }}</div>
            @if($invoice->project)
                <div class="meta-row"><span class="meta-label">Project</span> {{ $invoice->project->name }}</div>
            @endif
            <div class="meta-row"><span class="meta-label">Currency</span> {{ $invoice->currency }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>Tax</td><td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td></tr>
        <tr class="total-row"><td>Total Due</td><td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td></tr>
    </table>

    @if($invoice->notes)
        <div class="notes">
            <h4>Notes</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <div class="footer">
        Thank you for your business — PixelsSoft
    </div>
</body>
</html>
