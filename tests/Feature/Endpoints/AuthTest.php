<?php

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('loginProvider')]
    public function test_user_can_login(bool $expect, string $password)
    {
        $user = User::factory()->create([
            'email' => 'test@gmail.com',
            'password' => 'password',
        ]);

        $response = $this
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => $password,
            ]);

        if ($expect) {
            $response
                ->assertOk()
                ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
        } else {
            $response->assertUnauthorized();
        }
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
        ]);

        Sanctum::actingAs($user);
        // Use the token in the Authorization header
        $this
            ->post('/api/logout')
            ->dump()
            ->assertNoContent();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public static function loginProvider(): array
    {
        return [
            [
                'expect' => true,
                'password' => 'password',
            ],
            [
                'expect' => false,
                'password' => 'wrong_password',
            ],
        ];
    }
}
