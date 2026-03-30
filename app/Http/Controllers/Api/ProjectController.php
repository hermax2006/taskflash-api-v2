<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // GET /api/groups/{group}/projects
    public function index(Request $request, Group $group)
    {
        $this->checkMember($request, $group);

        $projects = $group->projects()->withCount('tasks')->get();

        return response()->json($projects);
    }

    // POST /api/groups/{group}/projects
    public function store(Request $request, Group $group)
    {
        $this->checkMember($request, $group);

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'color' => 'nullable|string',
            'icon'  => 'nullable|string',
        ]);

        $project = $group->projects()->create($data);

        return response()->json($project, 201);
    }

    // DELETE /api/projects/{project}
    public function destroy(Request $request, Project $project)
    {
        $group = $project->group;
        $this->checkAdmin($request, $group);
        $project->delete();

        return response()->json(['message' => 'Projet supprimé.']);
    }

    private function checkMember($request, $group)
    {
        $isMember = $group->members()
                          ->where('user_id', $request->user()->id)
                          ->exists();
        if (! $isMember) abort(403, 'Accès refusé.');
    }

    private function checkAdmin($request, $group)
    {
        $member = $group->members()
                        ->where('user_id', $request->user()->id)
                        ->first();
        if (! $member || $member->pivot->role !== 'admin') abort(403, 'Admin requis.');
    }
}