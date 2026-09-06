<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel');
            $table->string('destination_hash', 128)->index();
            $table->string('destination_value_masked')->nullable();
            $table->string('status')->default('subscribed');
            $table->boolean('allow_transactional')->default(true);
            $table->boolean('allow_marketing')->default(true);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->text('unsubscribe_reason')->nullable();
            $table->string('consent_source')->nullable();
            $table->timestamps();
            $table->unique(['contact_id', 'channel'], 'cs_contact_channel_unique');
        });

        Schema::create('contact_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_marketable_only')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('cached_count')->default(0);
            $table->timestamps();
        });

        Schema::create('contact_list_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->timestamp('added_at')->useCurrent();
            $table->string('source_tag')->nullable();
            $table->unique(['contact_list_id', 'contact_id']);
        });

        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('filter_rules_json');
            $table->unsignedBigInteger('cached_count')->nullable();
            $table->timestamp('cached_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('channel');
            $table->string('template_type')->default('general');
            $table->string('subject_line')->nullable();
            $table->text('content_body');
            $table->string('language_code')->default('id');
            $table->json('placeholders_json')->nullable();
            $table->boolean('has_approved_external_template')->default(false);
            $table->string('external_template_provider_id')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('channel');
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('sender_integration_connection_id')->nullable();
            $table->foreignId('message_template_id')->nullable()->constrained()->nullOnDelete();
            $table->text('template_snapshot_body')->nullable();
            $table->string('template_snapshot_subject')->nullable();
            $table->json('placeholders_values_json')->nullable();
            $table->string('audience_type')->nullable();
            $table->foreignId('segment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_list_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('projected_audience_count')->nullable();
            $table->timestamp('schedule_send_at')->nullable();
            $table->timestamp('sending_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('launched_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancel_reason')->nullable();
            $table->foreignId('parent_campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('destination');
            $table->string('channel');
            $table->string('status')->index();
            $table->text('personalized_content_text')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('opted_out_at')->nullable();
            $table->string('external_delivery_id')->nullable()->index();
            $table->text('last_error_message')->nullable();
            $table->string('job_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['campaign_id', 'destination']);
            $table->index(['contact_id', 'campaign_id']);
        });

        Schema::create('message_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_type');
            $table->string('channel');
            $table->foreignId('campaign_recipient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('destination');
            $table->string('subject')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('provider_type')->nullable();
            $table->unsignedBigInteger('integration_connection_id')->nullable();
            $table->string('outcome');
            $table->string('external_id')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->json('provider_response_json')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('correlation_id')->nullable()->index();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('provider_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider_type');
            $table->string('event_type');
            $table->string('event_id')->unique();
            $table->timestamp('occurred_at_provider')->nullable();
            $table->string('signature_valid')->nullable();
            $table->string('integration_connection_id')->nullable();
            $table->json('headers_json')->nullable();
            $table->json('payload_json')->nullable();
            $table->text('raw_payload')->nullable();
            $table->ipAddress('request_ip')->nullable();
            $table->string('outcome');
            $table->text('outcome_message')->nullable();
            $table->string('processed_job_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('automation_flows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_type');
            $table->json('trigger_config_json')->nullable();
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->unsignedInteger('total_entered_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->boolean('logging_enabled')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('automation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_flow_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('step_type');
            $table->json('config_json')->nullable();
            $table->string('label')->nullable();
            $table->unsignedInteger('delay_seconds')->default(0);
            $table->timestamps();
        });

        Schema::create('automation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_flow_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('current_step_id')->nullable()->constrained('automation_steps')->nullOnDelete();
            $table->unsignedInteger('current_step_position')->default(0);
            $table->string('status')->default('running');
            $table->timestamp('entered_at')->useCurrent();
            $table->timestamp('next_step_scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('correlation_id')->nullable()->index();
            $table->json('context_snapshot_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_runs');
        Schema::dropIfExists('automation_steps');
        Schema::dropIfExists('automation_flows');
        Schema::dropIfExists('provider_webhook_events');
        Schema::dropIfExists('message_deliveries');
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('segments');
        Schema::dropIfExists('contact_list_members');
        Schema::dropIfExists('contact_lists');
        Schema::dropIfExists('contact_subscriptions');
    }
};
