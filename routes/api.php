<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

Route::get('/messages/{department}', [MessageController::class, 'index']);
Route::post('/messages', [MessageController::class, 'store']);