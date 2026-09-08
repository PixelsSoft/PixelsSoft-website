<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Accounts\Invoice;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acc_invoices', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('notes');
        });

        Invoice::query()->whereNull('public_token')->each(function (Invoice $invoice) {
            $invoice->forceFill(['public_token' => Str::random(48)])->saveQuietly();
        });

        Schema::create('acc_stripe_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('acc_invoices')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('acc_payments')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country', 2);
            $table->string('card_brand')->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->unsignedTinyInteger('card_exp_month')->nullable();
            $table->unsignedSmallInteger('card_exp_year')->nullable();
            $table->string('card_funding')->nullable();
            $table->string('card_country', 2)->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->string('stripe_payment_method_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('status')->default('pending');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        $permission = Permission::firstOrCreate([
            'name' => 'system.stripe.manage',
            'guard_name' => 'web',
        ]);
        Role::where('name', 'super-admin')->first()?->givePermissionTo($permission);
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_stripe_payments');
        Schema::table('acc_invoices', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};
