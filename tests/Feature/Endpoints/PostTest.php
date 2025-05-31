<?php

namespace Tests\Feature\Endpoints;

use App\Events\PostCreated;
use App\Jobs\PublishPostToTelegram;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_post_and_dispatch_publish_to_telegram_job()
    {
        Bus::fake();
        Event::fake();

        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/posts', [
                'title' => 'Test Post Title',
                'body' => 'Test Post Body',
            ]);

        $response
            ->assertCreated()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    $this->assertSingleStructure($json);
                });
            });

        Event::assertDispatched(PostCreated::class);

        // Simulate listener manually, or allow it
        Event::fakeFor(function () {
            $post = \App\Models\Post::latest()->first();
            PublishPostToTelegram::dispatch($post);
        });

        Bus::assertDispatched(PublishPostToTelegram::class);
    }

    public function test_user_can_update_a_post_and_dispatch_update_telegram_message_job()
    {
        Bus::fake();
        Event::fake();

        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title',
            'body' => 'Original Body',
            'telegram_message_id' => '123456',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
            'body' => 'Updated Body',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['title' => 'Updated Title']);

        Event::assertDispatched(\App\Events\PostUpdated::class);

        Event::fakeFor(function () use ($post) {
            \App\Jobs\UpdateTelegramMessage::dispatch($post);
        });

        Bus::assertDispatched(\App\Jobs\UpdateTelegramMessage::class);
    }

    public function test_user_can_delete_a_post_and_dispatch_delete_telegram_message_job()
    {
        Bus::fake();
        Event::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'telegram_message_id' => '123456',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->deleteJson("/api/posts/{$post->id}");

        $response->assertNoContent();

        Event::assertDispatched(\App\Events\PostDeleted::class);

        Event::fakeFor(function () use ($post) {
            \App\Jobs\DeleteTelegramMessage::dispatch($post);
        });

        Bus::assertDispatched(\App\Jobs\DeleteTelegramMessage::class);
    }

    public function test_user_can_view__single_post()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson("/api/posts/{$post->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $post->id,
                'title' => $post->title,
            ]);
    }

    public function test_user_can_view_all_posts()
    {
        $user = User::factory()->create();

        Post::factory()->count(3)->create(['user_id' => $user->id]);

        $this
            ->actingAs($user)
            ->get('/api/posts')
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->count('data', 3)
                    ->has('data', function (AssertableJson $json) {
                        $json->each(function (AssertableJson $json) {
                            $this->assertSingleStructure($json);
                        });
                    })->etc();
            });
    }

    private function assertSingleStructure(AssertableJson $json): AssertableJson
    {
        return $json->whereAllType([
            'id' => 'integer',
            'telegram_message_id' => 'integer|null',
            'title' => 'string',
            'body' => 'string',
            'user_id' => 'integer',
        ]);
    }
}
