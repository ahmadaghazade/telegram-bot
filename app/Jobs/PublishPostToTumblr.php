<?php

namespace App\Jobs;

use App\Models\Post;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Tumblr\API\Client;

class PublishPostToTumblr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->publishPostToTumblr($this->post);
    }

    public function publishPostToTumblr(Post $post): void
    {
        // 1. OAuth middleware for Guzzle
        $oauth = new Oauth1([
            'consumer_key' => config('services.tumblr.consumer_key'),
            'consumer_secret' => config('services.tumblr.consumer_secret'),
            'token' => config('services.tumblr.token'),
            'token_secret' => config('services.tumblr.token_secret'),
        ]);

        // 2. Guzzle HandlerStack + attach OAuth
        $stack = HandlerStack::create();
        $stack->push($oauth);

        // 4. Create Tumblr client with Guzzle client injected
        $client = new Client(
            config('services.tumblr.consumer_key'),
            config('services.tumblr.consumer_secret'),
            config('services.tumblr.token'),
            config('services.tumblr.token_secret'),
        );

        $rh = $client->getRequestHandler();
        $rh->client = new GuzzleClient([
            'handler' => $stack,
            'auth' => 'oauth',
            'proxy' => 'socks5h://127.0.0.1:12334',
            'timeout' => 20,
        ]);
        // 5. Prepare blog hostname
        $blogHostname = config('services.tumblr.blog_hostname');
        // 6. Prepare post data
        $imagePath = storage_path('app/public/'.ltrim($post->image, '/'));

        if (file_exists($imagePath)) {
            // Publish photo post with caption (title + body)
            $caption = $post->title."\n\n".$post->body;

            $response = $client->createPost($blogHostname, [
                'type' => 'photo',
                'caption' => $caption,
                'data' => $imagePath,
            ]);

        } else {
            // Publish text post with title and body
            $response = $client->createPost($blogHostname, [
                'type' => 'text',
                'title' => $post->title,
                'body' => $post->body,
            ]);
        }

        Log::info('Tumblr post published', ['response' => $response]);
    }
}
