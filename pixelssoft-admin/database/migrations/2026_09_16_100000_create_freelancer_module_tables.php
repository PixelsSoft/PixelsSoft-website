<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('freelancer_user_id')->nullable()->index();
            $table->string('username')->nullable();
            $table->string('display_name')->nullable();
            $table->string('email')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('timezone')->default('Asia/Karachi');
            $table->string('profile_url')->nullable();
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->decimal('rating', 5, 2)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->text('profile_description')->nullable();
            $table->json('raw_profile')->nullable();
            $table->text('access_token')->nullable(); // encrypted
            $table->text('refresh_token')->nullable(); // encrypted
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_connected')->default(false);
            $table->boolean('automation_enabled')->default(false);
            $table->boolean('dry_run')->default(true);
            $table->string('automation_mode')->default('manual'); // manual|approval|automatic
            $table->boolean('global_paused')->default(false);
            $table->unsignedInteger('daily_bid_limit')->default(20);
            $table->unsignedInteger('hourly_bid_limit')->default(5);
            $table->unsignedInteger('monthly_bid_limit')->default(500);
            $table->unsignedInteger('bid_delay_seconds')->default(60);
            $table->unsignedInteger('min_skill_match_percent')->default(60);
            $table->unsignedInteger('max_project_age_minutes')->default(15);
            $table->unsignedInteger('max_bid_count')->nullable();
            $table->string('project_type_filter')->default('both'); // fixed|hourly|both
            $table->string('country_mode')->default('all'); // all|include|exclude
            $table->json('country_codes')->nullable();
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->decimal('hourly_rate_min', 12, 2)->nullable();
            $table->decimal('hourly_rate_max', 12, 2)->nullable();
            $table->decimal('min_client_rating', 4, 2)->nullable();
            $table->unsignedInteger('min_client_reviews')->nullable();
            $table->json('positive_keywords')->nullable();
            $table->json('negative_keywords')->nullable();
            $table->unsignedTinyInteger('min_positive_keywords')->default(0);
            $table->json('include_category_ids')->nullable();
            $table->json('exclude_category_ids')->nullable();
            $table->json('schedule')->nullable(); // [{days:[1..5], start:"09:00", end:"18:00"}]
            $table->json('score_weights')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_project_sync_at')->nullable();
            $table->timestamp('last_api_success_at')->nullable();
            $table->timestamp('last_api_error_at')->nullable();
            $table->string('last_api_error')->nullable();
            $table->timestamps();
        });

        Schema::create('freelancer_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->constrained('freelancer_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('freelancer_skill_id')->index();
            $table->string('name');
            $table->string('seo_url')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_secondary')->default(false);
            $table->boolean('automation_enabled')->default(true);
            $table->unsignedInteger('priority')->default(100);
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->unique(['freelancer_account_id', 'freelancer_skill_id'], 'fl_skills_account_skill_unique');
        });

        Schema::create('freelancer_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->constrained('freelancer_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('freelancer_portfolio_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('image_url')->nullable();
            $table->string('category')->nullable();
            $table->json('skill_ids')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('use_for_bidding')->default(true);
            $table->string('status')->default('active');
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->unique(['freelancer_account_id', 'freelancer_portfolio_id'], 'fl_portfolios_account_item_unique');
        });

        Schema::create('freelancer_strategies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->constrained('freelancer_accounts')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(10);
            $table->unsignedInteger('min_skill_match_percent')->nullable();
            $table->json('skill_ids')->nullable();
            $table->json('country_codes')->nullable();
            $table->string('country_mode')->nullable();
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->decimal('min_client_rating', 4, 2)->nullable();
            $table->unsignedInteger('max_project_age_minutes')->nullable();
            $table->unsignedInteger('max_bid_count')->nullable();
            $table->unsignedInteger('bid_delay_seconds')->nullable();
            $table->json('schedule')->nullable();
            $table->string('timezone')->nullable();
            $table->unsignedInteger('daily_limit')->nullable();
            $table->string('bid_amount_mode')->default('percent_min');
            $table->decimal('bid_fixed_amount', 12, 2)->nullable();
            $table->decimal('bid_percent', 6, 2)->nullable();
            $table->decimal('bid_min', 12, 2)->nullable();
            $table->decimal('bid_max', 12, 2)->nullable();
            $table->unsignedInteger('delivery_days')->default(7);
            $table->json('delivery_rules')->nullable();
            $table->unsignedBigInteger('template_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('freelancer_bid_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->constrained('freelancer_accounts')->cascadeOnDelete();
            $table->foreignId('strategy_id')->nullable()->constrained('freelancer_strategies')->nullOnDelete();
            $table->string('name');
            $table->text('content');
            $table->json('skill_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('freelancer_strategies', function (Blueprint $table) {
            $table->foreign('template_id')->references('id')->on('freelancer_bid_templates')->nullOnDelete();
        });

        Schema::create('freelancer_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->constrained('freelancer_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('freelancer_project_id')->index();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('project_url')->nullable();
            $table->string('project_type')->nullable(); // fixed|hourly
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('currency', 8)->nullable();
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->string('duration')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code', 8)->nullable();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->string('client_username')->nullable();
            $table->decimal('client_rating', 5, 2)->nullable();
            $table->unsignedInteger('client_reviews')->nullable();
            $table->json('required_skills')->nullable();
            $table->json('category_ids')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->nullable();
            $table->unsignedInteger('bid_count')->nullable();
            $table->decimal('average_bid', 12, 2)->nullable();
            $table->timestamp('posted_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('matched')->default(false)->index();
            $table->decimal('match_score', 6, 2)->default(0);
            $table->decimal('skill_match_score', 6, 2)->default(0);
            $table->decimal('keyword_score', 6, 2)->default(0);
            $table->boolean('country_match')->nullable();
            $table->json('match_explanation')->nullable();
            $table->string('automation_status')->default('new')->index(); // new|qualified|rejected|ignored|queued|bid
            $table->string('reject_reason')->nullable();
            $table->string('bid_status')->default('none')->index();
            $table->foreignId('matched_strategy_id')->nullable()->constrained('freelancer_strategies')->nullOnDelete();
            $table->json('suggested')->nullable(); // bid, delivery, portfolio_ids, proposal
            $table->json('raw_response')->nullable();
            $table->timestamps();
            $table->unique(['freelancer_account_id', 'freelancer_project_id'], 'fl_projects_account_project_unique');
        });

        Schema::create('freelancer_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->constrained('freelancer_accounts')->cascadeOnDelete();
            $table->foreignId('freelancer_project_id')->constrained('freelancer_projects')->cascadeOnDelete();
            $table->unsignedBigInteger('freelancer_bid_id')->nullable()->index();
            $table->foreignId('strategy_id')->nullable()->constrained('freelancer_strategies')->nullOnDelete();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 8)->nullable();
            $table->unsignedInteger('delivery_days')->nullable();
            $table->text('proposal')->nullable();
            $table->decimal('match_score', 6, 2)->nullable();
            $table->decimal('skill_match_score', 6, 2)->nullable();
            $table->decimal('keyword_score', 6, 2)->nullable();
            $table->boolean('country_match')->nullable();
            $table->json('portfolio_ids')->nullable();
            $table->string('status')->default('pending')->index();
            $table->boolean('is_dry_run')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['freelancer_account_id', 'freelancer_project_id'], 'fl_bids_account_project_unique');
        });

        Schema::create('freelancer_api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->nullable()->constrained('freelancer_accounts')->nullOnDelete();
            $table->string('endpoint');
            $table->string('method', 10);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->boolean('success')->default(false);
            $table->string('error_message')->nullable();
            $table->foreignId('related_project_id')->nullable()->constrained('freelancer_projects')->nullOnDelete();
            $table->foreignId('related_bid_id')->nullable()->constrained('freelancer_bids')->nullOnDelete();
            $table->timestamps();
            $table->index(['created_at', 'success']);
        });

        Schema::create('freelancer_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('freelancer_account_id')->nullable()->constrained('freelancer_accounts')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_audit_logs');
        Schema::dropIfExists('freelancer_api_logs');
        Schema::dropIfExists('freelancer_bids');
        Schema::dropIfExists('freelancer_projects');
        Schema::table('freelancer_strategies', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
        });
        Schema::dropIfExists('freelancer_bid_templates');
        Schema::dropIfExists('freelancer_strategies');
        Schema::dropIfExists('freelancer_portfolios');
        Schema::dropIfExists('freelancer_skills');
        Schema::dropIfExists('freelancer_accounts');
    }
};
