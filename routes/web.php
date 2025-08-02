<?php

// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard       as AdminDashboard;
use App\Livewire\ShelterAdmin\Dashboard as ShelterDashboard;
use App\Livewire\User\Dashboard        as UserDashboard;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', UserDashboard::class)->name('dashboard');

    Route::middleware('can:admin-only')->prefix('admin')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
    });

    Route::middleware('can:shelter-admin')->prefix('shelter')->group(function () {
        Route::get('/dashboard', ShelterDashboard::class)->name('shelter.dashboard');
    });
});