<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\AuthenticateWithApiToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — TOKO BUKU
|--------------------------------------------------------------------------
|
| Semua response JSON. Auth menggunakan Bearer Token yang di-generate
| saat login/register dan disimpan di tabel api_tokens.
|
*/

// ── Public: tidak perlu token ────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    // ── Protected: wajib sertakan Bearer Token ───────────────────────────────
    Route::middleware(AuthenticateWithApiToken::class)->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });

});
