<?php

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{

    use RefreshDatabase;


    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
        $this->assertDatabaseHas('users', ['email' => 'test@gmail.com']);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_user_can_not_login_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
        ]);

        // Create and assign the token to a variable
        $token = $user->createToken('test-token')->plainTextToken;

        // Use the token in the Authorization header
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertOk()
            ->assertJson(['message' => 'User logged out successfully']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }
}
