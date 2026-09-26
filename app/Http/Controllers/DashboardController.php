<?php

namespace App\Http\Controllers;

use App\Models\MonitoredHost;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $query = MonitoredHost::query();
        $summary = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('current_status', 'active')->count(),
            'down' => (clone $query)->where('current_status', 'down')->count(),
            'unstable' => (clone $query)->where('current_status', 'unstable')->count(),
        ];
        $hosts = $query->orderBy('name')->paginate(20)->withQueryString();

        return Inertia::render('Dashboard', ['hosts' => $hosts, 'summary' => $summary]);
    }
}
