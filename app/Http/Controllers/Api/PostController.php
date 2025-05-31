<?php

namespace App\Http\Controllers\Api;

use App\Events\PostCreated;
use App\Events\PostDeleted;
use App\Events\PostUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PostResource::collection(
            Post::where('user_id', Auth::id())->paginate()
        );
    }

    public function store(PostStoreRequest $request): PostResource
    {
        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'body' => $request->body,
        ]);

        PostCreated::dispatch($post);

        return new PostResource($post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): PostResource
    {
        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): Response
    {
        $post->update($request->validated());
        PostUpdated::dispatch($post);

        return response($post, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): Response
    {
        if ($post->telegram_message_id) {
            PostDeleted::dispatch($post);
        }
        $post->delete();

        return response()->noContent();
    }
}
