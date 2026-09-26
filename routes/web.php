<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/hosts', [HostController::class, 'index'])->name('hosts.index');
    Route::get('/incidents', IncidentController::class)->name('incidents.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::middleware('admin')->group(function () {
        Route::get('/hosts/create', [HostController::class, 'create'])->name('hosts.create');
        Route::post('/hosts', [HostController::class, 'store'])->name('hosts.store');
        Route::get('/hosts/{host}/edit', [HostController::class, 'edit'])->name('hosts.edit');
        Route::put('/hosts/{host}', [HostController::class, 'update'])->name('hosts.update');
        Route::delete('/hosts/{host}', [HostController::class, 'destroy'])->name('hosts.destroy');
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::get('/hosts/{host}', [HostController::class, 'show'])->name('hosts.show');
});
require __DIR__.'/auth.php';
