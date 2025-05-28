<?php

namespace App\Listeners;

use App\Events\PostDeleted;
use App\Jobs\DeleteTelegramMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DeleteTelegramPost implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(PostDeleted $event): void
    {
        Log::info('Listener DeleteTelegramPost is triggered for post ID: '.$event->post->id);

        DeleteTelegramMessage::dispatch($event->post);
    }
}
