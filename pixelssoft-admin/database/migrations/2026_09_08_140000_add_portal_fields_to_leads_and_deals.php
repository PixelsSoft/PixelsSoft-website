<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->string('portal_contract_id')->nullable()->after('source_id');
            $table->string('portal_url')->nullable()->after('portal_contract_id');
        });

        Schema::table('crm_deals', function (Blueprint $table) {
            $table->string('portal_contract_id')->nullable()->after('source_id');
            $table->string('portal_url')->nullable()->after('portal_contract_id');
        });
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropColumn(['portal_contract_id', 'portal_url']);
        });
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->dropColumn(['portal_contract_id', 'portal_url']);
        });
    }
};
