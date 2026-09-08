<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('acc_ledger_entries')
            ->whereIn('type', ['gross_revenue', 'platform_commission', 'sales_commission'])
            ->update(['payment_account_id' => null]);
    }

    public function down(): void
    {
        // Cash-book cleanup is not reversible.
    }
};
