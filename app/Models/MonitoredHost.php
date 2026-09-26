<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoredHost extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'name', 'ip_address', 'description', 'enabled', 'interval_seconds', 'timeout_ms', 'latency_threshold_ms', 'retry_count', 'current_status', 'current_latency', 'packet_loss', 'consecutive_failures', 'last_checked_at', 'last_active_at', 'last_down_at'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'current_latency' => 'float', 'packet_loss' => 'float', 'last_checked_at' => 'datetime', 'last_active_at' => 'datetime', 'last_down_at' => 'datetime'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MonitoringGroup::class, 'group_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(MonitoringResult::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(MonitoringIncident::class);
    }
}
