<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\NotificationController;

// autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // mensajes entre departamentos
    Route::get('/messages/{department}', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);

    // departamentos CRUD
    Route::apiResource('departments', DepartmentController::class);

    // notificaciones
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::post('/notify/{type}', [NotificationController::class, 'notify']);
});

