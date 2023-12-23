<div class="flex w-full max-w-4xl flex-wrap">
    <ul class="flex space-x-2">
       @foreach ($muted_phrases as $phrase)
          <li class="px-4 py-2 rounded-lg bg-gray-100 shadow-md hover:bg-gray-200">{{ $phrase }}</li>
       @endforeach
    </ul>
</div>
