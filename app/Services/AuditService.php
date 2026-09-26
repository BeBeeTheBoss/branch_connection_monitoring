<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public static function record(string $action, Model $model, array $old = []): void
    {
        AuditLog::create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => $model::class, 'auditable_id' => $model->getKey(), 'old_values' => $old ?: null, 'new_values' => $model->getAttributes(), 'ip_address' => request()?->ip()]);
    }
}
