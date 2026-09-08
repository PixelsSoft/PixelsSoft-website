<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::table('pm_milestones', function (Blueprint $table) {
            $table->timestamp('settled_at')->nullable()->after('released_by');
            $table->foreignId('settled_by')->nullable()->after('settled_at')->constrained('users')->nullOnDelete();
            $table->string('billing_currency', 3)->default('USD')->after('net_amount');
            $table->string('received_currency', 3)->nullable()->after('billing_currency');
            $table->decimal('received_amount', 12, 2)->default(0)->after('received_currency');
            $table->decimal('fx_rate', 14, 6)->nullable()->after('received_amount');
        });

        Schema::table('acc_payments', function (Blueprint $table) {
            $table->string('currency', 3)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('acc_payments', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('pm_milestones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('settled_by');
            $table->dropColumn([
                'settled_at', 'billing_currency', 'received_currency',
                'received_amount', 'fx_rate',
            ]);
        });

        Schema::dropIfExists('notifications');
    }
};
