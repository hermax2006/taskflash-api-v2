<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $fillable = ['name', 'color', 'invite_code', 'owner_id'];

    // Génère un code d'invitation unique automatiquement
    protected static function booted()
    {
        static::creating(function ($group) {
            $group->invite_code = strtoupper(Str::random(8));
        });
    }

    // Le propriétaire du groupe
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Tous les membres (via table pivot)
    public function members()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    // Les projets du groupe
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}