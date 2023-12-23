@extends('admin.layout')
@section('content')
   <div class="flex w-full">
      <h2 class="mr-12 text-4xl font-bold text-gray-600">Muted Phrases</h2>
    </div>
    <p class="mt-4 text-gray-500">If these phrases are found in post's title, the post will be skipped</p>
   <hr class="my-12">
   <livewire:muted></livewire:muted>


@stop
