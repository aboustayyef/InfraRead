<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class readall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:readall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark All Posts as Read';

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
        $posts = Post::all();
        $posts->each(function($post){
            $post->read = 1;
            $post->save();
        });
        return 0;
    }
}
