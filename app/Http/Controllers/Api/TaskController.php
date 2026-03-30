<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Events\TaskClaimed;
use App\Events\TaskReleased;
use App\Events\TaskMoved;
use App\Events\TaskCreated as TaskCreatedEvent;
use App\Events\TaskDeleted as TaskDeletedEvent;

class TaskController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $this->checkMember($request, $project);

        $tasks = $project->tasks()
                         ->with('claimedBy:id,name,avatar')
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json($tasks);
    }

    public function store(Request $request, Project $project)
    {
        $this->checkMember($request, $project);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'tag'         => 'nullable|in:bug,feat,ux,infra,data',
            'status'      => 'nullable|in:todo,doing,done',
        ]);

        $task = $project->tasks()->create($data);
        $task->load('claimedBy:id,name,avatar');
          broadcast(new TaskCreatedEvent($task))->toOthers();
        return response()->json($task, 201);
    }

    public function claim(Request $request, Task $task)
    {
        $this->checkMember($request, $task->project);

        if ($task->claimed_by) {
            return response()->json(['message' => 'Tâche déjà prise.'], 409);
        }

        $task->update(['claimed_by' => $request->user()->id]);
        $task->load('claimedBy:id,name,avatar');
         broadcast(new TaskClaimed($task))->toOthers();
        return response()->json($task);
    }

    public function release(Request $request, Task $task)
    {
        $this->checkMember($request, $task->project);

        if ($task->claimed_by !== $request->user()->id) {
            return response()->json(['message' => 'Tu ne peux libérer que tes propres tâches.'], 403);
        }

        $task->update(['claimed_by' => null]);
        $task->load('claimedBy:id,name,avatar');
        broadcast(new TaskReleased($task))->toOthers();
        return response()->json($task);
    }

    public function move(Request $request, Task $task)
    {
        $this->checkMember($request, $task->project);

        $data = $request->validate([
            'status' => 'required|in:todo,doing,done',
        ]);

        $task->update($data);
        $task->load('claimedBy:id,name,avatar');
       broadcast(new TaskMoved($task))->toOthers();
        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        $this->checkMember($request, $task->project);
        $taskId = $task->id;
        $groupId = $task->project->group_id;
        $task->delete();
        broadcast(new TaskDeletedEvent($taskId, $groupId))->toOthers();
        return response()->json(['message' => 'Tâche supprimée.']);
    }

    private function checkMember($request, $project)
    {
        $user = $request->user();
        if (! $user) abort(401, 'Non authentifié.');

        $isMember = $project->group
                            ->members()
                            ->where('user_id', $user->id)
                            ->exists();
        if (! $isMember) abort(403, 'Accès refusé.');
    }
    public function update(Request $request, Task $task)
{
    $this->checkMember($request, $task->project);

    $data = $request->validate([
        'title'       => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'tag'         => 'nullable|in:bug,feat,ux,infra,data',
    ]);

    $task->update($data);
    $task->load('claimedBy:id,name,avatar');

    broadcast(new TaskMoved($task))->toOthers();

    return response()->json($task);
}
}