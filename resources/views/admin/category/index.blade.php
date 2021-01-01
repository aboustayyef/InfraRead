@extends('admin.layout')
@section('content')
<div class="flex w-full">
    <h2 class="text-4xl font-bold text-gray-600 mr-12">Categories</h2>
</div>
<hr class="my-12">
<div class="">
    @foreach($categories as $category)
        <div class="flex justify-between items-center mt-1 max-w-lg p-1 hover:bg-gray-50">
            <div class="text-primary font-semibold text-xl tracking-wider">{{$category->description}} </div>
            <a class="ir_button" href="/admin/category/{{($category->id)}}/edit">edit</a>
        </div>
    @endforeach
</div>
<a class="inline-block ir_button mt-12" href="{{route('admin.category.create')}}" class="btn btn-primary pull-right">Create New category</a>
@stop