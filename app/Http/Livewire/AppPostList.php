<?php

namespace App\Http\Livewire;

use App\Models\Post;
use App\Models\Source;
use Livewire\Component;

class AppPostList extends Component
{
    public $posts;
    public $highlighted_post_index;
    public $keyboard_navigation_on;
    public $source = 'all';
    public $source_name = 'all';

    protected $listeners = ['markPostAsRead', 'highlightPost', 'disableHighlight', 'updateSource'];

    public function mount()
    {
        $this->getPosts();
        $this->keyboard_navigation_on = false;
        $this->highlighted_post_index = 0;
    }

    public function updateSource($source)
    {
        $this->source = $source;
        if ($source !== 'all') {
            $this->source_name = Source::find($source)->name;
        } else {
            $this->source_name = 'all';
        }
        $this->getPosts();
    }

    public function markPostAsRead(Post $post)
    {
        $post->read = 1;
        $post->save();
        $this->getPosts();
    }

    public function disableHighlight()
    {
        $this->highlighted_post_index = 0;
        $this->keyboard_navigation_on = false;
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
        if ($this->source == 'all') {
            $this->posts = Post::with(['source', 'category'])->where('read', 0)->orderBy('posted_at', 'desc')->get();
        } else {
            $this->posts = Post::with(['source', 'category'])->where('read', 0)->where('source_id', $this->source)->orderBy('posted_at', 'desc')->get();
        }
    }

    public function render()
    {
        return view('livewire.app-post-list')->with('posts', $this->posts);
    }
}
