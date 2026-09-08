<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\PaymentAccount;
use App\Models\Pm\Milestone;
use App\Services\MilestoneReleaseService;
use Illuminate\Http\Request;

class SettlementAdminController extends Controller
{
    public function index()
    {
        $milestones = Milestone::query()
            ->with(['project.company', 'project.source', 'releasedBy'])
            ->whereNotNull('released_at')
            ->whereNull('settled_at')
            ->whereNull('invoice_id')
            ->latest('released_at')
            ->get();

        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('admin.accounts.settlements.index', compact('milestones', 'paymentAccounts'));
    }

    public function store(Request $request, Milestone $milestone, MilestoneReleaseService $service)
    {
        $data = $request->validate([
            'payment_account_id' => 'required|exists:acc_payment_accounts,id',
            'paid_at' => 'required|date',
            'received_currency' => 'required|string|size:3',
            'received_amount' => 'required|numeric|min:0.01',
            'fx_rate' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        $data['received_currency'] = strtoupper($data['received_currency']);
        $service->settle($milestone, $data, $request->user());

        $request->user()->unreadNotifications
            ->filter(fn ($notification) => (int) ($notification->data['milestone_id'] ?? 0) === (int) $milestone->id)
            ->each->markAsRead();

        return back()->with('success', 'Payment recorded against the wallet, including currency conversion.');
    }
}
