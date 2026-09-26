<?php

namespace App\Http\Controllers;

use App\Http\Requests\HostRequest;
use App\Models\MonitoredHost;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HostController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Hosts/Index', ['hosts' => MonitoredHost::when($request->search, fn ($q, $v) => $q->where(fn ($x) => $x->where('name', 'like', "%{$v}%")->orWhere('ip_address', 'like', "%{$v}%")))->orderBy('name')->paginate(20)->withQueryString(), 'filters' => $request->only('search')]);
    }

    public function create()
    {
        return Inertia::render('Hosts/Form', ['host' => null]);
    }

    public function store(HostRequest $request)
    {
        $host = MonitoredHost::create($request->validated());
        AuditService::record('created', $host);

        return redirect()->route('hosts.index')->with('success', 'Branch added.');
    }

    public function show(MonitoredHost $host, Request $request)
    {
        $hours = match ($request->string('range')->toString()) {
            '1h' => 1, '6h' => 6, '7d' => 168, '30d' => 720, default => 24
        };
        $results = $host->results()->where('checked_at', '>=', now()->subHours($hours))->oldest('checked_at')->get();
        $total = $results->count();
        $uptime = $total ? round($results->whereIn('status', ['active', 'unstable'])->count() / $total * 100, 2) : null;

        return Inertia::render('Hosts/Show', ['host' => $host, 'results' => $results, 'incidents' => $host->incidents()->latest('started_at')->limit(25)->get(), 'uptime' => $uptime, 'range' => $request->get('range', '24h')]);
    }

    public function edit(MonitoredHost $host)
    {
        return Inertia::render('Hosts/Form', ['host' => $host]);
    }

    public function update(HostRequest $request, MonitoredHost $host)
    {
        $old = $host->getAttributes();
        $host->update($request->validated());
        AuditService::record('updated', $host, $old);

        return redirect()->route('hosts.index')->with('success', 'Branch updated.');
    }

    public function destroy(MonitoredHost $host)
    {
        $old = $host->getAttributes();
        AuditService::record('deleted', $host, $old);
        $host->delete();

        return redirect()->route('hosts.index')->with('success', 'Branch deleted.');
    }
}
