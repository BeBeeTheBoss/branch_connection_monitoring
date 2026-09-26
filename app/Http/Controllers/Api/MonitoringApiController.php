<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HostRequest;
use App\Models\MonitoredHost;
use App\Models\MonitoringGroup;
use Illuminate\Http\Request;

class MonitoringApiController extends Controller
{
    public function hosts()
    {
        return MonitoredHost::with('group')->paginate(50);
    }

    public function store(HostRequest $r)
    {
        return response()->json(MonitoredHost::create($r->validated())->load('group'), 201);
    }

    public function show(MonitoredHost $host)
    {
        return $host->load('group');
    }

    public function update(HostRequest $r, MonitoredHost $host)
    {
        $host->update($r->validated());

        return $host->load('group');
    }

    public function destroy(Request $r, MonitoredHost $host)
    {
        abort_unless($r->user()->isAdmin(), 403);
        $host->delete();

        return response()->noContent();
    }

    public function history(Request $r, MonitoredHost $host)
    {
        $hours = min(720, max(1, $r->integer('hours', 24)));

        return $host->results()->where('checked_at', '>=', now()->subHours($hours))->latest('checked_at')->paginate(100);
    }

    public function incidents(MonitoredHost $host)
    {
        return $host->incidents()->latest('started_at')->paginate(50);
    }

    public function summary()
    {
        return ['total' => MonitoredHost::count(), 'active' => MonitoredHost::where('current_status', 'active')->count(), 'down' => MonitoredHost::where('current_status', 'down')->count(), 'unstable' => MonitoredHost::where('current_status', 'unstable')->count(), 'unknown' => MonitoredHost::where('current_status', 'unknown')->count()];
    }

    public function groups()
    {
        return MonitoringGroup::withCount('hosts')->get();
    }
}
