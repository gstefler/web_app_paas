<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('images.index');
})->name('home');


Route::middleware(['auth'])->group(function () {
    Route::get('images', [ImageController::class, 'index'])->name('images.index');
    Route::post('images', [ImageController::class, 'store'])->name('images.store');
    Route::delete('images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
    Route::get('images/{image}', [ImageController::class, 'show'])->name('images.show');
});

//require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
