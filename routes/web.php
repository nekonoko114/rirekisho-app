<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Resume routes: guests can create/store/show; index/edit/update/destroy require admin.
Route::get('resumes/create', [ResumeController::class, 'create'])->name('resumes.create');
Route::post('resumes', [ResumeController::class, 'store'])->name('resumes.store');
Route::get('resumes/{resume}', [ResumeController::class, 'show'])->name('resumes.show');
// PDF export (uses server-side generator if installed)
Route::get('resumes/{resume}/pdf', [ResumeController::class, 'pdf'])->name('resumes.pdf');

// Debug routes removed. Use authenticated admin flows for testing instead.

// Authenticated users can manage their own resumes; admins can manage all.
// Apply the admin middleware class directly to avoid needing a Kernel alias.
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('resumes', [ResumeController::class, 'index'])->name('resumes.index');
    Route::get('resumes/{resume}/edit', [ResumeController::class, 'edit'])->name('resumes.edit');
    Route::match(['put','patch'], 'resumes/{resume}', [ResumeController::class, 'update'])->name('resumes.update');
    Route::delete('resumes/{resume}', [ResumeController::class, 'destroy'])->name('resumes.destroy');
});
