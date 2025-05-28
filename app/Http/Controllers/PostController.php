<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Events\PostDeleted;
use App\Events\PostUpdated;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Jobs\DeleteTelegramMessage;
use App\Jobs\UpdateTelegramMessage;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        return Post::where('user_id', Auth::id())->get();
    }

    public function store(PostStoreRequest $request)
    {
        $post = auth()->user()->posts()->create($request->validated());

        PostCreated::dispatch($post);

        return response($post, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());
        PostUpdated::dispatch($post);
        return response($post, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            if ($post->telegram_message_id) {
                PostDeleted::dispatch($post);
//            DeleteTelegramMessage::dispatch($post);
            }
            $post->delete();
            return response()->noContent();
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }
}
