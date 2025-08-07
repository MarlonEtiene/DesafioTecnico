<?php

use App\Http\Controllers\Api\CompanyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

Route::get('companies', [CompanyController::class, 'index']);

// Rotas de Autenticação
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

// Rotas de Tarefas (protegidas por JWT)
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{id}/complete', [TaskController::class, 'complete']);
    Route::post('/tasks/export', [TaskController::class, 'export'])->name('tasks.export');
});
