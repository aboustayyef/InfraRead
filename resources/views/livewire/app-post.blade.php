<div>
    @if (empty($post))
    {{-- Nothing --}}
    @else
    <div id="post-view" class="w-full absolute bg-white top-0 right-0 h-screen
                 overflow-y-auto           
                transition duration-200 ease-out transform-gpu @if(empty($post)) translate-x-full @else translate-x-0 @endif">
        <div class="p-12 w-full max-w-7xl mx-auto">
            <div class="mb-6 pb-4 border-b border-gray-200">
                <h1 class="text-gray-700 text-3xl max-w-prose font-semibold">
                    {{$post->title}}
                </h1>
                <h2 class="uppercase text-primary font-semibold text-xl mt-2">{{$post->source->name}}</h2>
                <h3 class="text-gray-300 mt-6">{{$post->posted_at->diffForHumans()}}</h3>
            </div>
            <div class="content has-columns text-lg text-gray-500 leading-relaxed">
                {!!$post->content!!}
            </div>
        </div>
    </div>
    {{-- close button --}}
    <div class="absolute">
        <button wire:keydown.escape="$emit('exitPost')"  wire:click="$emit('exitPost')" class="h-12 w-12 text-2xl font-bold absolute bottom-8 left-8 text-white bg-gray-800 rounded-full flex justify-center items-center">&times;</button>
    </div>
    @endif 
</div>
