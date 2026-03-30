<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskFlashSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
        'name' => 'Utilisateur Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // 2. Créer un groupe
    $group = Group::create([
        'name' => 'Equipe Dev GLSI',
        'color' => '#1A56A0',
        'invite_code' => Str::random(8),
        'owner_id' => $user->id,
    ]);

    // 3. Créer un projet dans ce groupe
    $project = Project::create([
        'title' => 'Application Flash-Card',
        'description' => 'Projet de fin de semestre',
        'group_id' => $group->id,
    ]);

    // 4. Créer quelques tâches
    Task::create([
        'title' => 'Configurer Laravel sur WSL',
        'status' => 'done',
        'tag' => 'infra',
        'project_id' => $project->id,
        'claimed_by' => $user->id,
    ]);

    Task::create([
        'title' => 'Créer les migrations',
        'status' => 'doing',
        'tag' => 'data',
        'project_id' => $project->id,
        'claimed_by' => $user->id,
    ]);
    }
}
