<?php

use App\Http\Controllers\DaysController;
use App\Http\Controllers\EntitiesController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/years', function() {
    return Inertia::render('Years');
})->name('years');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard',  [EntitiesController::class, 'pickup'])->name('dashboard');

    // apiResource では足りないモノを追加。apiResourceの前に定義しないといけない（"create"を{entity}で吸収されるため)
    Route::get('/entities/create', [EntitiesController::class, 'create'])->name('entities.create');
    Route::get('/entities/{entity}/edit', [EntitiesController::class, 'edit'])->name('entities.edit');
    Route::get('/entities/{entity}/days/create', [DaysController::class, 'create'])->name('entities.days.create');
    Route::get('/entities/{entity}/days/{day}/edit', [DaysController::class, 'edit'])->name('entities.days.edit');

    Route::apiResources([
        'entities' => EntitiesController::class,
        'entities.days' => DaysController::class,
    ]);
});

Route::resource('test', \App\Http\Controllers\TestController::class);

require __DIR__.'/auth.php';
