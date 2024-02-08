<div>
    <div class="flex justify-between">
        <h2 class="mr-4 text-4xl font-bold text-gray-600 lg:mr-12">Sources</h2>
        <a href="/feeds.opml" class="inline-block rounded-md px-3 py-2 mb-4 bg-red-50 hover:bg-red-200">&darr; Download OPML</a>
    </div>

    <div class="flex flex-wrap w-full max-w-2xl items-center mt-4 space-x-4">
        <input type="text" placeholder="Filter Sources" wire:model="searchString" class="ir_input">
        <a href="{{route('admin.source.create')}}" class="hover:text-white hover:bg-primary bg-white text-primary px-4 py-2 border border-primary rounded-md block">+ Add Source </a>
    </div>

    <div class="mt-6">
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
    </div>
    {{-- Do your work, then step back. --}}
</div>
