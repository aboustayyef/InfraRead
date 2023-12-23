@extends('admin.layout')
@section('content')
   <div class="flex w-full">
      <h2 class="mr-12 text-4xl font-bold text-gray-600">Categories</h2>
   </div>
   <hr class="my-12">
   <div class="flex w-full max-w-4xl flex-wrap">
      @foreach ($categories as $category)
         <a href="/admin/category/{{ $category->id }}/edit"
            class="group mb-3 mr-3 flex max-w-lg items-center justify-between rounded-md border border-gray-200 px-4 py-2 hover:bg-gray-50">
            <div class="text-sm font-bold uppercase tracking-wider text-gray-500 group-hover:text-primary">
               <div>{{ $category->description }}</div>
            </div>
            {{-- <a class="ir_button" href="/admin/category/{{($category->id)}}/edit">edit</a> --}}
         </a>
      @endforeach
   </div>
   <a href="{{ route('admin.category.create') }}"
      class="mt-12 block inline-block rounded-md border border-primary bg-white px-4 py-2 text-primary hover:bg-primary hover:text-white">+
      Add Category</a>
@stop
