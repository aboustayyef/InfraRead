<div class="text-left w-full h-screen overflow-y-auto">
    @foreach($posts as $key => $post)
        <div class="w-96 mx-auto">
            <button class="border border-gray-400 px-4 py-1 mb-2" wire:click="$emit('postSelected',{{$post->id}})">{{$post->title}}</button>
        </div>
    @endforeach
</div>
