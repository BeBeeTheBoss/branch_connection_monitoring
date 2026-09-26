<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'ip_address' => ['required', 'ip'], 'description' => ['nullable', 'string', 'max:2000'], 'group_id' => ['nullable', 'exists:monitoring_groups,id'], 'enabled' => ['boolean'], 'interval_seconds' => ['required', 'integer', 'min:10', 'max:86400'], 'timeout_ms' => ['required', 'integer', 'min:100', 'max:30000'], 'latency_threshold_ms' => ['required', 'integer', 'min:1', 'max:30000'], 'retry_count' => ['required', 'integer', 'min:1', 'max:10']];
    }
}
