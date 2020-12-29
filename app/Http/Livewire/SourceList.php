<?php

namespace App\Http\Livewire;

use App\Models\Source;
use Livewire\Component;

class SourceList extends Component
{
    public $sources;
    public $searchString = null;

    public function mount()
    {
        $this->sources = Source::with('Category')->get();
    }

    public function render()
    {
        return view('livewire.source-list');
    }

    public function matchSearchString($source)
    {
        // check of $source title or description contain the search string

        if (
        $this->searchString == null ||
        str_contains(strtolower($source->name), strtolower($this->searchString)) ||
        str_contains(strtolower($source->description), strtolower($this->searchString)
    )) {
            return true;
        }

        return false;
    }
}
