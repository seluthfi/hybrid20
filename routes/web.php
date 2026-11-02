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

    Route::get('/', [\App\Http\Controllers\TodayController::class, 'index'])->name('today');

    // Workout Routes
    Route::resource('workouts', \App\Http\Controllers\WorkoutController::class);

    // Daily Activities & Body Measurements
    Route::put('/daily-activities/{date}', [\App\Http\Controllers\DailyActivityController::class, 'upsert'])->name('daily-activities.upsert');
    Route::get('/body', [\App\Http\Controllers\BodyMeasurementController::class, 'index'])->name('body.index');
    Route::put('/body/{date}', [\App\Http\Controllers\BodyMeasurementController::class, 'upsert'])->name('measurements.upsert');
    Route::delete('/body/{date}', [\App\Http\Controllers\BodyMeasurementController::class, 'destroy'])->name('measurements.destroy');

    // Foods & Food Diary (PLAN.md §5, §10.8)
    Route::get('/foods/search', [\App\Http\Controllers\FoodController::class, 'search'])->name('foods.search');
    Route::resource('foods', \App\Http\Controllers\FoodController::class);
    Route::resource('food-logs', \App\Http\Controllers\FoodLogController::class);

    // Progress Photos (PLAN.md §5, §10.9, §12)
    Route::get('/body/photos/compare', [\App\Http\Controllers\ProgressPhotoController::class, 'compare'])->name('progress-photos.compare');
    Route::get('/progress-photos/{id}/image', [\App\Http\Controllers\ProgressPhotoController::class, 'image'])->name('progress-photos.image');
    Route::get('/progress-photos/{id}/thumb', [\App\Http\Controllers\ProgressPhotoController::class, 'thumb'])->name('progress-photos.thumb');
    Route::resource('progress-photos', \App\Http\Controllers\ProgressPhotoController::class)->only(['index', 'store', 'destroy']);

    // Rencana (Plan) Routes
    Route::get('/plan/{calendar_position?}', [\App\Http\Controllers\PlanController::class, 'show'])->name('plan.show');
    Route::patch('/program-weeks/{id}', [\App\Http\Controllers\PlanController::class, 'updateNotes'])->name('program-weeks.update-notes');
    Route::post('/program-weeks/{id}/repeat', [\App\Http\Controllers\PlanController::class, 'repeatWeek'])->name('program-weeks.repeat');
    Route::delete('/program-weeks/{id}/repeat', [\App\Http\Controllers\PlanController::class, 'cancelRepeat'])->name('program-weeks.cancel-repeat');

    // Scheduled Workouts Routes
    Route::patch('/scheduled-workouts/{id}', [\App\Http\Controllers\ScheduledWorkoutController::class, 'update'])->name('scheduled-workouts.update');
    Route::post('/scheduled-workouts/{id}/skip', [\App\Http\Controllers\ScheduledWorkoutController::class, 'skip'])->name('scheduled-workouts.skip');

    // Pengaturan (Settings) Routes
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'edit'])->name('settings.edit');
    Route::match(['put', 'patch'], '/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/program', [\App\Http\Controllers\SettingsController::class, 'updateProgram'])->name('settings.update-program');
});

// Development Design System Preview (Local Environment Only)
Route::get('/_dev/components', function () {
    abort_unless(app()->environment(['local', 'testing']), 404);
    return view('dev.components');
})->name('dev.components');
