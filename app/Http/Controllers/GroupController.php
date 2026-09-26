<?php

namespace App\Http\Controllers;

use App\Models\MonitoringGroup;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function index()
    {
        return Inertia::render('Groups/Index', ['groups' => MonitoringGroup::withCount(['hosts', 'hosts as active_count' => fn ($q) => $q->where('current_status', 'active'), 'hosts as down_count' => fn ($q) => $q->where('current_status', 'down'), 'hosts as unstable_count' => fn ($q) => $q->where('current_status', 'unstable')])->orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        abort_unless($r->user()->isAdmin(), 403);
        $data = $r->validate(['name' => 'required|string|max:255|unique:monitoring_groups', 'description' => 'nullable|string|max:1000', 'color' => 'required|regex:/^#[0-9a-fA-F]{6}$/']);
        $g = MonitoringGroup::create($data);
        AuditService::record('created', $g);

        return back();
    }

    public function update(Request $r, MonitoringGroup $group)
    {
        abort_unless($r->user()->isAdmin(), 403);
        $data = $r->validate(['name' => 'required|string|max:255|unique:monitoring_groups,name,'.$group->id, 'description' => 'nullable|string|max:1000', 'color' => 'required|regex:/^#[0-9a-fA-F]{6}$/']);
        $old = $group->getAttributes();
        $group->update($data);
        AuditService::record('updated', $group, $old);

        return back();
    }

    public function destroy(Request $r, MonitoringGroup $group)
    {
        abort_unless($r->user()->isAdmin(), 403);
        $old = $group->getAttributes();
        AuditService::record('deleted', $group, $old);
        $group->delete();

        return back();
    }
}
