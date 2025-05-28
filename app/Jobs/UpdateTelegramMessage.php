<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateTelegramMessage implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function handle(): void
    {
        try {
            Log::info("update telegram message");
            $botToken = config('services.telegram.bot_token');
            $channel = config('services.telegram.channel');

            $message = "*{$this->post->title}*\n{$this->post->body}";

            if ($this->post->telegram_message_id) {
                Http::withOptions([
                    'proxy' => 'socks5://127.0.0.1:12334',
                ])->post("https://api.telegram.org/bot{$botToken}/editMessageText", [
                    'chat_id' => $channel,
                    'message_id' => $this->post->telegram_message_id,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
