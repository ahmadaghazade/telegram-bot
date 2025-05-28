<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Http\Requests\PostStoreRequest;
use App\Jobs\PublishPostToTelegram;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        return Post::where('user_id', Auth::id())->paginate(10);
    }

    public function store(PostStoreRequest $request)
    {
        $post =  auth()->user()->posts()->create($request->validated());

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
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required',
            'content' => 'required'
        ]);
        $post->update($validated);
        return response($post, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            $post->delete();
            return response()->noContent();
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }
}
