<div class="text-left w-96 mx-auto">
    @foreach($posts as $key => $post)
        <button class="border border-gray-400 px-4 py-1 mb-2" wire:click="$emit('postSelected',{{$post->id}})">{{$post->title}}</button><br>
    @endforeach
</div>
