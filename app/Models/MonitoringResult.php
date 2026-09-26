<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringResult extends Model
{
    public $timestamps = false;

    protected $fillable = ['monitored_host_id', 'status', 'latency_ms', 'packet_loss', 'error_code', 'error_message', 'checked_at'];

    protected function casts(): array
    {
        return ['latency_ms' => 'float', 'packet_loss' => 'float', 'checked_at' => 'datetime'];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(MonitoredHost::class, 'monitored_host_id');
    }
}
