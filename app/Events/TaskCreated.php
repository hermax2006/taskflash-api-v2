<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Task $task)
    {
        $task->load('claimedBy:id,name,avatar');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('group.' . $this->task->project->group_id),
        ];
    }

    public function broadcastWith(): array
    {
        return ['task' => $this->task];
    }
    public function broadcastAs(): string
{
    return 'TaskCreated'; 
}
}