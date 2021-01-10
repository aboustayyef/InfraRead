<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;

class AppPostList extends Component
{
    public $posts;
    // private $numberOfPosts = 20;

    protected $listeners = ['markAsRead'];

    public function mount()
    {
        $this->getPosts();
    }

    public function markAsRead(Post $post)
    {
        $post->read = 1;
        $post->save();
        $this->getPosts();
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
