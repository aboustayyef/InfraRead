<div  class="pt-12 relative text-left w-full h-screen overflow-y-auto p-12">
    {{-- unread count --}}
    <script>window.numberOfPosts={{$posts->count()}}</script>
    <div class="max-w-7xl mx-auto flex mb-6 justify-between">
        <div id="ReadCount" class="bg-primary px-4 py-1 rounded-md text-white">
            <div class="max-w-7xl mx-auto">
                Unread: {{$posts->count()}}
            </div>
        </div>
        <a href="/admin" class="block">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 text-gray-300 hover:text-primary cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </a>
    </div>
    @if ($source !== 'all')
        <div class="bg-gray-50 shadow-md rounded-md mb-4 flex justify-between max-w-7xl p-2 container mx-auto py-4 items-center">
            <div class="text-gray-600 uppercase text-sm font-semibold">Posts by {{$source_name}}</div>
            <button wire:click="$emit('updateSource','all')" class="text-lg text-gray-400  w-8 h-8 rounded-full bg-gray-100 hover:bg-primary hover:text-white">
                &times;
            </button>
        </div>
    @endif
    @foreach($posts as $key => $post)
        <div id="post-{{$key}}" data-postid="{{$post->id}}" class="@if($keyboard_navigation_on && 
        $key == $highlighted_post_index) bg-yellow-50 @endif max-w-7xl mx-auto cursor-pointer p-2 border-b border-gray-200">
            <div class="flex">
                <div class="mr-12 w-1/2">
                    <h2 wire:click="$emit('viewPost',{{$post->id}})" class="text-2xl font-semibold text-gray-700 pt-6">{{$post->title}}</h2>
                    <h3 wire:click="$emit('updateSource',{{$post->source->id}})" class="mt-2 font-semibold text-xl uppercase text-primary">{{$post->source->name}}</h3>
                    <h4 class="mt-4 text-gray-500 text-lg">{{$post->posted_at->diffForHumans()}}</h4>
                </div>
                <div wire:click="$emit('viewPost',{{$post->id}})" class="w-1/2 font-light leading-relaxed text-gray-400 text-xl">
                    <p>{{$post->excerpt}}</p>
                </div>
            </div>
            <div class="w-1/2 mb-6">
                <button wire:click.prevent="$emit('markPostAsRead',{{$post->id}})" class="border border-gray-300 rounded-md px-4 py-2 mt-4 hover:bg-primary hover:text-white">Mark Read</button>
            </div>
        </div>
    @endforeach
</div>
