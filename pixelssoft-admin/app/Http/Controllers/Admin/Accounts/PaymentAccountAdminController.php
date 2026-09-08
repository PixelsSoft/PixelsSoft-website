<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\PaymentAccount;
use Illuminate\Http\Request;

class PaymentAccountAdminController extends Controller
{
    public function index()
    {
        $accounts = PaymentAccount::orderBy('name')->get();

        return view('admin.accounts.wallets.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = PaymentAccount::makeSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        PaymentAccount::create($data);

        return back()->with('success', 'Payment account saved.');
    }

    public function update(Request $request, PaymentAccount $paymentAccount)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $paymentAccount->update($data);

        return back()->with('success', 'Payment account updated.');
    }

    public function destroy(PaymentAccount $paymentAccount)
    {
        $paymentAccount->delete();

        return back()->with('success', 'Payment account removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'provider' => 'required|in:wise,payoneer,stripe,pakistani_bank,other',
            'currency' => 'required|string|size:3',
            'identifier' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);
    }
}
