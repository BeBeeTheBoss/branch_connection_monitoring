<?php

namespace App\Services;

use App\Models\MonitoredHost;
use App\Models\MonitoringIncident;
use App\Models\NotificationChannel;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    public function send(MonitoredHost $host, string $event): void
    {
        $message = $this->buildMessage($host, $event);

        foreach (NotificationChannel::where('enabled', true)->get() as $channel) {
            try {
                if ($channel->type === 'telegram') {
                    $config = $channel->configuration;
                    Http::timeout(8)
                        ->post("https://api.telegram.org/bot{$config['bot_token']}/sendMessage", [
                            'chat_id' => $config['chat_id'],
                            'text' => $message,
                            'disable_web_page_preview' => true,
                        ])
                        ->throw();
                }

                NotificationLog::create([
                    'notification_channel_id' => $channel->id,
                    'monitored_host_id' => $host->id,
                    'event' => $event,
                    'status' => 'sent',
                    'message' => $message,
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                NotificationLog::create([
                    'notification_channel_id' => $channel->id,
                    'monitored_host_id' => $host->id,
                    'event' => $event,
                    'status' => 'failed',
                    'message' => $message,
                    'error' => $e->getMessage(),
                ]);

                report($e);
            }
        }
    }

    private function buildMessage(MonitoredHost $host, string $event): string
    {
        $time = now()->format('M d, Y • h:i:s A').' (MMT)';
        $latency = $host->current_latency === null ? '—' : number_format($host->current_latency, 2).' ms';
        $packetLoss = $host->packet_loss === null ? '—' : number_format($host->packet_loss, 0).'%';

        if ($event === 'down') {
            $incident = MonitoringIncident::where('monitored_host_id', $host->id)
                ->latest('started_at')
                ->first();

            return implode("\n", [
                '🚨 BRANCH CONNECTION DOWN',
                '━━━━━━━━━━━━━━━━━━',
                "🏢 Branch: {$host->name}",
                "🌐 IP Address: {$host->ip_address}",
                '',
                '🔴 Status: DOWN',
                // "📉 Packet Loss: {$packetLoss}",
                // "🔁 Ping Attempts: {$host->retry_count}",
                '⚠️ Reason: '.$this->reasonLabel($incident?->reason),
                '',
                "🕒 Detected: {$time}",
            ]);
        }

        $incident = MonitoringIncident::where('monitored_host_id', $host->id)
            ->where('status', 'resolved')
            ->latest('recovered_at')
            ->first();
        $status = strtoupper($host->current_status);

        return implode("\n", [
            '✅ BRANCH CONNECTION RECOVERED',
            '━━━━━━━━━━━━━━━━━━',
            "🏢 Branch: {$host->name}",
            "🌐 IP Address: {$host->ip_address}",
            '',
            "🟢 Status: {$status}",
            "⚡ Latency: {$latency}",
            // "📉 Packet Loss: {$packetLoss}",
            '⏱ Downtime: '.$this->durationLabel($incident?->duration_seconds),
            '',
            "🕒 Recovered: {$time}",
        ]);
    }

    private function reasonLabel(?string $reason): string
    {
        return match ($reason) {
            'timeout' => 'ICMP response timed out',
            'unreachable' => 'Host or network unreachable',
            'binary_unavailable' => 'Ping service unavailable',
            'process_error' => 'Monitoring process error',
            default => $reason ?: 'No ICMP response received',
        };
    }

    private function durationLabel(?int $seconds): string
    {
        if ($seconds === null) {
            return 'Unknown';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;
        $parts = [];

        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }
        $parts[] = "{$remainingSeconds}s";

        return implode(' ', $parts);
    }
}
