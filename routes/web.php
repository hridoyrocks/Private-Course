<?php

use App\Http\Controllers\Admin\AccessController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\User\CourseController as UserCourseController;
use App\Http\Controllers\User\VideoController as UserVideoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.courses.index');
    }
    return redirect()->route('login');
})->name('home');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::resource('users', UserController::class)->except(['show']);
    Route::delete('users/{user}/devices/{device}', [UserController::class, 'removeDevice'])->name('users.devices.remove');
    Route::delete('users/{user}/devices', [UserController::class, 'removeAllDevices'])->name('users.devices.removeAll');

    // Courses
    Route::resource('courses', AdminCourseController::class)->except(['show']);

    // Videos
    Route::get('courses/{course}/videos/create', [VideoController::class, 'create'])->name('videos.create');
    Route::post('courses/{course}/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::get('videos/{video}/edit', [VideoController::class, 'edit'])->name('videos.edit');
    Route::put('videos/{video}', [VideoController::class, 'update'])->name('videos.update');
    Route::delete('videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
    Route::post('courses/{course}/videos/reorder', [VideoController::class, 'reorder'])->name('videos.reorder');

    // Access Control
    Route::get('access', [AccessController::class, 'index'])->name('access.index');
    Route::get('access/create', [AccessController::class, 'create'])->name('access.create');
    Route::post('access', [AccessController::class, 'store'])->name('access.store');
    Route::post('users/{user}/access', [AccessController::class, 'grantAccess'])->name('users.access.grant');
    Route::delete('users/{user}/access/{course}', [AccessController::class, 'revokeAccess'])->name('users.access.revoke');
});

// User Routes
Route::middleware(['auth', 'check.device'])->prefix('my')->name('user.')->group(function () {
    Route::get('courses', [UserCourseController::class, 'index'])->name('courses.index');
    Route::get('courses/{course}', [UserCourseController::class, 'show'])->name('courses.show');

    Route::get('videos/{video}/watch', [UserVideoController::class, 'watch'])->name('videos.watch');
    Route::get('videos/{video}/stream', [UserVideoController::class, 'getStreamUrl'])->name('videos.stream');
});

require __DIR__.'/settings.php';
