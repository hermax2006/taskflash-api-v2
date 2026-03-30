<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'status', 'tag', 'project_id', 'claimed_by'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // L'utilisateur qui a pris la tâche
    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}