<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Task\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {
    Route::get('tasks', [TaskController::class, 'indexAll']);
    Route::get('subjects/{subject}/tasks', [TaskController::class, 'index']);
    Route::post('subjects/{subject}/tasks', [TaskController::class, 'store']);
    Route::get('subjects/{subject}/tasks/{id}', [TaskController::class, 'show']);
    Route::put('subjects/{subject}/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('subjects/{subject}/tasks/{id}', [TaskController::class, 'destroy']);
    Route::get('subjects', [TaskController::class, 'getSubjects']);
});

Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});
