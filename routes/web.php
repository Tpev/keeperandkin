<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard        as AdminDashboard;
use App\Livewire\ShelterAdmin\Dashboard as ShelterDashboard;
use App\Livewire\User\Dashboard         as UserDashboard;
use App\Livewire\Admin\SystemBrowser;
use App\Models\Dog;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dogs.index');
    }

    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Public routes (no auth)
|--------------------------------------------------------------------------
| Jetstream/Fortify registers /login, /register, /forgot-password, etc.
| Put any public landing/marketing pages here as well.
*/

// Example public landing (optional):
// Route::view('/', 'landing')->name('landing');

/*
|--------------------------------------------------------------------------
| Admin-only (must be logged in, verified, and admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin', SystemBrowser::class)->name('admin.index');
    Route::get('/admin/eval-options', \App\Livewire\Admin\EvalOptions::class)
        ->name('admin.eval.options');
});

/*
|--------------------------------------------------------------------------
| Authenticated app routes (login required → redirects guests to /login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    /* Dashboards */
    Route::get('/dashboard', UserDashboard::class)->name('dashboard');

    Route::middleware('can:admin-only')->prefix('admin')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
    });

    Route::middleware('can:shelter-admin')->prefix('shelter')->group(function () {
        Route::get('/dashboard', ShelterDashboard::class)->name('shelter.dashboard');
    });

    /* Dogs */
    Route::get('/dogs', fn () => view('dogs.index'))->name('dogs.index');
    Route::get('/dogs/create', fn () => view('dogs.create'))->name('dogs.create');

    Route::get('/dogs/{dog}/evaluate', fn (Dog $dog) => view('dogs.evaluate', compact('dog')))
        ->name('dogs.evaluate');

    Route::get('/dogs/{dog}/edit', fn (Dog $dog) => view('dogs.edit', compact('dog')))
        ->name('dogs.edit');

    Route::get('/dogs/{dog}', fn (Dog $dog) => view('dogs.show', compact('dog')))
        ->name('dogs.show'); // protected
});
