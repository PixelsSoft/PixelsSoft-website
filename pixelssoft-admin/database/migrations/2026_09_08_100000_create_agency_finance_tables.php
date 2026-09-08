<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_acquisition_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('other'); // freelance_portal, direct, referral, other
            $table->decimal('platform_commission_percent', 6, 2)->default(0);
            $table->decimal('default_sales_commission_percent', 6, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('acc_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider'); // wise, payoneer, stripe, pakistani_bank, other
            $table->string('currency', 3)->default('USD');
            $table->string('identifier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('crm_leads', function (Blueprint $table) {
            $table->foreignId('source_id')->nullable()->after('source')->constrained('crm_acquisition_sources')->nullOnDelete();
        });

        Schema::table('crm_deals', function (Blueprint $table) {
            $table->foreignId('source_id')->nullable()->after('lead_id')->constrained('crm_acquisition_sources')->nullOnDelete();
            $table->foreignId('sales_person_id')->nullable()->after('owner_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('pm_projects', function (Blueprint $table) {
            $table->foreignId('source_id')->nullable()->after('deal_id')->constrained('crm_acquisition_sources')->nullOnDelete();
            $table->foreignId('sales_person_id')->nullable()->after('manager_id')->constrained('users')->nullOnDelete();
            $table->foreignId('payment_account_id')->nullable()->after('sales_person_id')->constrained('acc_payment_accounts')->nullOnDelete();
            $table->string('currency', 3)->default('USD')->after('budget_amount');
            $table->decimal('contract_amount', 12, 2)->default(0)->after('currency');
            $table->decimal('platform_commission_percent', 6, 2)->default(0)->after('contract_amount');
            $table->decimal('sales_commission_percent', 6, 2)->default(0)->after('platform_commission_percent');
            $table->string('sales_commission_basis')->default('gross')->after('sales_commission_percent');
            $table->string('portal_contract_id')->nullable()->after('sales_commission_basis');
            $table->string('portal_url')->nullable()->after('portal_contract_id');
        });

        Schema::create('pm_project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('pm_projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role'); // developer, qa, production, sales, manager
            $table->timestamps();
            $table->unique(['project_id', 'user_id', 'role']);
        });

        Schema::table('pm_milestones', function (Blueprint $table) {
            $table->foreignId('payment_account_id')->nullable()->after('amount')->constrained('acc_payment_accounts')->nullOnDelete();
            $table->timestamp('released_at')->nullable()->after('status');
            $table->foreignId('released_by')->nullable()->after('released_at')->constrained('users')->nullOnDelete();
            $table->decimal('platform_fee_amount', 12, 2)->default(0)->after('released_by');
            $table->decimal('sales_commission_amount', 12, 2)->default(0)->after('platform_fee_amount');
            $table->decimal('net_amount', 12, 2)->default(0)->after('sales_commission_amount');
            $table->foreignId('invoice_id')->nullable()->after('net_amount')->constrained('acc_invoices')->nullOnDelete();
            $table->string('portal_milestone_id')->nullable()->after('invoice_id');
            $table->text('release_notes')->nullable()->after('portal_milestone_id');
        });

        Schema::table('acc_payments', function (Blueprint $table) {
            $table->foreignId('payment_account_id')->nullable()->after('invoice_id')->constrained('acc_payment_accounts')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->after('payment_account_id')->constrained('pm_projects')->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->after('project_id')->constrained('pm_milestones')->nullOnDelete();
        });

        Schema::create('acc_sales_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('pm_projects')->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('pm_milestones')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('percent', 6, 2)->default(0);
            $table->string('basis')->default('gross');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('accrued'); // accrued, paid
            $table->timestamp('accrued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_from_account_id')->nullable()->constrained('acc_payment_accounts')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('acc_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // gross_revenue, platform_commission, net_receipt, sales_commission
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('occurred_at');
            $table->foreignId('project_id')->nullable()->constrained('pm_projects')->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('pm_milestones')->nullOnDelete();
            $table->foreignId('payment_account_id')->nullable()->constrained('acc_payment_accounts')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('acc_invoices')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('acc_payments')->nullOnDelete();
            $table->foreignId('sales_commission_id')->nullable()->constrained('acc_sales_commissions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_ledger_entries');
        Schema::dropIfExists('acc_sales_commissions');

        Schema::table('acc_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_account_id');
            $table->dropConstrainedForeignId('project_id');
            $table->dropConstrainedForeignId('milestone_id');
        });

        Schema::table('pm_milestones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_account_id');
            $table->dropConstrainedForeignId('released_by');
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropColumn([
                'released_at', 'platform_fee_amount', 'sales_commission_amount',
                'net_amount', 'portal_milestone_id', 'release_notes',
            ]);
        });

        Schema::dropIfExists('pm_project_members');

        Schema::table('pm_projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_id');
            $table->dropConstrainedForeignId('sales_person_id');
            $table->dropConstrainedForeignId('payment_account_id');
            $table->dropColumn([
                'currency', 'contract_amount', 'platform_commission_percent',
                'sales_commission_percent', 'sales_commission_basis',
                'portal_contract_id', 'portal_url',
            ]);
        });

        Schema::table('crm_deals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_id');
            $table->dropConstrainedForeignId('sales_person_id');
        });

        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_id');
        });

        Schema::dropIfExists('acc_payment_accounts');
        Schema::dropIfExists('crm_acquisition_sources');
    }
};
