<?php

namespace App\Listeners;

use App\Events\NewSourceAdded;

class GetPostsFromNewlyAddedSource
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(NewSourceAdded $event)
    {
        // Get latest posts from added source
        $event->source->updatePosts();
    }
}
