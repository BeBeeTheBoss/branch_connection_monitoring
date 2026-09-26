<?php

namespace App\Http\Controllers;

use App\Models\NotificationChannel;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function edit()
    {
        return Inertia::render('Settings/Edit', ['settings' => Setting::pluck('value', 'key'), 'telegram' => NotificationChannel::where('type', 'telegram')->first()]);
    }

    public function update(Request $r)
    {
        $data = $r->validate(['default_interval_seconds' => 'required|integer|min:10|max:86400', 'default_timeout_ms' => 'required|integer|min:100|max:30000', 'default_retry_count' => 'required|integer|min:1|max:10', 'default_latency_threshold_ms' => 'required|integer|min:1|max:30000', 'history_retention_days' => 'required|integer|min:1|max:3650', 'telegram_enabled' => 'boolean', 'telegram_bot_token' => 'nullable|string|max:255', 'telegram_chat_id' => 'nullable|string|max:255']);
        foreach (collect($data)->except(['telegram_enabled', 'telegram_bot_token', 'telegram_chat_id']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }$channel = NotificationChannel::firstOrNew(['type' => 'telegram']);
        $old = $channel->exists ? $channel->configuration : [];
        $channel->fill(['name' => 'Telegram Alerts', 'enabled' => $data['telegram_enabled'] ?? false, 'configuration' => ['bot_token' => $data['telegram_bot_token'] ?: ($old['bot_token'] ?? ''), 'chat_id' => $data['telegram_chat_id'] ?: ($old['chat_id'] ?? '')]])->save();

        return back()->with('success', 'Settings saved.');
    }
}
