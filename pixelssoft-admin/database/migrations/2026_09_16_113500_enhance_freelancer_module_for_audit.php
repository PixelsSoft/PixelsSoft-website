<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_accounts', function (Blueprint $table) {
            $table->timestamp('connected_at')->nullable()->after('is_connected');
            $table->timestamp('last_connection_test_at')->nullable()->after('connected_at');
            $table->boolean('last_connection_test_ok')->nullable()->after('last_connection_test_at');
        });

        Schema::create('freelancer_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->unique()->constrained('freelancer_accounts')->cascadeOnDelete();
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable(); // encrypted
            $table->string('oauth_redirect_uri')->nullable();
            $table->string('api_base_url')->nullable();
            $table->string('environment')->default('sandbox');
            $table->unsignedInteger('api_timeout')->default(30);
            $table->unsignedInteger('api_retries')->default(2);
            $table->string('api_status')->default('not_tested');
            $table->timestamp('api_last_request_at')->nullable();
            $table->timestamp('api_last_success_at')->nullable();
            $table->timestamp('api_last_failure_at')->nullable();
            $table->unsignedInteger('api_last_response_time_ms')->nullable();
            $table->string('api_last_error')->nullable();
            $table->json('api_rate_limit')->nullable();
            $table->string('proposal_mode')->default('template'); // template|ai|hybrid
            $table->string('ai_provider')->nullable();
            $table->text('ai_api_key')->nullable(); // encrypted
            $table->string('ai_model')->nullable();
            $table->string('ai_base_url')->nullable();
            $table->decimal('ai_temperature', 4, 2)->default(0.30);
            $table->unsignedInteger('ai_max_tokens')->default(600);
            $table->unsignedInteger('ai_timeout')->default(20);
            $table->unsignedInteger('ai_retries')->default(1);
            $table->boolean('ai_enabled')->default(false);
            $table->boolean('ai_proposal_enabled')->default(false);
            $table->boolean('ai_bid_amount_enabled')->default(false);
            $table->boolean('ai_delivery_enabled')->default(false);
            $table->boolean('ai_portfolio_enabled')->default(false);
            $table->timestamp('ai_tested_at')->nullable();
            $table->string('ai_status')->default('not_tested');
            $table->string('ai_last_error')->nullable();
            $table->text('ai_system_prompt')->nullable();
            $table->text('ai_proposal_prompt')->nullable();
            $table->string('proposal_style')->default('short');
            $table->unsignedInteger('proposal_max_characters')->default(1400);
            $table->string('ai_failure_behavior')->default('fallback_template'); // fallback_template|manual_approval|do_not_bid
            $table->boolean('fallback_template_enabled')->default(true);
            $table->unsignedInteger('max_bid_submission_seconds')->default(60);
            $table->unsignedTinyInteger('max_portfolio_links_per_bid')->default(3);
            $table->foreignId('default_strategy_id')->nullable()->constrained('freelancer_strategies')->nullOnDelete();
            $table->foreignId('default_template_id')->nullable()->constrained('freelancer_bid_templates')->nullOnDelete();
            $table->json('notification_preferences')->nullable();
            $table->timestamps();
        });

        Schema::create('freelancer_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('freelancer_category_id')->nullable()->unique();
            $table->string('name');
            $table->string('path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->index('name');
        });

        Schema::create('freelancer_portfolio_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->constrained('freelancer_accounts')->cascadeOnDelete();
            $table->string('title');
            $table->string('url');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('technologies')->nullable();
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['freelancer_account_id', 'is_active', 'priority'], 'fl_portfolio_links_active_priority_idx');
        });

        Schema::create('freelancer_portfolio_link_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_link_id')->constrained('freelancer_portfolio_links')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('freelancer_skills')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['portfolio_link_id', 'skill_id'], 'fl_portfolio_link_skill_unique');
        });

        Schema::create('freelancer_portfolio_link_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_link_id')->constrained('freelancer_portfolio_links')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('freelancer_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['portfolio_link_id', 'category_id'], 'fl_portfolio_link_category_unique');
        });

        Schema::create('freelancer_automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_account_id')->nullable()->constrained('freelancer_accounts')->nullOnDelete();
            $table->foreignId('freelancer_project_id')->nullable()->constrained('freelancer_projects')->nullOnDelete();
            $table->foreignId('freelancer_bid_id')->nullable()->constrained('freelancer_bids')->nullOnDelete();
            $table->string('stage');
            $table->string('level', 20)->default('info');
            $table->string('message');
            $table->json('context')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
            $table->index(['freelancer_project_id', 'occurred_at'], 'fl_automation_logs_project_occurred_idx');
        });

        Schema::table('freelancer_projects', function (Blueprint $table) {
            $table->timestamp('detected_at')->nullable()->after('posted_at');
            $table->timestamp('matching_started_at')->nullable()->after('detected_at');
            $table->timestamp('matching_completed_at')->nullable()->after('matching_started_at');
            $table->timestamp('portfolio_selected_at')->nullable()->after('matching_completed_at');
            $table->timestamp('ai_started_at')->nullable()->after('portfolio_selected_at');
            $table->timestamp('ai_completed_at')->nullable()->after('ai_started_at');
            $table->timestamp('bid_queued_at')->nullable()->after('ai_completed_at');
            $table->timestamp('bid_started_at')->nullable()->after('bid_queued_at');
            $table->timestamp('bid_submitted_at')->nullable()->after('bid_started_at');
            $table->unsignedInteger('target_bid_seconds')->nullable()->after('bid_submitted_at');
            $table->unsignedInteger('total_processing_time_ms')->nullable()->after('target_bid_seconds');
            $table->unsignedInteger('total_time_to_bid_ms')->nullable()->after('total_processing_time_ms');
        });

        Schema::table('freelancer_bids', function (Blueprint $table) {
            $table->timestamp('queued_at')->nullable()->after('is_dry_run');
            $table->timestamp('processing_started_at')->nullable()->after('queued_at');
            $table->unsignedInteger('ai_processing_time_ms')->nullable()->after('processing_started_at');
            $table->unsignedInteger('api_response_time_ms')->nullable()->after('ai_processing_time_ms');
            $table->unsignedInteger('queue_processing_time_ms')->nullable()->after('api_response_time_ms');
            $table->unsignedInteger('total_time_to_bid_ms')->nullable()->after('queue_processing_time_ms');
            $table->string('ai_provider')->nullable()->after('total_time_to_bid_ms');
            $table->string('ai_model')->nullable()->after('ai_provider');
            $table->string('proposal_mode')->nullable()->after('ai_model');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_bids', function (Blueprint $table) {
            $table->dropColumn([
                'queued_at',
                'processing_started_at',
                'ai_processing_time_ms',
                'api_response_time_ms',
                'queue_processing_time_ms',
                'total_time_to_bid_ms',
                'ai_provider',
                'ai_model',
                'proposal_mode',
            ]);
        });

        Schema::table('freelancer_projects', function (Blueprint $table) {
            $table->dropColumn([
                'detected_at',
                'matching_started_at',
                'matching_completed_at',
                'portfolio_selected_at',
                'ai_started_at',
                'ai_completed_at',
                'bid_queued_at',
                'bid_started_at',
                'bid_submitted_at',
                'target_bid_seconds',
                'total_processing_time_ms',
                'total_time_to_bid_ms',
            ]);
        });

        Schema::dropIfExists('freelancer_automation_logs');
        Schema::dropIfExists('freelancer_portfolio_link_category');
        Schema::dropIfExists('freelancer_portfolio_link_skill');
        Schema::dropIfExists('freelancer_portfolio_links');
        Schema::dropIfExists('freelancer_categories');
        Schema::dropIfExists('freelancer_settings');

        Schema::table('freelancer_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'connected_at',
                'last_connection_test_at',
                'last_connection_test_ok',
            ]);
        });
    }
};
