<?php

namespace Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name'                  => 'John Doe',
            'email'                 => 'johndoe@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'token',
                'token_type',
            ])
            ->assertJson([
                'message' => 'Registrasi berhasil.',
                'user' => [
                    'name'  => 'John Doe',
                    'email' => 'johndoe@example.com',
                    'role'  => 'pembeli',
                ],
                'token_type' => 'Bearer',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
            'role'  => 'pembeli',
        ]);

        $this->assertDatabaseHas('api_tokens', [
            'name' => 'register',
        ]);
    }

    public function test_register_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name'                  => '',
            'email'                 => 'not-an-email',
            'password'              => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        // First register or create user
        $this->postJson('/api/v1/register', [
            'name'                  => 'Jane Doe',
            'email'                 => 'janedoe@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'janedoe@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'token',
                'token_type',
            ])
            ->assertJson([
                'message' => 'Login berhasil.',
                'user' => [
                    'name'  => 'Jane Doe',
                    'email' => 'janedoe@example.com',
                ],
                'token_type' => 'Bearer',
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::create([
            'name'     => 'Alice',
            'email'    => 'alice@example.com',
            'password' => 'secret1234',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'alice@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $registerResponse = $this->postJson('/api/v1/register', [
            'name'                  => 'Bob',
            'email'                 => 'bob@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $token = $registerResponse->json('token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'name'  => 'Bob',
                    'email' => 'bob@example.com',
                    'role'  => 'pembeli',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_me(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $registerResponse = $this->postJson('/api/v1/register', [
            'name'                  => 'Charlie',
            'email'                 => 'charlie@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $token = $registerResponse->json('token');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Logout berhasil. Token dihapus.',
            ]);

        // After logout, me endpoint should return 401
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me');

        $meResponse->assertStatus(401);
    }
}
