<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublishPostToTelegram implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Dispatchable;

    /**
     * Create a new job instance.
     */

    protected $post;
    public function __construct(Post  $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Publishing Post to Telegram");

        $botToken = config('services.telegram.bot_token');
        $channel = config('services.telegram.channel');

        $message = "*{$this->post->title}*\n{$this->post->body}";
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        Log::info("Telegram URL: " . $url);

        try {
            $response = Http::withOptions([
                'proxy' => 'socks5://127.0.0.1:12334',
                'timeout' => 10,
            ])->post($url, [
                'chat_id' => $channel,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            Log::info("Telegram Response", $response->json());
        } catch (\Exception $e) {
            Log::error("Telegram Send Failed: " . $e->getMessage());
        }
    }
}
