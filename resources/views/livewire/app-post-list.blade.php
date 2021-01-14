<div  class="pt-12 relative text-left w-full h-screen overflow-y-auto p-12">
    {{-- unread count --}}
    <script>window.numberOfPosts={{$posts->count()}}</script>
    <div class="max-w-7xl mx-auto cursor-pointer mb-6">
    <div id="ReadCount" class="bg-primary px-4 py-1 rounded-b-md text-white absolute top-0">
        <div class="max-w-7xl mx-auto">
            Unread: {{$posts->count()}}
        </div>
    </div>
</div>
    @if ($source !== 'all')
        <div class="max-w-7xl p-2 container mx-auto py-4">Posts by {{$source_name}} <button wire:click="$emit('updateSource','all')" class="px-2 rounded-full bg-gray-200 hover:bg-primary hover:text-white">&times;</button></div>
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
