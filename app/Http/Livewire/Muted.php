<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Muted extends Component
{
    public $muted_phrases;
    private $filePath = 'muted_phrases.json';
    public function mount()
    {
        $this->muted_phrases = $this->getList();
    }
    public function getList() :array
    {
        $jsonString = Storage::disk('local')->get($this->filePath);
        return json_decode($jsonString, true); // Converts to an array
    }
    public function writeList()
    {

    }
    public function render()
    {
        return view('livewire.muted');
    }
}
