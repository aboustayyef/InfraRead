@extends('admin.layout')
@section('content')
<div class="flex w-full">
    <h2 class="text-4xl font-bold text-gray-600 mr-12">Categories</h2>
</div>
<hr class="my-12">
<div class="max-w-4xl w-full flex flex-wrap">
    @foreach($categories as $category)
    <a href="/admin/category/{{($category->id)}}/edit"
        class="mr-3 mb-3 border border-gray-200 rounded-md px-4 py-2 flex justify-between items-center max-w-lg hover:bg-gray-50 group">
        <div class="text-gray-500 uppercase text-sm font-bold tracking-wider group-hover:text-primary">
            <div>{{$category->description}}</div>
        </div>
        {{-- <a class="ir_button" href="/admin/category/{{($category->id)}}/edit">edit</a> --}}
    </a>
    @endforeach
</div>
<a href="{{route('admin.category.create')}}"
    class="mt-12 inline-block hover:text-white hover:bg-primary bg-white text-primary px-4 py-2 border border-primary rounded-md block">+
    Add Category</a>
@stop
