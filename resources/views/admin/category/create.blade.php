@extends('admin.layout')
@section('content')

<div class="flex w-full">
    <h2 class="text-4xl font-bold text-gray-600">Create new Category</h2>
</div>
<hr class="my-12">
        <form method="POST" action="{{route('admin.category.store')}}">
            @include('admin.category._form')
        <input type="submit" class="ir_button mt-4"></input>
        </form>
@stop