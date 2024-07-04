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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard',  [EntitiesController::class, 'pickup'])->name('dashboard');

    Route::get('/entities', [EntitiesController::class, 'index'])->name('entities.index');
    Route::get('/entities/create', [EntitiesController::class, 'create'])->name('entities.create');
    Route::get('/entities/{entity}/edit', [EntitiesController::class, 'edit'])->name('entities.edit');
    Route::get('/entities/{entity}/days/create', [DaysController::class, 'create'])->name('entities.days.create');
    Route::get('/entities/{entity}/days/{day}/edit', [DaysController::class, 'edit'])->name('entities.days.edit');

    Route::apiResources([
        'entities' => EntitiesController::class,
        'entities.days' => DaysController::class,
    ]);
});

require __DIR__.'/auth.php';
