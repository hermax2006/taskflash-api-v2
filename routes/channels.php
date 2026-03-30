<?php

use App\Models\Group;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return Group::find($groupId)
                ?->members()
                ->where('user_id', $user->id)
                ->exists();
});

Broadcast::channel('presence.group.{groupId}', function ($user, $groupId) {
    $isMember = Group::find($groupId)
                     ?->members()
                     ->where('user_id', $user->id)
                     ->exists();
    if ($isMember) {
        return [
            'id'     => $user->id,
            'name'   => $user->name,
            'avatar' => $user->avatar,
        ];
    }
    return false;
});