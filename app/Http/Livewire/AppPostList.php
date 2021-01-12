<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;

class AppPostList extends Component
{
    public $posts;
    public $highlighted_post_index;
    // private $numberOfPosts = 20;

    protected $listeners = ['markAsRead', 'postHighlighted'];

    public function mount()
    {
        $this->getPosts();
        $this->highlighted_post_index = 0;
    }

    public function markAsRead(Post $post)
    {
        $post->read = 1;
        $post->save();
        $this->getPosts();
    }

    public function postHighlighted($index)
    {
        $this->highlighted_post_index = $index;
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
