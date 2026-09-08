<?php

namespace App\Services;

use App\Models\Accounts\Invoice;
use App\Models\Accounts\InvoiceItem;
use App\Models\Accounts\LedgerEntry;
use App\Models\Accounts\Payment;
use App\Models\Accounts\PaymentAccount;
use App\Models\Accounts\SalesCommission;
use App\Models\Pm\Milestone;
use App\Models\User;
use App\Notifications\MilestoneAwaitingSettlementNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class MilestoneReleaseService
{
    public function __construct(private ProjectFinanceCalculator $calculator) {}

    public function requestRelease(Milestone $milestone, array $input, User $actor): Milestone
    {
        return DB::transaction(function () use ($milestone, $input, $actor) {
            $milestone = Milestone::query()->whereKey($milestone->id)->lockForUpdate()->firstOrFail();

            if ($milestone->isReleased()) {
                throw ValidationException::withMessages([
                    'milestone' => 'This milestone has already been released.',
                ]);
            }

            $project = $milestone->project()->lockForUpdate()->firstOrFail();
            $gross = round((float) ($input['amount'] ?? $milestone->amount), 2);

            if ($gross <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Enter the milestone amount being released.',
                ]);
            }

            $releasedAt = isset($input['released_at'])
                ? \Illuminate\Support\Carbon::parse($input['released_at'])
                : now();
            $split = $this->calculator->split($gross, $project);
            $billingCurrency = $project->currency ?: 'USD';

            $milestone->update([
                'amount' => $split['gross'],
                'status' => 'awaiting_settlement',
                'released_at' => $releasedAt,
                'released_by' => $actor->id,
                'platform_fee_amount' => $split['platform_fee'],
                'sales_commission_amount' => $split['sales_commission'],
                'net_amount' => $split['net'],
                'billing_currency' => $billingCurrency,
                'portal_milestone_id' => $input['portal_milestone_id'] ?? null,
                'release_notes' => $input['release_notes'] ?? null,
            ]);

            $fresh = $milestone->fresh(['project.source', 'releasedBy']);
            $this->notifyAccounts($fresh);

            return $fresh;
        });
    }

    public function settle(Milestone $milestone, array $input, User $actor): Milestone
    {
        return DB::transaction(function () use ($milestone, $input, $actor) {
            $milestone = Milestone::query()->whereKey($milestone->id)->lockForUpdate()->firstOrFail();

            if (!$milestone->isReleased()) {
                throw ValidationException::withMessages([
                    'milestone' => 'Sales must release this milestone before Accounts can record the payment.',
                ]);
            }

            if ($milestone->isSettled()) {
                throw ValidationException::withMessages([
                    'milestone' => 'This payment has already been recorded.',
                ]);
            }

            $project = $milestone->project()->lockForUpdate()->firstOrFail();
            $project->loadMissing(['deal.lead', 'deal.acquisitionSource', 'source']);
            $account = PaymentAccount::query()->where('is_active', true)->findOrFail($input['payment_account_id']);
            $paidAt = isset($input['paid_at'])
                ? \Illuminate\Support\Carbon::parse($input['paid_at'])
                : now();

            $billingCurrency = $milestone->billing_currency ?: ($project->currency ?: 'USD');
            $receivedCurrency = strtoupper($input['received_currency'] ?? $account->currency ?: $billingCurrency);
            $netUsd = (float) $milestone->net_amount;
            $gross = (float) $milestone->amount;
            $expectedNet = $netUsd > 0 ? $netUsd : $gross;
            $receivedAmount = round((float) ($input['received_amount'] ?? $expectedNet), 2);
            $sameCurrency = strtoupper($receivedCurrency) === strtoupper($billingCurrency);

            if ($receivedAmount <= 0) {
                throw ValidationException::withMessages([
                    'received_amount' => 'Enter the amount that landed in the wallet.',
                ]);
            }

            if ($sameCurrency && $receivedAmount > $expectedNet) {
                throw ValidationException::withMessages([
                    'received_amount' => sprintf(
                        'Amount cannot be more than %s %s (expected after portal commission). Extra Wise/Payoneer cuts should lower this, not raise it.',
                        $billingCurrency,
                        number_format($expectedNet, 2)
                    ),
                ]);
            }

            if (!$sameCurrency && $expectedNet > 0) {
                $impliedRate = $receivedAmount / $expectedNet;
                if ($impliedRate > 500) {
                    throw ValidationException::withMessages([
                        'received_amount' => sprintf(
                            'Amount looks too high for %s %s net. Check the received currency and figure — you cannot record more than the milestone after commission.',
                            $billingCurrency,
                            number_format($expectedNet, 2)
                        ),
                    ]);
                }
            }

            $fxRate = $netUsd > 0 ? round($receivedAmount / $netUsd, 6) : null;
            if (!empty($input['fx_rate'])) {
                $fxRate = (float) $input['fx_rate'];
            }

            $split = [
                'gross' => (float) $milestone->amount,
                'platform_percent' => (float) $project->platform_commission_percent,
                'platform_fee' => (float) $milestone->platform_fee_amount,
                'net' => $netUsd,
                'sales_percent' => (float) $project->sales_commission_percent,
                'sales_basis' => $project->sales_commission_basis === 'net' ? 'net' : 'gross',
                'sales_commission' => (float) $milestone->sales_commission_amount,
            ];

            $processorFee = 0.0;
            if ($sameCurrency && $receivedAmount < $split['gross']) {
                $allFees = round($split['gross'] - $receivedAmount, 2);
                $processorFee = round(max(0, $allFees - $split['platform_fee']), 2);
            }

            $invoiceTotal = $sameCurrency ? $receivedAmount : $split['net'];
            $sourceName = $project->source?->name
                ?? $project->deal?->acquisitionSource?->name
                ?? 'Platform';

            $invoiceNotes = sprintf(
                'Released %s %s on %s. Portal commission %s. Expected net %s %s. Received %s %s into %s.',
                $billingCurrency,
                number_format($split['gross'], 2),
                $sourceName,
                $split['platform_fee'] > 0
                    ? sprintf('%s %s (%s%%)', $billingCurrency, number_format($split['platform_fee'], 2), rtrim(rtrim(number_format($split['platform_percent'], 2), '0'), '.'))
                    : 'none',
                $billingCurrency,
                number_format($split['net'], 2),
                $receivedCurrency,
                number_format($receivedAmount, 2),
                $account->name
            );

            $invoice = Invoice::create([
                'number' => Invoice::generateNumber(),
                'company_id' => $project->company_id,
                'project_id' => $project->id,
                'deal_id' => $project->deal_id,
                'status' => 'paid',
                'issue_date' => $paidAt->toDateString(),
                'due_date' => $paidAt->toDateString(),
                'subtotal' => $split['gross'],
                'tax' => round($split['platform_fee'] + $processorFee, 2),
                'total' => $invoiceTotal,
                'currency' => $billingCurrency,
                'notes' => $invoiceNotes,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $milestone->title,
                'quantity' => 1,
                'unit_price' => $split['gross'],
                'amount' => $split['gross'],
            ]);

            if ($split['platform_fee'] > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $sourceName . ' platform fee',
                    'quantity' => 1,
                    'unit_price' => -1 * $split['platform_fee'],
                    'amount' => -1 * $split['platform_fee'],
                ]);
            }

            if ($processorFee > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $account->name . ' / processor fee',
                    'quantity' => 1,
                    'unit_price' => -1 * $processorFee,
                    'amount' => -1 * $processorFee,
                ]);
            }

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_account_id' => $account->id,
                'project_id' => $project->id,
                'milestone_id' => $milestone->id,
                'amount' => $receivedAmount,
                'currency' => $receivedCurrency,
                'method' => $this->methodForProvider($account->provider),
                'reference' => $input['reference']
                    ?? $project->deal?->portal_contract_id
                    ?? $project->deal?->lead?->portal_contract_id,
                'paid_at' => $paidAt,
            ]);

            $salesCommission = null;
            if ($project->sales_person_id && $split['sales_commission'] > 0) {
                $salesCommission = SalesCommission::create([
                    'project_id' => $project->id,
                    'milestone_id' => $milestone->id,
                    'user_id' => $project->sales_person_id,
                    'percent' => $split['sales_percent'],
                    'basis' => $split['sales_basis'],
                    'amount' => $split['sales_commission'],
                    'currency' => $billingCurrency,
                    'status' => 'accrued',
                    'accrued_at' => $milestone->released_at ?? $paidAt,
                    'notes' => $input['notes'] ?? $milestone->release_notes,
                ]);
            }

            $common = [
                'occurred_at' => $paidAt,
                'project_id' => $project->id,
                'milestone_id' => $milestone->id,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
            ];

            LedgerEntry::create($common + [
                'type' => LedgerEntry::TYPE_GROSS_REVENUE,
                'amount' => $split['gross'],
                'currency' => $billingCurrency,
                'description' => 'Milestone gross: ' . $milestone->title,
            ]);

            if ($split['platform_fee'] > 0) {
                LedgerEntry::create($common + [
                    'type' => LedgerEntry::TYPE_PLATFORM_COMMISSION,
                    'amount' => $split['platform_fee'],
                    'currency' => $billingCurrency,
                    'description' => ($project->source?->name ?? 'Platform') . ' commission',
                ]);
            }

            if ($processorFee > 0) {
                LedgerEntry::create($common + [
                    'type' => LedgerEntry::TYPE_PROCESSOR_FEE,
                    'amount' => $processorFee,
                    'currency' => $billingCurrency,
                    'description' => $account->name . ' / processor fee',
                ]);
            }

            LedgerEntry::create($common + [
                'type' => LedgerEntry::TYPE_NET_RECEIPT,
                'payment_account_id' => $account->id,
                'amount' => $receivedAmount,
                'currency' => $receivedCurrency,
                'description' => sprintf(
                    'Received into %s (%s %s net → %s %s)',
                    $account->name,
                    $billingCurrency,
                    number_format($split['net'], 2),
                    $receivedCurrency,
                    number_format($receivedAmount, 2)
                ),
            ]);

            if ($salesCommission) {
                LedgerEntry::create($common + [
                    'type' => LedgerEntry::TYPE_SALES_COMMISSION,
                    'amount' => $split['sales_commission'],
                    'currency' => $billingCurrency,
                    'sales_commission_id' => $salesCommission->id,
                    'user_id' => $project->sales_person_id,
                    'description' => 'Sales commission accrued',
                ]);
            }

            $milestone->update([
                'status' => 'settled',
                'settled_at' => $paidAt,
                'settled_by' => $actor->id,
                'payment_account_id' => $account->id,
                'invoice_id' => $invoice->id,
                'received_currency' => $receivedCurrency,
                'received_amount' => $receivedAmount,
                'fx_rate' => $fxRate,
            ]);

            return $milestone->fresh(['invoice', 'paymentAccount', 'releasedBy']);
        });
    }

    private function notifyAccounts(Milestone $milestone): void
    {
        $recipients = User::query()
            ->permission('accounts.settlements.manage')
            ->where('status', 'active')
            ->get();

        if ($recipients->isEmpty()) {
            $recipients = User::role(['finance', 'super-admin'])->where('status', 'active')->get();
        }

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new MilestoneAwaitingSettlementNotification($milestone));
        }
    }

    private function methodForProvider(string $provider): string
    {
        return match ($provider) {
            'stripe' => 'card',
            'wise', 'payoneer', 'pakistani_bank' => 'bank_transfer',
            default => 'other',
        };
    }
}
