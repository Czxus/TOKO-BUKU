<?php

use App\Http\Controllers\BookController; // Sesuaikan jika ada controller buku
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::redirect('/', '/login');

// Katalog publik (FR-CUS-01): lintas toko dan per toko
Volt::route('katalog', 'pages.catalog.index')->name('katalog');
Volt::route('store/{slug}', 'pages.catalog.index')->name('toko.katalog');

// Keranjang pembeli (Modul 8): disimpan di Redis, harga/stok direvalidasi dari database
Volt::route('keranjang', 'pages.cart.index')
    ->middleware('auth')
    ->name('keranjang');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// === TAMBAHAN UNTUK MODUL 4: RUTE TENANT DENGAN SCOPED BINDING ===
Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::prefix('tenant/{tenant:domain_slug}')->scopeBindings()->group(function () {
        // Contoh rute dashboard tenant
        Route::get('/dashboard', function ($tenant) {
            return "Selamat datang di Toko Buku: " . $tenant;
        })->name('tenant.dashboard');

        // Rute manajemen buku tenant
        Volt::route('/books', 'pages.seller.books')->name('tenant.books');
    });
});