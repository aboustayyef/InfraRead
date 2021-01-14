<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;

class AppPostList extends Component
{
    public $posts;
    public $highlighted_post_index;
    public $keyboard_navigation_on;
    // private $numberOfPosts = 20;

    protected $listeners = ['markPostAsRead', 'highlightPost'];

    public function mount()
    {
        $this->getPosts();
        $this->keyboard_navigation_on = false;
        $this->highlighted_post_index = 0;
    }

    public function markPostAsRead(Post $post)
    {
        $post->read = 1;
        $post->save();
        $this->getPosts();
    }

    public function highlightPost($index)
    {
        if ($this->keyboard_navigation_on == false) {
            $this->highlighted_post_index = 0;
            $this->keyboard_navigation_on = true;
        } else {
            $this->highlighted_post_index = $index;
        }
    }

    public function getPosts()
    {
        $this->posts = Post::with(['source', 'category'])->where('read', 0)->orderBy('posted_at', 'desc')->get();
    }

    public function render()
    {
        return view('livewire.app-post-list')->with('posts', $this->posts);
    }
}
