<?php

namespace App\Listeners;

use App\Events\PostCreated;
use App\Jobs\PublishPostToTelegram;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPostToTelegram implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PostCreated $event): void
    {
        PublishPostToTelegram::dispatch($event->post);
    }
}
