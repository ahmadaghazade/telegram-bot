<?php

namespace App\Listeners;

use App\Events\PostCreated;
use App\Jobs\PublishPostToTelegram;
use App\Jobs\PublishPostToTumblr;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
        Log::info('PostCreated event handled in test', ['post_id' => $event->post->id]);
        PublishPostToTelegram::dispatch($event->post);
        PublishPostToTumblr::dispatch($event->post);
    }
}
