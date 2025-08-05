<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard       as AdminDashboard;
use App\Livewire\ShelterAdmin\Dashboard as ShelterDashboard;
use App\Livewire\User\Dashboard        as UserDashboard;
use App\Models\Dog;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dogs/{dog}/evaluate', fn(App\Models\Dog $dog) => view('dogs.evaluate', compact('dog')))
         ->name('dogs.evaluate');
});

Route::get('/dogs/{dog}/edit', function (Dog $dog) {
    return view('dogs.edit', compact('dog'));
})->name('dogs.edit');

/* ───────── Dog “show” page ───────── */
Route::get('/dogs/{dog}', function (Dog $dog) {
    return view('dogs.show', compact('dog'));
})->name('dogs.show');   // ← fixed

/* ───────── Dog index & create (auth) ───────── */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dogs', fn () => view('dogs.index'))->name('dogs.index');
    Route::get('/dogs/create', fn () => view('dogs.create'))->name('dogs.create');
});

/* ───────── Dashboards ───────── */
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', UserDashboard::class)->name('dashboard');

    Route::middleware('can:admin-only')->prefix('admin')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
    });

    Route::middleware('can:shelter-admin')->prefix('shelter')->group(function () {
        Route::get('/dashboard', ShelterDashboard::class)->name('shelter.dashboard');
    });
});
