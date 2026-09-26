<?php

namespace App\Console\Commands;

use App\Models\MonitoringResult;
use Illuminate\Console\Command;

class PruneMonitoringHistory extends Command
{
    protected $signature = 'monitoring:prune {--days=}';

    protected $description = 'Delete monitoring samples beyond the retention period';

    public function handle(): int
    {
        $days = max(1, (int) ($this->option('days') ?: config('monitoring.history_retention_days')));
        $deleted = MonitoringResult::where('checked_at', '<', now()->subDays($days))->delete();
        $this->info("Deleted {$deleted} old samples.");

        return self::SUCCESS;
    }
}
