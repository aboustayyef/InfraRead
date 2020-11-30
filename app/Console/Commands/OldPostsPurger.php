<?php

namespace App\Console\Commands;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OldPostsPurger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purgeOldPosts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'mark as read all posts that are older than a month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $oneMonthAgo = (new Carbon())->subMonth();
        $posts = Post::where('posted_at', '<', $oneMonthAgo);
        $posts->each(function ($post) {
            $post->read = 1;
            $post->save();
            $this->error($post->id);
        });
    }
}
