<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return response()->json([
                'message' => 'Token tidak ditemukan. Sertakan Bearer Token di header Authorization.',
            ], 401);
        }

        $hashed = hash('sha256', $bearerToken);

        $apiToken = ApiToken::with('user')->where('token', $hashed)->first();

        if (! $apiToken) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kadaluarsa.',
            ], 401);
        }

        // Tandai kapan terakhir dipakai
        $apiToken->update(['last_used_at' => now()]);

        // Set user ke request agar bisa diakses via auth()->user() di controller
        auth()->setUser($apiToken->user);

        return $next($request);
    }
}
