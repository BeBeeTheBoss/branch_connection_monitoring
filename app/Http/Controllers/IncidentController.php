<?php

namespace App\Http\Controllers;

use App\Models\MonitoringIncident;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncidentController extends Controller
{
    public function __invoke(Request $r)
    {
        return Inertia::render('Incidents/Index', ['incidents' => MonitoringIncident::with('host:id,name,ip_address')->when($r->status, fn ($q, $v) => $q->where('status', $v))->latest('started_at')->paginate(25)->withQueryString(), 'filter' => $r->status]);
    }
}
