<div>
    @if (empty($post))
    {{-- Nothing --}}
    @else
    <div    class="w-full absolute bg-gray-50 top-0 right-0 h-screen
                 overflow-y-auto           
                transition duration-200 ease-out transform-gpu @if(empty($post)) translate-x-full @else translate-x-0 @endif">
        <div class="p-12 w-full max-w-7xl mx-auto">
            <div>
                <h1 class="text-2xl font-semibold">
                    {{$post->title}}
                </h1>
                <h2>{{$post->source->name}}</h2>
            </div>
            <div class="content has-columns text-lg text-gray-700">
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
<script>
    // Keyboard shortcuts
    window.addEventListener('keydown', (e) => {
      if(e.key === 'Escape'){
        window.Livewire.emit('exitPost')
      }  
    })
</script>