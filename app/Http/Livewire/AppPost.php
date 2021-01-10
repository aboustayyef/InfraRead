<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;

class AppPost extends Component
{
    public $post;

    protected $listeners = ['postSelected', 'markAsRead'];

    public function postSelected(Post $post)
    {
        $this->post = $post;
    }

    public function markAsRead(Post $post)
    {
        $this->post = null;
    }

    public function render()
    {
        return view('livewire.app-post');
    }
}
