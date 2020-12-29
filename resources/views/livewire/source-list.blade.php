<div>

    <input type="text" placeholder="Filter Sources" wire:model="searchString" class="border-none w-full max-w-md p-3 bg-gray-50 rounded-md">

    @foreach ($sources as $source)
        @if ($this->matchSearchString($source))
            <x-source-item v-for="source in this.filtered_sources" v-if="appReady">
                <x-slot name="title">{{$source->name}}</x-slot>
                <x-slot name="description">{{$source->description}}</x-slot>
                <x-slot name="category">{{$source->category? $source->category->description:"No Category"}}</x-slot>
                <x-slot name="edit">/admin/source/{{$source->id}}/edit</x-slot>
            </x-source-item> 
        @endif
    @endforeach
    {{-- Do your work, then step back. --}}
</div>
