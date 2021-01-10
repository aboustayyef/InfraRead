<div class="w-full absolute bg-gray-50 top-0 right-0 h-screen  transition duration-200 ease-out transform-gpu @if(empty($post)) translate-x-full @else translate-x-0 @endif">
   @if (empty($post))
        No Posts Selected Yet
   @else

        

        <div class="p-12">
            <button wire:click="$emit('markAsRead',{{$post->id}})">[ x ]</button>
            <h1>
                {{$post->title}}
            </h1>
            <h2>{{$post->source->name}}</h2>
            <div>
                {!!$post->content!!}
            </div>
        </div>
   @endif 
</div>
