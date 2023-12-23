<div>
   <div class="flex w-full max-w-4xl">
      <ul class="flex flex-wrap">
         @foreach ($muted_phrases as $phrase)
            <div class="group relative cursor-pointer overflow-visible">
               <li class="mb-3 mr-4 rounded-lg bg-gray-100 px-4 py-2 shadow-md hover:bg-gray-200">
                  {{ $phrase }}</li>
               {{-- remove phrase button --}}
               <button
                    wire:click="removePhrase({{$loop->iteration}})"
                  class="absolute -top-4 right-2 flex h-8 w-8 items-center justify-center rounded-full border border-gray-400 bg-white text-xl font-bold opacity-0 shadow-sm hover:bg-primary hover:text-white group-hover:opacity-100">&times;</button>
            </div>
         @endforeach
      </ul>
   </div>
   <div class="mt-4">
      <input wire:keydown.enter="addPhrase" type="text" class="rounded border border-gray-300 p-2"
         wire:model="phrase_to_add" placeholder="Add new phrase">
      <button title="remove phrase" wire:click = "addPhrase" class="rounded bg-primary px-4 py-2 font-bold text-white">
         Add
      </button>
   </div>
</div>
