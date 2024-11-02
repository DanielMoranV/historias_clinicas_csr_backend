<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JWTAuthController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [JWTAuthController::class, 'login'])->name('auth.login');
    Route::post('/logout', [JWTAuthController::class, 'logout'])->name('auth.logout');
    Route::post('/refresh', [JWTAuthController::class, 'refresh'])->name('auth.refresh');
    Route::post('/register', [JWTAuthController::class, 'register'])->name('auth.register');
});