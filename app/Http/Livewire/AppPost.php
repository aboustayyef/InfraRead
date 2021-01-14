<?php

namespace App\Http\Livewire;

use App\Models\Post;
use App\Utilities\ReadLater;
use Livewire\Component;

class AppPost extends Component
{
    public $post;
    public $read_later_status = 'save';

    protected $listeners = ['viewPost', 'exitPost', 'savePostForReadLater', 'savingPostForLater'];

    public function render()
    {
        return view('livewire.app-post');
    }

    public function viewPost(Post $post)
    {
        $this->post = $post;
        $this->emit('markPostAsRead', $post->id);
    }

    public function exitPost()
    {
        $this->emit('markPostAsRead', $this->post->id);
        $this->post = null;
        $this->read_later_status = 'save';
    }

    public function savePostForReadLater($url)
    {
        $this->read_later_status = 'saving';
        $this->emit('savingPostForLater', $url);
    }

    public function savingPostForLater($url)
    {
        $readlater = new ReadLater($url);
        $savestatus = $readlater->save();
        if ($savestatus) {
            $this->read_later_status = 'saved';
        } else {
            $this->read_later_status = 'error';
        }
    }
}
