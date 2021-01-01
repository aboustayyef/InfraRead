@extends('admin.layout')

@section('content')
<div class="container">

  <div class="flex w-full">
    <h2 class="text-4xl font-bold text-gray-600 mr-12">Edit Source</h2>
</div>
<hr class="my-12">
    <form method="POST" action="/admin/source/{{$source->id}}" >
        <input name="_method" type="hidden" value="PUT">
        @include('admin.source._form')
    <input type="submit" class="ir_button mt-4"></input>
    </form>


    <x-ir-modal formAction="/admin/source/{{$source->id}}">
      Deleting a source cannot be undone
    </x-ir-modal> 


</div>
@stop