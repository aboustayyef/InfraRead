<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;

class AppPost extends Component
{
    public $post;

    protected $listeners = ['postSelected', 'exitPost'];

    public function postSelected(Post $post)
    {
        $this->post = $post;
        $this->emit('markAsRead', $post->id);
    }

    public function exitPost()
    {
        $this->post = null;
    }

    public function render()
    {
        return view('livewire.app-post');
    }
}
