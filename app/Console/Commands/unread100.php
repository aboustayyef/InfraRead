<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class unread100 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:unread100';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'marke that last 100 posts as unread for testing purposes';

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
     * @return int
     */
    public function handle()
    {
        $posts = Post::OrderBy('posted_at','desc')->take(100)->get();
        $posts->each(function($post){
            $post->read = 0;
            $post->save();
        });
        return 0;
    }
}
