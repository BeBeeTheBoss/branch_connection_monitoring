<?php

namespace Database\Seeders;

use App\Models\MonitoredHost;
use App\Models\NotificationChannel;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@example.com'], ['name' => 'Network Admin', 'role' => 'admin', 'password' => Hash::make(env('DEMO_ADMIN_PASSWORD', 'ChangeMe123!')), 'email_verified_at' => now()]);
        User::updateOrCreate(['email' => 'monitor@example.com'], ['name' => 'Monitoring User', 'role' => 'monitor', 'password' => Hash::make(env('DEMO_MONITOR_PASSWORD', 'ChangeMe123!')), 'email_verified_at' => now()]);

        $hosts = [
            ['DC-Mingalardon2', '192.168.151.241'],
            ['South Dagon', '192.168.51.241'],
            ['PRO 1 PLUS (Terminal M)', '192.168.46.241'],
            ['Clearance Sale', '192.168.11.241'],
            ['Bago', '192.168.61.241'],
            ['Mingalardon', '192.168.66.241'],
            ['Aye Tharyar', '192.168.41.241'],
            ['Hlaing Tharyar', '192.168.36.241'],
            ['DC-Myawaddy', '192.168.31.241'],
            ['WH-Mingalardon', '192.168.151.241'],
            ['Project Sales', '192.168.3.241'],
            ['Tampawady', '192.168.25.241'],
            ['Mawlamyine', '192.168.31.241'],
            ['East Dagon', '192.168.16.241'],
            ['Satsan', '192.168.11.241'],
            ['Theik Pan', '192.168.21.241'],
            ['Lanthit', '192.168.3.241'],
        ];

        DB::transaction(function () use ($hosts) {
            DB::table('jobs')->where('queue', config('monitoring.queue'))->delete();
            MonitoredHost::query()->delete();

            foreach ($hosts as [$name, $ip]) {
                MonitoredHost::create([
                    'name' => $name,
                    'ip_address' => $ip,
                    'description' => 'Production branch endpoint',
                    'enabled' => true,
                    'interval_seconds' => 60,
                    'timeout_ms' => 2000,
                    'latency_threshold_ms' => 200,
                    'retry_count' => 3,
                ]);
            }
        });

        foreach (['default_interval_seconds' => '60', 'default_timeout_ms' => '2000', 'default_retry_count' => '3', 'default_latency_threshold_ms' => '200', 'history_retention_days' => '30'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        NotificationChannel::firstOrCreate(['name' => 'Telegram Alerts'], ['type' => 'telegram', 'enabled' => false, 'configuration' => ['bot_token' => 'configure-me', 'chat_id' => 'configure-me']]);
    }
}
