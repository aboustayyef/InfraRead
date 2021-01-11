<div  class="pt-12 relative text-left w-full h-screen overflow-y-auto p-12">
    {{-- unread count --}}
    <div class="max-w-7xl mx-auto cursor-pointer mb-6">
    <div class="bg-primary px-4 py-1 rounded-b-md text-white absolute top-0">
        <div class="max-w-7xl mx-auto">
            Unread: {{$posts->count()}}
        </div>
    </div>
</div>
    @foreach($posts as $key => $post)
        <div class="max-w-7xl mx-auto cursor-pointer p-2 border-b border-gray-200">
            <div wire:click="$emit('postSelected',{{$post->id}})" class="flex">
                <div class="mr-12 w-1/2">
                    <h2 class="text-2xl font-semibold text-gray-700 pt-6">{{$key}} {{$post->title}}</h2>
                    <h3 class="mt-2 font-semibold text-xl uppercase text-primary">{{$post->source->name}}</h3>
                    <h4 class="mt-4 text-gray-500 text-lg">{{$post->posted_at->diffForHumans()}}</h4>
                </div>
                <div class="w-1/2 font-light leading-relaxed text-gray-400 text-xl">
                    <p>{{$post->excerpt}}</p>
                </div>
            </div>
            <div class="w-1/2 mb-6">
                <button wire:click.prevent="$emit('markAsRead',{{$post->id}})" class="border border-gray-300 rounded-md px-4 py-2 mt-4 hover:bg-primary hover:text-white">Mark Read</button>
            </div>
        </div>
    @endforeach
</div>
