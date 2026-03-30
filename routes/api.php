<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);

    // Groupes
    Route::get('/groups',               [GroupController::class, 'index']);
    Route::post('/groups',              [GroupController::class, 'store']);
    Route::post('/groups/join',         [GroupController::class, 'join']);
    Route::get('/groups/{group}',       [GroupController::class, 'show']);
    Route::delete('/groups/{group}/leave', [GroupController::class, 'leave']);

    // Projets
    Route::get('/groups/{group}/projects',  [ProjectController::class, 'index']);
    Route::post('/groups/{group}/projects', [ProjectController::class, 'store']);
    Route::delete('/projects/{project}',    [ProjectController::class, 'destroy']);

    // Tâches
    Route::get('/projects/{project}/tasks',  [TaskController::class, 'index']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{task}/claim',      [TaskController::class, 'claim']);
    Route::patch('/tasks/{task}/release',    [TaskController::class, 'release']);
    Route::patch('/tasks/{task}/move',       [TaskController::class, 'move']);
    Route::delete('/tasks/{task}',           [TaskController::class, 'destroy']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);

});