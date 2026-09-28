<?php

namespace App\Jobs;

use App\Models\MonitoredHost;
use App\Models\MonitoringIncident;
use App\Models\MonitoringResult;
use App\Services\NotificationService;
use App\Services\PingService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MonitorHost implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 2;

    public int $backoff = 10;

    public int $uniqueFor = 120;

    public function uniqueId(): string
    {
        return (string) $this->hostId;
    }

    public function __construct(public int $hostId)
    {
        $this->onQueue(config('monitoring.queue'));
    }

    public function handle(PingService $ping, NotificationService $notifications): void
    {
        $host = MonitoredHost::find($this->hostId);
        if (! $host?->enabled) {
            return;
        }$lock = Cache::lock("monitor-host:{$host->id}", max(10, $host->interval_seconds));
        if (! $lock->get()) {
            return;
        }try {
            $result = $ping->check($host);
            $previous = $host->current_status;
            $status = $result->reachable ? ($result->packetLoss > 0 || $result->latencyMs > $host->latency_threshold_ms ? 'unstable' : 'active') : 'down';
            DB::transaction(function () use ($host, $result, $status, $previous, $notifications) {
                $now = now();
                MonitoringResult::create(['monitored_host_id' => $host->id, 'status' => $status, 'latency_ms' => $result->latencyMs, 'packet_loss' => $result->packetLoss, 'error_code' => $result->errorCode, 'error_message' => $result->errorMessage, 'checked_at' => $now]);
                $host->fill(['current_status' => $status, 'current_latency' => $result->latencyMs, 'packet_loss' => $result->packetLoss, 'consecutive_failures' => $status === 'down' ? $host->consecutive_failures + 1 : 0, 'last_checked_at' => $now]);
                if ($status !== 'down') {
                    $host->last_active_at = $now;
                }if ($status === 'down') {
                    $host->last_down_at = $now;
                }$host->save();
                if ($status === 'down' && $previous !== 'down') {
                    MonitoringIncident::create(['monitored_host_id' => $host->id, 'started_at' => $now, 'reason' => $result->errorCode ?: 'No ICMP response', 'status' => 'open']);
                    $notifications->send($host, 'down');
                } elseif ($status !== 'down' && $previous === 'down') {
                    $incident = MonitoringIncident::where('monitored_host_id', $host->id)->where('status', 'open')->latest('started_at')->first();
                    if ($incident) {
                        $incident->update([
                            'recovered_at' => $now,
                            'duration_seconds' => (int) floor($incident->started_at->diffInSeconds($now, true)),
                            'status' => 'resolved',
                        ]);
                    }$notifications->send($host, 'recovered');
                }
            });
        } finally {
            $lock->release();
        }
    }
}
