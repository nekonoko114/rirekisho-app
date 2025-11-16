<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Authenticated users can view their own resumes list
    Route::get('resumes', [ResumeController::class, 'index'])->name('resumes.index');
    // Revoke public token (owner or admin)
    Route::post('resumes/{resume}/revoke-public', [ResumeController::class, 'revokePublic'])->name('resumes.revoke_public');
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
    Route::get('resumes/{resume}/edit', [ResumeController::class, 'edit'])->name('resumes.edit');
    Route::match(['put', 'patch'], 'resumes/{resume}', [ResumeController::class, 'update'])->name('resumes.update');
    Route::delete('resumes/{resume}', [ResumeController::class, 'destroy'])->name('resumes.destroy');

    // Admin: resume moderation
    Route::get('admin/resumes', [\App\Http\Controllers\Admin\ResumeModerationController::class, 'index'])->name('admin.resumes.index');
    Route::get('admin/resumes/{resume}', [\App\Http\Controllers\Admin\ResumeModerationController::class, 'show'])->name('admin.resumes.show');
    Route::post('admin/resumes/{resume}/approve', [\App\Http\Controllers\Admin\ResumeModerationController::class, 'approve'])->name('admin.resumes.approve');
    Route::post('admin/resumes/{resume}/reject', [\App\Http\Controllers\Admin\ResumeModerationController::class, 'reject'])->name('admin.resumes.reject');
    Route::post('admin/resumes/{resume}/review', [\App\Http\Controllers\Admin\ResumeModerationController::class, 'markReviewed'])->name('admin.resumes.mark_reviewed');

    // Admin user management: show only index and show (read-only listing)
    // Admin user management: full resource (index, create, store, show, edit, update, destroy)
    Route::resource('admin/users', \App\Http\Controllers\Admin\UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);
});
