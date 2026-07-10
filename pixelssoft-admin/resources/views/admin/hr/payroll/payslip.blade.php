<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip — {{ $item->employee->employee_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; line-height: 1.5; padding: 40px; }
        .header { margin-bottom: 32px; border-bottom: 2px solid #6366f1; padding-bottom: 16px; }
        .brand { font-size: 18px; font-weight: bold; }
        .brand span { color: #6366f1; }
        .title { font-size: 22px; font-weight: bold; margin-top: 12px; }
        .period { color: #6b7280; margin-top: 4px; }
        .section { margin-bottom: 24px; }
        .section h3 { font-size: 11px; text-transform: uppercase; color: #6b7280; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .label { color: #6b7280; }
        .amount { text-align: right; }
        .net-pay { margin-top: 24px; padding: 16px; background: #f3f4f6; border-radius: 4px; }
        .net-pay .value { font-size: 20px; font-weight: bold; color: #6366f1; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">Pixels<span>Soft</span></div>
        <div class="title">PAYSLIP</div>
        <div class="period">
            {{ \Carbon\Carbon::createFromDate($item->payrollRun->year, $item->payrollRun->month, 1)->format('F Y') }}
        </div>
    </div>

    <div class="section">
        <h3>Employee</h3>
        <table>
            <tr><td class="label">Name</td><td class="amount">{{ $item->employee->user?->name ?? '—' }}</td></tr>
            <tr><td class="label">Employee Code</td><td class="amount">{{ $item->employee->employee_code }}</td></tr>
            <tr><td class="label">Position</td><td class="amount">{{ $item->employee->position ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Earnings &amp; Deductions</h3>
        <table>
            <tr><td class="label">Basic Salary</td><td class="amount">${{ number_format($item->basic_salary, 2) }}</td></tr>
            <tr><td class="label">Allowances</td><td class="amount">${{ number_format($item->allowances, 2) }}</td></tr>
            <tr><td class="label">Deductions</td><td class="amount">(${{ number_format($item->deductions, 2) }})</td></tr>
        </table>
    </div>

    <div class="net-pay">
        <div class="label" style="margin-bottom:4px">Net Pay</div>
        <div class="value">${{ number_format($item->net_pay, 2) }}</div>
    </div>

    <div class="footer">
        This is a computer-generated payslip — PixelsSoft
    </div>
</body>
</html>
