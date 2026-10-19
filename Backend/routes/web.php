<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/UserControl', function () {
    return intertia::render('UserControl');
});

Route::get('/api-login', fn () => Inertia::render('ApiLogin'));

Route::get('/spddata', fn () => Inertia::render('SpddataManager'));

// Route::post('/Usercontrol', [spddataController::class, 'store'])->name('spddatas.store');

// Route::put('/Usercontrol/{id}', [spddataController::class, 'update'])->name('spddatas.update');

// Route::delete('Usercontrol/{id}', [spddataController::class, 'destroy'])->name('spddatas.destroys');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';


//ini buat nambah yang baru kyknya pake ini templatenya
// Option A: For a simple, static page with no data
// Route::get('/nama page yang dipake buat web', function () {
//     return Inertia::render('nama file page'); 
// });
