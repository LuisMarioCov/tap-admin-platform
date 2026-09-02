<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:login');

    Route::post('reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::middleware('section:products')->group(function (): void {
        Route::get('products/export/{format}', [ProductController::class, 'export'])
            ->whereIn('format', ['pdf', 'xlsx'])
            ->middleware('throttle:exports');
        Route::apiResource('products', ProductController::class);
    });

    Route::middleware('section:profiles')->group(function (): void {
        Route::get('profiles/export/{format}', [ProfileController::class, 'export'])
            ->whereIn('format', ['pdf', 'xlsx'])
            ->middleware('throttle:exports');
        Route::apiResource('profiles', ProfileController::class);
    });

    Route::middleware('section:users')->group(function (): void {
        Route::get('users/export/{format}', [UserController::class, 'export'])
            ->whereIn('format', ['pdf', 'xlsx'])
            ->middleware('throttle:exports');
        Route::get('users/{user}/photo', [UserController::class, 'photo']);
        Route::apiResource('users', UserController::class);
    });
});
