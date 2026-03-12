<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\AuthenticationController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->prefix('auth')->group(function () {
    Route::post('login', [AuthenticationController::class, 'login']);
    Route::post('send-reset-code', [PasswordResetController::class, 'sendResetCode']);
    Route::post('allowed-to-send-reset-code', [PasswordResetController::class, 'allowedToSendResetCode']);
    Route::post('confirm-reset-code', [PasswordResetController::class, 'confirmResetCode']);
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);
});

Route::post('auth/logout', [AuthenticationController::class, 'logout'])->middleware('auth:admin');


Route::middleware('auth:admin', 'activeToken:admin')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('admins', AdminController::class);
    Route::apiResource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index']);
});
