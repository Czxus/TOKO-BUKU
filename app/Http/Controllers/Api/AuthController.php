<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Register user baru.
     * POST /api/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Set role default = pembeli
        $user->role = 'pembeli';
        $user->save();

        $plainToken = $this->generateTokenForUser($user, 'register');

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token'      => $plainToken,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user.
     * POST /api/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $plainToken = $this->generateTokenForUser($user, 'login');

        return response()->json([
            'message' => 'Login berhasil.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token'      => $plainToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user (hapus token saat ini).
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $hashed = hash('sha256', $request->bearerToken());

        ApiToken::where('token', $hashed)->delete();

        return response()->json([
            'message' => 'Logout berhasil. Token dihapus.',
        ]);
    }

    /**
     * Info user yang sedang login.
     * GET /api/me
     */
  public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard()->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 401);
        }
        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 401);
        }

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    private function generateTokenForUser(User $user, string $name = 'api'): string
    {
        $plainToken = Str::random(60);

        ApiToken::create([
            'user_id' => $user->id,
            'token'   => hash('sha256', $plainToken),
            'name'    => $name,
        ]);

        return $plainToken;
    }
}