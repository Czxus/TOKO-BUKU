<?php

use App\Http\Controllers\BookController; // Sesuaikan jika ada controller buku
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// === TAMBAHAN UNTUK MODUL 4: RUTE TENANT DENGAN SCOPED BINDING ===
Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::prefix('tenant/{tenant:slug}')->scopeBindings()->group(function () {
        // Contoh rute dashboard tenant
        Route::get('/dashboard', function ($tenant) {
            return "Selamat datang di Toko Buku: " . $tenant;
        })->name('tenant.dashboard');

        // Contoh rute buku milik tenant (opsional jika memiliki BookController)
        // Route::get('/books', [BookController::class, 'index'])->name('tenant.books.index');
        // Route::get('/books/{book}', [BookController::class, 'show'])->name('tenant.books.show');
    });
});