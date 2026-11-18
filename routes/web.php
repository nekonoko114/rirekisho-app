<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\Admin\CvModerationController;
use App\Http\Controllers\Admin\ResumeModerationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CvController;
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

    // Authenticated users can edit and delete their own resumes (controlled by Policy)
    Route::get('resumes/{resume}/edit', [ResumeController::class, 'edit'])->name('resumes.edit');
    Route::match(['put', 'patch'], 'resumes/{resume}', [ResumeController::class, 'update'])->name('resumes.update');
    Route::delete('resumes/{resume}', [ResumeController::class, 'destroy'])->name('resumes.destroy');

    // Authenticated users can edit and delete their own cvs
    Route::get('cvs/{cv}/edit', [CvController::class, 'edit'])->name('cvs.edit');
    Route::match(['put', 'patch'], 'cvs/{cv}', [CvController::class, 'update'])->name('cvs.update');
    Route::delete('cvs/{cv}', [CvController::class, 'destroy'])->name('cvs.destroy');
    Route::post('cvs/{cv}/revoke-public', [CvController::class, 'revokePublic'])->name('cvs.revoke_public');
});

require __DIR__.'/auth.php';

// Resume routes: guests can create/store/show
Route::get('resumes/create', [ResumeController::class, 'create'])->name('resumes.create');
Route::post('resumes', [ResumeController::class, 'store'])->middleware('throttle:20,1')->name('resumes.store');
// Register the export route before the `{resume}` parameter route so the literal
// path `resumes/export` does not get captured by the `{resume}` binding.
Route::get('resumes/export', [ResumeController::class, 'export'])->middleware('throttle:30,1')->name('resumes.export');

Route::get('resumes/{resume}', [ResumeController::class, 'show'])->middleware('throttle:60,1')->name('resumes.show');
// PDF export (uses server-side generator if installed)
Route::get('resumes/{resume}/pdf', [ResumeController::class, 'pdf'])->middleware('throttle:15,1')->name('resumes.pdf');

// CV routes: guests can create/store/show
Route::get('cvs/create', [CvController::class, 'create'])->name('cvs.create');
Route::post('cvs', [CvController::class, 'store'])->middleware('throttle:20,1')->name('cvs.store');
Route::get('cvs/{cv}', [CvController::class, 'show'])->middleware('throttle:60,1')->name('cvs.show');
Route::get('cvs/{cv}/pdf', [CvController::class, 'pdf'])->middleware('throttle:15,1')->name('cvs.pdf');

// Debug routes removed. Use authenticated admin flows for testing instead.

// Apply the admin middleware for moderation and admin dashboard
Route::middleware(['auth', 'admin'])->group(function () {

    // Admin: resume moderation
    Route::get('admin/resumes', [ResumeModerationController::class, 'index'])->name('admin.resumes.index');
    // Register the export route before the `{resume}` parameter route so the literal
    // path `admin/resumes/export` does not get captured by the `{resume}` binding.
    Route::get('admin/resumes/export', [ResumeModerationController::class, 'export'])->name('admin.resumes.export');
    Route::get('admin/resumes/{resume}', [ResumeModerationController::class, 'show'])->name('admin.resumes.show');
    Route::post('admin/resumes/{resume}/approve', [ResumeModerationController::class, 'approve'])->name('admin.resumes.approve');
    Route::post('admin/resumes/{resume}/reject', [ResumeModerationController::class, 'reject'])->name('admin.resumes.reject');
    Route::post('admin/resumes/{resume}/review', [ResumeModerationController::class, 'markReviewed'])->name('admin.resumes.mark_reviewed');

    // Admin: CV management
    Route::get('admin/cvs', [CvModerationController::class, 'index'])->name('admin.cvs.index');
    Route::get('admin/cvs/{cv}', [CvModerationController::class, 'show'])->name('admin.cvs.show');
    Route::delete('admin/cvs/{cv}', [CvModerationController::class, 'destroy'])->name('admin.cvs.destroy');

    // Admin user management: show only index and show (read-only listing)
    // Admin user management: full resource (index, create, store, show, edit, update, destroy)
    Route::resource('admin/users', UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);
});
