<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->string('role')->default('monitor')->index());
        Schema::create('monitoring_groups', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->text('description')->nullable();
            $t->string('color', 16)->default('#2563eb');
            $t->timestamps();
        });
        Schema::create('monitored_hosts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('group_id')->nullable()->constrained('monitoring_groups')->nullOnDelete();
            $t->string('name');
            $t->string('ip_address', 45)->unique();
            $t->text('description')->nullable();
            $t->boolean('enabled')->default(true)->index();
            $t->unsignedInteger('interval_seconds')->default(60);
            $t->unsignedInteger('timeout_ms')->default(2000);
            $t->unsignedInteger('latency_threshold_ms')->default(200);
            $t->unsignedTinyInteger('retry_count')->default(3);
            $t->string('current_status')->default('unknown')->index();
            $t->decimal('current_latency', 10, 2)->nullable();
            $t->decimal('packet_loss', 5, 2)->nullable();
            $t->unsignedInteger('consecutive_failures')->default(0);
            $t->timestamp('last_checked_at')->nullable()->index();
            $t->timestamp('last_active_at')->nullable();
            $t->timestamp('last_down_at')->nullable();
            $t->timestamps();
        });
        Schema::create('monitoring_results', function (Blueprint $t) {
            $t->id();
            $t->foreignId('monitored_host_id')->constrained()->cascadeOnDelete();
            $t->string('status')->index();
            $t->decimal('latency_ms', 10, 2)->nullable();
            $t->decimal('packet_loss', 5, 2)->default(100);
            $t->string('error_code')->nullable();
            $t->text('error_message')->nullable();
            $t->timestamp('checked_at')->index();
            $t->index(['monitored_host_id', 'checked_at']);
            $t->index(['monitored_host_id', 'status', 'checked_at'], 'results_host_status_checked_idx');
        });
        Schema::create('monitoring_incidents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('monitored_host_id')->constrained()->cascadeOnDelete();
            $t->timestamp('started_at')->index();
            $t->timestamp('recovered_at')->nullable()->index();
            $t->unsignedBigInteger('duration_seconds')->nullable();
            $t->string('reason')->nullable();
            $t->string('status')->default('open')->index();
            $t->timestamps();
            $t->index(['monitored_host_id', 'status']);
        });
        Schema::create('notification_channels', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type');
            $t->boolean('enabled')->default(true);
            $t->text('configuration');
            $t->timestamps();
        });
        Schema::create('notification_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('notification_channel_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('monitored_host_id')->nullable()->constrained()->nullOnDelete();
            $t->string('event');
            $t->string('status');
            $t->text('message');
            $t->text('error')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
        });
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
            $t->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action')->index();
            $t->string('auditable_type');
            $t->unsignedBigInteger('auditable_id')->nullable();
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->timestamps();
            $t->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_channels');
        Schema::dropIfExists('monitoring_incidents');
        Schema::dropIfExists('monitoring_results');
        Schema::dropIfExists('monitored_hosts');
        Schema::dropIfExists('monitoring_groups');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
    }
};
