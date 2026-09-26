<?php

namespace App\Console\Commands;

use App\Jobs\MonitorHost;
use App\Models\MonitoredHost;
use Illuminate\Console\Command;

class DispatchMonitoring extends Command
{
    protected $signature = 'monitoring:dispatch';

    protected $description = 'Queue checks for all enabled hosts that are due';

    public function handle(): int
    {
        $count = 0;
        MonitoredHost::query()->where('enabled', true)->select('id', 'last_checked_at', 'interval_seconds')->chunkById(500, function ($hosts) use (&$count) {
            foreach ($hosts as $host) {
                if (! $host->last_checked_at || $host->last_checked_at->addSeconds($host->interval_seconds)->isPast()) {
                    MonitorHost::dispatch($host->id);
                    $count++;
                }
            }
        });
        $this->info("Queued {$count} host checks.");

        return self::SUCCESS;
    }
}
