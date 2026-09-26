<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonitoringGroup;
use Illuminate\Http\Request;

class GroupApiController extends Controller
{
    public function index()
    {
        return MonitoringGroup::withCount('hosts')->paginate(50);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate(['name' => 'required|string|max:255|unique:monitoring_groups', 'description' => 'nullable|string|max:1000', 'color' => 'required|regex:/^#[0-9a-fA-F]{6}$/']);

        return response()->json(MonitoringGroup::create($data), 201);
    }

    public function update(Request $request, MonitoringGroup $group)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate(['name' => 'required|string|max:255|unique:monitoring_groups,name,'.$group->id, 'description' => 'nullable|string|max:1000', 'color' => 'required|regex:/^#[0-9a-fA-F]{6}$/']);
        $group->update($data);

        return $group;
    }

    public function destroy(Request $request, MonitoringGroup $group)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $group->delete();

        return response()->noContent();
    }
}
