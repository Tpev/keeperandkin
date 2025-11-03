<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\Admin\Dashboard        as AdminDashboard;
use App\Livewire\ShelterAdmin\Dashboard as ShelterDashboard;
use App\Livewire\User\Dashboard         as UserDashboard;

// ✅ Import the correct Livewire admin components
use App\Livewire\Admin\SystemBrowser;   // <-- your existing class lives here
use App\Livewire\Admin\EvalOptions;
use App\Livewire\Admin\FormsIndex;
use App\Livewire\Admin\FormBuilder;

use App\Models\Dog;
use App\Livewire\Transfers\AcceptTransfer;
use App\Http\Controllers\TransferCancelController;
use App\Livewire\Admin\TrainingFlagsManager;
use App\Livewire\Admin\TrainingSessionsManager;


use App\Livewire\Admin\CertificationProgramsManager;
use App\Livewire\Learn\ProgramsIndex;
use App\Livewire\Learn\ProgramShow;
use App\Http\Controllers\DogPdfController;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Http\Controllers\DogEvaluationController;

Route::middleware('auth')->group(function () {
    Route::get('/dogs/{dog}/evaluations/{evaluation}', [DogEvaluationController::class, 'show'])
        ->name('dogs.evaluations.show');
});

Route::middleware(['auth']) // keep your usual middlewares
    ->group(function () {
        Route::get('/dogs/{dog}/pdf', [DogPdfController::class, 'overview'])
            ->name('dogs.pdf.overview');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('/learn', ProgramsIndex::class)->name('learn.index');
    Route::get('/learn/{slug}', ProgramShow::class)->name('learn.show');
});

Route::middleware(['auth']) // or your admin gate
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/cert-programs', CertificationProgramsManager::class)
            ->name('cert.programs');
    });

// Admin pages (adjust middleware to your app)
Route::middleware(['auth','verified'])->prefix('admin')->group(function () {
    Route::get('/training/flags', TrainingFlagsManager::class)->name('admin.training.flags');
    Route::get('/training/sessions', TrainingSessionsManager::class)->name('admin.training.sessions');
});

Route::middleware(['web'])->group(function () {
    // Accept page (guest can view but will be asked to login to proceed)
    Route::get('/transfer/accept/{transfer}', AcceptTransfer::class)->name('transfers.accept');

    // Cancel by sender (auth)
    Route::delete('/transfer/{transfer}/cancel', [TransferCancelController::class, '__invoke'])
        ->middleware(['auth','verified'])->name('transfers.cancel');
});

/* Public landing redirect */
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dogs.index')
        : redirect()->route('login');
});

/* ---------------- Admin-only ---------------- */
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin', SystemBrowser::class)->name('admin.index');
    Route::get('/admin/eval-options', EvalOptions::class)->name('admin.eval.options');

    // Phase 5 — Evaluation Forms Admin
    Route::get('/admin/forms', FormsIndex::class)->name('admin.forms.index');
    Route::get('/admin/forms/{form}', FormBuilder::class)->name('admin.forms.edit');
});

/* ---------------- Authenticated app ---------------- */
Route::middleware(['auth', 'verified'])->group(function () {
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
    Route::get('/dogs/{dog}/evaluate', fn (Dog $dog) => view('dogs.evaluate', compact('dog')))->name('dogs.evaluate');
    Route::get('/dogs/{dog}/edit', fn (Dog $dog) => view('dogs.edit', compact('dog')))->name('dogs.edit');
    Route::get('/dogs/{dog}', fn (Dog $dog) => view('dogs.show', compact('dog')))->name('dogs.show');
});
