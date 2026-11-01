<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/', function () {
        return view('today');
    })->name('today');
});

// Development Design System Preview (Local Environment Only)
Route::get('/_dev/components', function () {
    abort_unless(app()->environment(['local', 'testing']), 404);
    return view('dev.components');
})->name('dev.components');
