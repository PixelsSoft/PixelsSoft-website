<?php

use App\Http\Controllers\Admin\Accounts\AccountsDashboardController;
use App\Http\Controllers\Admin\Accounts\AccountsReportController;
use App\Http\Controllers\Admin\Accounts\ExpenseAdminController;
use App\Http\Controllers\Admin\Accounts\InvoiceAdminController;
use App\Http\Controllers\Admin\Accounts\LedgerAdminController;
use App\Http\Controllers\Admin\Accounts\PaymentAccountAdminController;
use App\Http\Controllers\Admin\Accounts\PaymentAdminController;
use App\Http\Controllers\Admin\Accounts\SalesCommissionAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('accounts')->name('accounts.')->group(function () {
    Route::middleware('permission:accounts.dashboard.view')->get('/', [AccountsDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:accounts.invoices.view')->get('/invoices', [InvoiceAdminController::class, 'index'])->name('invoices.index');
    Route::middleware('permission:accounts.invoices.create')->group(function () {
        Route::get('/invoices/create', [InvoiceAdminController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceAdminController::class, 'store'])->name('invoices.store');
    });
    Route::middleware('permission:accounts.invoices.view')->get('/invoices/{invoice}', [InvoiceAdminController::class, 'show'])->name('invoices.show');
    Route::middleware('permission:accounts.invoices.edit')->group(function () {
        Route::get('/invoices/{invoice}/edit', [InvoiceAdminController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [InvoiceAdminController::class, 'update'])->name('invoices.update');
        Route::post('/invoices/{invoice}/items', [InvoiceAdminController::class, 'addItem'])->name('invoices.items.store');
        Route::delete('/invoices/{invoice}/items/{item}', [InvoiceAdminController::class, 'removeItem'])->name('invoices.items.destroy');
        Route::patch('/invoices/{invoice}/sent', [InvoiceAdminController::class, 'markSent'])->name('invoices.sent');
        Route::get('/invoices/{invoice}/pdf', [InvoiceAdminController::class, 'pdf'])->name('invoices.pdf');
    });
    Route::middleware('permission:accounts.invoices.create')->post('/invoices/from-time', [InvoiceAdminController::class, 'fromTime'])->name('invoices.from-time');
    Route::middleware('permission:accounts.invoices.delete')->delete('/invoices/{invoice}', [InvoiceAdminController::class, 'destroy'])->name('invoices.destroy');

    Route::middleware('permission:accounts.payments.view')->get('/payments', [PaymentAdminController::class, 'index'])->name('payments.index');
    Route::middleware('permission:accounts.payments.create')->post('/invoices/{invoice}/payments', [PaymentAdminController::class, 'store'])->name('payments.store');

    Route::middleware('permission:accounts.settlements.manage')->group(function () {
        Route::get('/settlements', [\App\Http\Controllers\Admin\Accounts\SettlementAdminController::class, 'index'])->name('settlements.index');
        Route::post('/settlements/{milestone}', [\App\Http\Controllers\Admin\Accounts\SettlementAdminController::class, 'store'])->name('settlements.store');
    });

    Route::middleware('permission:accounts.ledger.view')->group(function () {
        Route::get('/ledger', [LedgerAdminController::class, 'index'])->name('ledger.index');
        Route::get('/ledger/{paymentAccount}', [LedgerAdminController::class, 'show'])->name('ledger.show');
    });
    Route::middleware('permission:accounts.commissions.view')->get('/commissions', [SalesCommissionAdminController::class, 'index'])->name('commissions.index');
    Route::middleware('permission:accounts.commissions.pay')->post('/commissions/{commission}/pay', [SalesCommissionAdminController::class, 'pay'])->name('commissions.pay');

    Route::middleware('permission:accounts.payment-accounts.manage')->group(function () {
        Route::get('/wallets', [PaymentAccountAdminController::class, 'index'])->name('wallets.index');
        Route::post('/wallets', [PaymentAccountAdminController::class, 'store'])->name('wallets.store');
        Route::put('/wallets/{paymentAccount}', [PaymentAccountAdminController::class, 'update'])->name('wallets.update');
        Route::delete('/wallets/{paymentAccount}', [PaymentAccountAdminController::class, 'destroy'])->name('wallets.destroy');
    });

    Route::middleware('role_or_permission:accounts.expenses.view-own|accounts.expenses.view-all')->get('/expenses', [ExpenseAdminController::class, 'index'])->name('expenses.index');
    Route::middleware('permission:accounts.expenses.create')->group(function () {
        Route::get('/expenses/create', [ExpenseAdminController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseAdminController::class, 'store'])->name('expenses.store');
    });
    Route::middleware('permission:accounts.expenses.approve')->group(function () {
        Route::patch('/expenses/{expense}/approve', [ExpenseAdminController::class, 'approve'])->name('expenses.approve');
        Route::patch('/expenses/{expense}/reject', [ExpenseAdminController::class, 'reject'])->name('expenses.reject');
    });

    Route::middleware('permission:accounts.reports.view')->get('/reports', [AccountsReportController::class, 'index'])->name('reports.index');
});
