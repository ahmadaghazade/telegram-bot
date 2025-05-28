<?php

namespace App\Listeners;

use App\Events\PostUpdated;
use App\Jobs\UpdateTelegramMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateTelegramPost implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(PostUpdated $event): void
    {
        UpdateTelegramMessage::dispatch($event->post);
    }
}
