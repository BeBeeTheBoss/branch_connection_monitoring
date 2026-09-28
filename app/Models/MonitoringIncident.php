<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringIncident extends Model
{
    protected $fillable = ['monitored_host_id', 'started_at', 'recovered_at', 'duration_seconds', 'reason', 'status'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'recovered_at' => 'datetime', 'duration_seconds' => 'integer'];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(MonitoredHost::class, 'monitored_host_id');
    }
}
