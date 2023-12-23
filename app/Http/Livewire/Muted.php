<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Muted extends Component
{
    public $muted_phrases;
    public $phrase_to_add;
    private $filePath = 'muted_phrases.json';
    public function mount()
    {
        $this->phrase_to_add = "";
        $this->muted_phrases = $this->getList();
    }

    public function addPhrase()
    {
        $this->muted_phrases[] = $this->phrase_to_add;
        $this->writeList();
        $this->phrase_to_add = "";
    }

    public function removePhrase($serial)
    {
        array_splice($this->muted_phrases, $serial - 1, 1);
        $this->writeList();
    }

    public function getList(): array
    {
        $jsonString = Storage::disk('local')->get($this->filePath);
        return json_decode($jsonString, true); // Converts to an array
    }
    public function writeList()
    {
        // Convert the array back to JSON
        $jsonString = json_encode($this->muted_phrases, JSON_PRETTY_PRINT);
        // Write to the file
        Storage::disk('local')->put($this->filePath, $jsonString);
    }
    public function render()
    {
        return view('livewire.muted');
    }
}
