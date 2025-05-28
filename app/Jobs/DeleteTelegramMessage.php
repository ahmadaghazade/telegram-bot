<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeleteTelegramMessage implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

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
        try {
            Log::info('Post ID in job: ' . $this->post->id);
            Log::info('Telegram Message ID: ' . $this->post->telegram_message_id);

            Log::info("telegram message delete job dispatched");
            $botToken = config('services.telegram.bot_token');
            $chatId = config('services.telegram.channel');
            $messageId = $this->post->telegram_message_id;
            if (!$messageId) {
                Log::warning("No Telegram message ID for post ID {$this->post->id}");
                return;
            }

            $response = Http::withOptions([
                'proxy' => 'socks5://127.0.0.1:12334',
            ])->post("https://api.telegram.org/bot{$botToken}/deleteMessage", [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);

            Log::info('Telegram message deleted', $response->json());
        } catch (\Throwable $th) {
            Log::error("Telegram message delete job dispatch error {$th->getMessage()}");
        }
    }
}
