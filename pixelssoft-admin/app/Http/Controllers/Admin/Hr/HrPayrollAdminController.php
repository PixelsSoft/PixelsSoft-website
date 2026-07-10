<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use App\Models\Hr\PayrollItem;
use App\Models\Hr\PayrollRun;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class HrPayrollAdminController extends Controller
{
    public function index()
    {
        $runs = PayrollRun::with('processor')->withCount('items')->latest('year')->latest('month')->paginate(12);

        return view('admin.hr.payroll.index', compact('runs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        if (PayrollRun::where('month', $data['month'])->where('year', $data['year'])->exists()) {
            return back()->with('error', 'Payroll run already exists for this period.');
        }

        $run = PayrollRun::create(array_merge($data, ['status' => 'draft']));

        Employee::where('status', 'active')->each(function (Employee $employee) use ($run) {
            $basic = (float) ($employee->salary ?? 0);
            PayrollItem::create([
                'payroll_run_id' => $run->id,
                'employee_id' => $employee->id,
                'basic_salary' => $basic,
                'allowances' => 0,
                'deductions' => 0,
                'net_pay' => $basic,
            ]);
        });

        return redirect()->route('admin.hr.payroll.show', $run)->with('success', 'Payroll run created.');
    }

    public function show(PayrollRun $payrollRun)
    {
        $payrollRun->load(['items.employee.user']);

        return view('admin.hr.payroll.show', ['run' => $payrollRun]);
    }

    public function process(PayrollRun $payrollRun)
    {
        $payrollRun->update([
            'status' => 'processed',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Payroll processed.');
    }

    public function payslip(PayrollRun $payrollRun, PayrollItem $item)
    {
        $item->load(['employee.user', 'payrollRun']);

        return Pdf::loadView('admin.hr.payroll.payslip', compact('item'))
            ->download('payslip-' . $item->employee->employee_code . '.pdf');
    }
}
