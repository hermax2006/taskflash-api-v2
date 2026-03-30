<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Un user appartient à plusieurs groupes (via la table pivot)
public function groups()
{
    return $this->belongsToMany(Group::class)
                ->withPivot('role', 'joined_at')
                ->withTimestamps();
}

// Les groupes dont il est propriétaire
public function ownedGroups()
{
    return $this->hasMany(Group::class, 'owner_id');
}
}