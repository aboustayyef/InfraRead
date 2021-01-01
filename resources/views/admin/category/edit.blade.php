@extends('admin.layout')
@section('content')

<div class="flex w-full">
    <h2 class="text-4xl font-bold text-gray-600 mr-12">Edit Category</h2>
</div>
<hr class="my-12">

        <form method="POST" action="/admin/category/{{$category->id}}" >
            <input name="_method" type="hidden" value="PUT">
            @include('admin.category._form')
        <input type="submit" class="ir_button mt-4"></input>
	        </form>


	{{-- Modal --}}
	<x-ir-modal formAction="/admin/category/{{$category->id}}">
		Deleting a category cannot be undone
	</x-ir-modal> 

@stop
