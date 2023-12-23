This is the Muted Phrases page
@extends('admin.layout')
@section('content')
   <div class="flex w-full">
      <h2 class="mr-12 text-4xl font-bold text-gray-600">Muted Phrases</h2>
    </div>
    <p class="mt-4 text-gray-500">If these phrases are found in post's title, the post will be skipped</p>
   <hr class="my-12">
   <div class="flex w-full max-w-4xl flex-wrap">
       <ul class="flex space-x-2">
          @foreach ($mutedPhrases as $phrase)
             <li class="px-4 py-2 rounded-lg bg-gray-100 shadow-md hover:bg-gray-200">{{ $phrase }}</li>
          @endforeach
       </ul>
   </div>

@stop
