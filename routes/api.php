<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\NotificationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail']);

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $frontend = env('FRONTEND_URL', 'http://localhost:5173');

    $user = User::find($id);

    if (!$user) {
        return redirect($frontend . '/login?verified=0&message=Usuario%20no%20encontrado');
    }

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return redirect($frontend . '/login?verified=0&message=Enlace%20de%20verificacion%20invalido');
    }

    if (!URL::hasValidSignature($request)) {
        return redirect($frontend . '/login?verified=0&message=El%20enlace%20ha%20expirado%20o%20no%20es%20valido');
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect($frontend . '/login?verified=1');
})->middleware(['signed'])->name('verification.verify');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::middleware('verified')->group(function () {
        Route::get('/messages/{department}', [MessageController::class, 'index']);
        Route::post('/messages', [MessageController::class, 'store']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/{id}', [NotificationController::class, 'show']);

        Route::apiResource('departments', DepartmentController::class);
        Route::post('/notifications', [NotificationController::class, 'store']);
        Route::post('/notify/{type}', [NotificationController::class, 'notify']);
    });
});