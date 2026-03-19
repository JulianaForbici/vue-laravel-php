<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TaskController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [TaskController::class, 'index'])->name('dashboard');

    Route::resource('tasks', TaskController::class)->only([ 'store', 'edit',  'update',  'destroy',
      ]);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';