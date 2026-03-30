<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // GET /api/groups — mes groupes
    public function index(Request $request)
    {
        $groups = $request->user()
                          ->groups()
                          ->with('owner', 'members')
                          ->get();

        return response()->json($groups);
    }

    // POST /api/groups — créer un groupe
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'color' => 'nullable|string',
        ]);

        $group = Group::create([
            'name'     => $data['name'],
            'color'    => $data['color'] ?? '#1A56A0',
            'owner_id' => $request->user()->id,
        ]);

        // Le créateur devient automatiquement admin
        $group->members()->attach($request->user()->id, [
            'role'      => 'admin',
            'joined_at' => now(),
        ]);

        return response()->json($group->load('members'), 201);
    }

    // POST /api/groups/join — rejoindre via code
    public function join(Request $request)
    {
        $data = $request->validate([
            'invite_code' => 'required|string',
        ]);

        $group = Group::where('invite_code', strtoupper($data['invite_code']))->first();

        if (! $group) {
            return response()->json(['message' => 'Code invalide.'], 404);
        }

        // Vérifier qu'il n'est pas déjà membre
        $alreadyMember = $group->members()
                               ->where('user_id', $request->user()->id)
                               ->exists();

        if ($alreadyMember) {
            return response()->json(['message' => 'Tu es déjà membre de ce groupe.'], 409);
        }

        $group->members()->attach($request->user()->id, [
            'role'      => 'member',
            'joined_at' => now(),
        ]);

        return response()->json($group->load('members'));
    }

    // GET /api/groups/{id} — détail d'un groupe
    public function show(Request $request, Group $group)
    {
        // Vérifier que l'user est membre
        $isMember = $group->members()
                          ->where('user_id', $request->user()->id)
                          ->exists();

        if (! $isMember) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json($group->load('owner', 'members', 'projects'));
    }

    // DELETE /api/groups/{id}/leave — quitter un groupe
    public function leave(Request $request, Group $group)
    {
        if ($group->owner_id === $request->user()->id) {
            return response()->json(['message' => 'Le propriétaire ne peut pas quitter le groupe.'], 403);
        }

        $group->members()->detach($request->user()->id);

        return response()->json(['message' => 'Tu as quitté le groupe.']);
    }
}