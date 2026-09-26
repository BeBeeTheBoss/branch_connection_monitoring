<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationChannel extends Model
{
    protected $fillable = ['name', 'type', 'enabled', 'configuration'];

    protected $hidden = ['configuration'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'configuration' => 'encrypted:array'];
    }
}
