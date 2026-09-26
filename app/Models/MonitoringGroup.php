<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'color'];

    public function hosts(): HasMany
    {
        return $this->hasMany(MonitoredHost::class, 'group_id');
    }
}
