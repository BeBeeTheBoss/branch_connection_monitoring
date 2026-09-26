<?php

namespace Tests\Feature;

use App\Jobs\MonitorHost;
use App\Models\MonitoredHost;
use App\Models\NotificationChannel;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PingResult;
use App\Services\PingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_is_public_but_management_remains_protected(): void
    {
        MonitoredHost::create(['name' => 'Public Branch', 'ip_address' => '192.0.2.10', 'enabled' => true, 'interval_seconds' => 60, 'timeout_ms' => 1000, 'latency_threshold_ms' => 200, 'retry_count' => 3]);

        $this->get('/dashboard')->assertOk()->assertInertia(fn ($page) => $page->component('Dashboard')->has('hosts.data', 1));
        $this->get('/hosts')->assertRedirect('/login');
        $this->get('/incidents')->assertRedirect('/login');
    }

    public function test_monitor_user_can_view_but_cannot_create_hosts(): void
    {
        $user = User::factory()->create(['role' => 'monitor', 'email_verified_at' => now()]);
        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->post('/hosts', ['name' => 'Router'])->assertForbidden();
    }

    public function test_admin_can_register_ipv4_and_ipv6_hosts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        foreach (['10.0.0.1', '2001:db8::1'] as $i => $ip) {
            $this->actingAs($admin)->post('/hosts', ['name' => "Host {$i}", 'ip_address' => $ip, 'group_id' => null, 'description' => null, 'enabled' => true, 'interval_seconds' => 60, 'timeout_ms' => 1000, 'latency_threshold_ms' => 200, 'retry_count' => 3])->assertRedirect('/hosts');
        }$this->assertDatabaseCount('monitored_hosts', 2);
    }

    public function test_invalid_ip_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin)->post('/hosts', ['name' => 'Bad', 'ip_address' => 'example.com', 'enabled' => true, 'interval_seconds' => 60, 'timeout_ms' => 1000, 'latency_threshold_ms' => 200, 'retry_count' => 3])->assertSessionHasErrors('ip_address');
    }

    public function test_monitor_job_records_active_result(): void
    {
        $host = MonitoredHost::create(['name' => 'Router', 'ip_address' => '127.0.0.1', 'enabled' => true, 'interval_seconds' => 60, 'timeout_ms' => 1000, 'latency_threshold_ms' => 200, 'retry_count' => 3]);
        $this->mock(PingService::class, fn ($m) => $m->shouldReceive('check')->once()->andReturn(new PingResult(true, 4.2, 0)));
        app()->call([new MonitorHost($host->id), 'handle']);
        $this->assertDatabaseHas('monitoring_results', ['monitored_host_id' => $host->id, 'status' => 'active']);
        $this->assertSame('active', $host->fresh()->current_status);
    }

    public function test_first_completed_failed_cycle_creates_incident(): void
    {
        $host = MonitoredHost::create(['name' => 'Offline', 'ip_address' => '192.0.2.1', 'enabled' => true, 'interval_seconds' => 60, 'timeout_ms' => 1000, 'latency_threshold_ms' => 200, 'retry_count' => 3]);
        $this->mock(PingService::class, fn ($m) => $m->shouldReceive('check')->once()->andReturn(new PingResult(false, null, 100, 'timeout', 'Timed out')));
        app()->call([new MonitorHost($host->id), 'handle']);
        $this->assertDatabaseHas('monitoring_incidents', ['monitored_host_id' => $host->id, 'status' => 'open']);
    }

    public function test_telegram_down_alert_contains_operational_details(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $host = MonitoredHost::create(['name' => 'South Dagon', 'ip_address' => '192.168.51.241', 'enabled' => true, 'interval_seconds' => 60, 'timeout_ms' => 1000, 'latency_threshold_ms' => 200, 'retry_count' => 3, 'current_status' => 'down', 'packet_loss' => 100]);
        $host->incidents()->create(['started_at' => now(), 'reason' => 'timeout', 'status' => 'open']);
        NotificationChannel::create(['name' => 'Telegram Alerts', 'type' => 'telegram', 'enabled' => true, 'configuration' => ['bot_token' => 'test-token', 'chat_id' => '123456']]);

        app(NotificationService::class)->send($host, 'down');

        Http::assertSent(function ($request) {
            $message = $request['text'];

            return $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
                && str_contains($message, 'BRANCH CONNECTION DOWN')
                && str_contains($message, 'South Dagon')
                && str_contains($message, '192.168.51.241')
                && str_contains($message, 'Packet Loss: 100%')
                && str_contains($message, 'Ping Attempts: 3')
                && str_contains($message, 'ICMP response timed out');
        });
        $this->assertDatabaseHas('notification_logs', ['monitored_host_id' => $host->id, 'event' => 'down', 'status' => 'sent']);
    }
}
