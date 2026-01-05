<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Courses Routes
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rating Routes
    Route::post('/courses/{course}/rate', [RatingController::class, 'store'])->name('courses.rate');

    // Recommendations Routes (Microservice API)
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
    Route::get('/recommendations/health', [RecommendationController::class, 'health'])->name('recommendations.health');
    Route::get('/recommendations/metrics', [RecommendationController::class, 'metrics'])->name('recommendations.metrics');
    Route::post('/recommendations/train', [RecommendationController::class, 'train'])->name('recommendations.train');
    Route::post('/recommendations/clear-cache', [RecommendationController::class, 'clearCache'])->name('recommendations.clear-cache');
    Route::get('/courses/{course}/predict-rating', [RecommendationController::class, 'predictRating'])->name('courses.predict-rating');
});

require __DIR__ . '/auth.php';
